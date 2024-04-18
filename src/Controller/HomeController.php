<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class HomeController extends AbstractController
{
    private $cache;

    private $security;

    public function __construct(CacheInterface $footballCache, TokenStorageInterface $tokenStorage)
    {
        $this->cache = $footballCache;

        $this->security = $tokenStorage;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $teamsData = $this->cache->get('football_teams', function() {
            throw new \Exception("No data available in cache");
        });

        $user = $this->security->getToken()->getUser();

        if ($user instanceof User) {
        $followedTeams = $user->getFollowedTeams();
        } else {
        $followedTeams = [];
        }

        return $this->render('home/index.html.twig', [
            'teams' => $teamsData['teams'],
            'followedTeams' => $followedTeams
        ]);
    }
}
