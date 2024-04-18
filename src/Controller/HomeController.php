<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

class HomeController extends AbstractController
{
    private $cache;

    public function __Construct(CacheInterface $footballCache)
    {
        $this->cache = $footballCache;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $teamsData = $this->cache->get('football_teams', function() {
            throw new \Exception("No data available in cache");
        });

        return $this->render('home/index.html.twig', [
            'teams' => $teamsData['teams']
        ]);
    }
}
