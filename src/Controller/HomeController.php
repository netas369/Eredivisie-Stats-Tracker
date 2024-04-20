<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Team;

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
    public function index(EntityManagerInterface $entityManager): Response
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

        // Fetching data to get followed teams below
        $user = $this->getUser();

        if ($user) {
            // Fetching the followed teams directly from the database
            $followedTeams = $entityManager->getRepository(Team::class)
                                            ->createQueryBuilder('t')
                                            ->innerJoin('t.followers', 'u')
                                            ->where('u.id = :userId')
                                            ->setParameter('userId', $user->getId())
                                            ->getQuery()
                                            ->getResult();
        }

        return $this->render('home/index.html.twig', [
            'teams' => $teamsData['teams'],
            'followedTeams' => $followedTeams
        ]);

    }
}
