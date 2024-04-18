<?php

namespace App\DataFixtures;

use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TeamFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $team1 = new Team();
        $team1->setName('FC Twente \'65');
        $team1->setApiId(666); // Ensure this matches your API ID expectations
        $manager->persist($team1);

        $team2 = new Team();
        $team2->setName('SBV Excelsior');
        $team2->setApiId(670); // Ensure this matches your API ID expectations
        $manager->persist($team2);

        $team3 = new Team();
        $team3->setName('Test');
        $team3->setApiId(1); // Ensure this matches your API ID expectations
        $manager->persist($team3);


        $manager->flush();
    }
}
