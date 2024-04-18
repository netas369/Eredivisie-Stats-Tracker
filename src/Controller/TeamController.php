<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TeamController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
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

    #[Route('/team/follow/{id}', name: 'team_follow')]
    public function followTeam(Team $team): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You must be logged in to follow a team.');
        }

        $user->addFollowedTeam($team);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('team_list');
    }

    #[Route('/team/unfollow/{id}', name: 'team_unfollow')]
    public function unfollowTeam(Team $team): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You must be logged in to unfollow a team.');
        }

        $user->removeFollowedTeam($team);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('team_list');
    }
}