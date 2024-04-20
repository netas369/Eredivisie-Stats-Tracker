<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class StandingsController extends AbstractController
{
    #[Route('/standings', name: 'standings')]
    public function index(CacheInterface $cache): Response
    {
        $standings = $cache->get('football_standings', function(ItemInterface $item) {
            throw new \Exception("Standings data is not available in the cache");
        });

        return $this->render('standings/index.html.twig', [
            'standings' => $standings
        ]);
    }
}
