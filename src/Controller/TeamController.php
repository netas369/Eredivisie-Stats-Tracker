<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\ItemInterface;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class TeamController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;
    private TokenStorageInterface $tokenStorage;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache, TokenStorageInterface $tokenStorage)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->tokenStorage = $tokenStorage;
        
    }

    #[Route('/team/{teamId}', name: 'team_details')]
    public function showTeamDetails($teamId, CacheInterface $cache): Response
    {
        // Retrieve match data from the cache or fetch it if necessary
        $matchesData = $cache->get('football_team_matches_' . $teamId, function (ItemInterface $item) use ($teamId) {
            $item->expiresAfter(3600); // Cache for 1 hour

            $response = $this->httpClient->request(
                'GET',
                "https://api.football-data.org/v4/teams/{$teamId}/matches/",
                ['headers' => ['X-Auth-Token' => '1828402b90f84baa952ffca2fe9b3b53']]
            );

            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                return $data['matches'] ?? [];
            } else {
                return [];
            }
        });

        return $this->render('team/index.html.twig', [
            'matches' => $matchesData
        ]);
    }

    #[Route('/team/follow/{apiId}', name: 'team_follow')]
    public function followTeam(int $apiId, EntityManagerInterface $entityManager): Response
    {
        $team = $entityManager->getRepository(Team::class)->findOneBy(['apiId' => $apiId]);

        if (!$team) {
            throw $this->createNotFoundException('No team found for api_id ' . $apiId);
        }

        $user = $this->getUser();
        $user->addFollowedTeam($team);
        $entityManager->persist($user);
        $entityManager->flush();
        $entityManager->refresh($user);

        return $this->redirectToRoute('app_home'); // Redirect to a route after adding
    }


    #[Route('/team/unfollow/{apiId}', name: 'team_unfollow')]
    public function unfollowTeam(int $apiId, EntityManagerInterface $entityManager): Response
{
    $team = $entityManager->getRepository(Team::class)->findOneBy(['apiId' => $apiId]);
    if (!$team) {
        throw new NotFoundHttpException('No team found for api_id ' . $apiId);
    }

    $user = $this->getUser();
    if (!$user) {
        throw new AccessDeniedException('You must be logged in to unfollow a team.');
    }

    $user->removeFollowedTeam($team);
    $entityManager->persist($user);
    $entityManager->flush();

    return $this->redirectToRoute('app_home'); // Redirect to a route after removing
}


    #[Route('/followed', name: 'followed_list')]
    public function listTeams()
    {$token = $this->tokenStorage->getToken();
        if (!$token) {
            return $this->redirectToRoute('app_login');
        }
        
        $user = $token->getUser();
        if (!is_object($user)) {
            throw new UnsupportedUserException('Expected an object type user.');
        }

        $followedTeams = $user->getFollowedTeams();
        $teamData = [];

        foreach ($followedTeams as $team) {
            $teamData[$team->getId()] = [
                'team' => $team,
                'recentMatch' => $this->fetchTeamMatchData($team->getApiId(), 'recent'),
                'upcomingMatch' => $this->fetchTeamMatchData($team->getApiId(), 'upcoming')
            ];
        }

        return $this->render('followed/index.html.twig', [
            'teamMatches' => $teamData
        ]);
    }

    private function fetchTeamMatchData(int $teamId, string $matchType): ?array
    {
        $cacheKey = "team_{$teamId}_{$matchType}_match";
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($teamId, $matchType) {
            $url = "https://api.football-data.org/v4/teams/{$teamId}/matches?status=" . 
                   ($matchType === 'recent' ? 'FINISHED' : 'SCHEDULED') . "&limit=1";
            $response = $this->httpClient->request('GET', $url, [
                'headers' => ['X-Auth-Token' => '1828402b90f84baa952ffca2fe9b3b53']
            ]);

            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                return $data['matches'][0] ?? null;
            }

            return null;
        });
    }

}