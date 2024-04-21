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
use Psr\Log\LoggerInterface;

class TeamController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;
    private TokenStorageInterface $tokenStorage;
    private $logger;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache, TokenStorageInterface $tokenStorage, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
        
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


#[Route('/followed', name: 'followed_teams')]
public function listTeams(): Response
{
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    $followedTeams = $user->getFollowedTeams();
    $teamMatches = [];

    foreach ($followedTeams as $team) {
        $matches = $this->fetchTeamMatches($team->getApiId()); // Fetch all matches for the team
        $upcomingMatch = $this->getUpcomingMatch($matches); // Get the next upcoming match within a week
        $recentMatch = $this->getMostRecentMatch($matches); // Get the most recent match
    
        $teamMatches[] = [
            'team' => $team,
            'recentMatch' => $recentMatch,
            'upcomingMatch' => $upcomingMatch
        ];
    }

    return $this->render('followed/index.html.twig', [
        'teamMatches' => $teamMatches
    ]);
}

private function fetchTeamMatches(int $teamId): array
{
    $url = "https://api.football-data.org/v4/teams/{$teamId}/matches";
    $response = $this->httpClient->request('GET', $url, [
        'headers' => ['X-Auth-Token' => '1828402b90f84baa952ffca2fe9b3b53']
    ]);

    if ($response->getStatusCode() === 200) {
        return $response->toArray()['matches'];
    }

    return [];
}

private function getMostRecentMatch(array $matches): ?array
{
    // Filter out matches that are finished and sort them by date descending
    $filteredMatches = array_filter($matches, function ($match) {
        return new \DateTime($match['utcDate']) < new \DateTime() && $match['status'] === 'FINISHED';
    });

    usort($filteredMatches, function ($a, $b) {
        return new \DateTime($b['utcDate']) <=> new \DateTime($a['utcDate']);
    });

    // Return the first match which will be the most recent past match
    return $filteredMatches[0] ?? null;
}

private function getUpcomingMatch(array $matches): ?array
{
    $now = new \DateTime();
    $oneWeekLater = new \DateTime('+7 days');

    $filteredMatches = array_filter($matches, function ($match) use ($now, $oneWeekLater) {
        $matchDate = new \DateTime($match['utcDate']);
        return $matchDate > $now && $matchDate <= $oneWeekLater;
    });

    usort($filteredMatches, function ($a, $b) {
        return new \DateTime($a['utcDate']) <=> new \DateTime($b['utcDate']);
    });

    // Return the first upcoming match that is within the next week
    return $filteredMatches[0] ?? null;
}
}