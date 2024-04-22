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
        $team1->setApiId(666);
        $manager->persist($team1);

        $team2 = new Team();
        $team2->setName('SBV Excelsior');
        $team2->setApiId(670);
        $manager->persist($team2);

        $team3 = new Team();
        $team3->setName('Heracles Almelo');
        $team3->setApiId(671);
        $manager->persist($team3);

        $team4 = new Team();
        $team4->setName('SC Heerenveen');
        $team4->setApiId(673);
        $manager->persist($team4);

        $team5 = new Team();
        $team5->setName('PSV');
        $team5->setApiId(674);
        $manager->persist($team5);

        $team6 = new Team();
        $team6->setName('Feyenoord Rotterdam');
        $team6->setApiId(675);
        $manager->persist($team6);

        $team7 = new Team();
        $team7->setName('FC Utrecht');
        $team7->setApiId(676);
        $manager->persist($team7);

        $team8 = new Team();
        $team8->setName('AFC Ajax');
        $team8->setApiId(678);
        $manager->persist($team8);

        $team9 = new Team();
        $team9->setName('SBV Vitesse');
        $team9->setApiId(679);
        $manager->persist($team9);

        $team10 = new Team();
        $team10->setName('AZ');
        $team10->setApiId(682);
        $manager->persist($team10);

        $team11 = new Team();
        $team11->setName('RKC Waalwijk');
        $team11->setApiId(683);
        $manager->persist($team11);

        $team12 = new Team();
        $team12->setName('PEC Zwolle');
        $team12->setApiId(684);
        $manager->persist($team12);

        $team13 = new Team();
        $team13->setName('Go Ahead Eagles');
        $team13->setApiId(718);
        $manager->persist($team13);

        $team14 = new Team();
        $team14->setName('Almere City FC');
        $team14->setApiId(1911);
        $manager->persist($team14);

        $team15 = new Team();
        $team15->setName('NEC');
        $team15->setApiId(1915);
        $manager->persist($team15);

        $team16 = new Team();
        $team16->setName('FC Volendam');
        $team16->setApiId(1919);
        $manager->persist($team16);

        $team17 = new Team();
        $team17->setName('Fortuna Sittard');
        $team17->setApiId(1920);
        $manager->persist($team17);

        $team18 = new Team();
        $team18->setName('Sparta Rotterdam');
        $team18->setApiId(6806);
        $manager->persist($team18);


        $manager->flush();
    }
}
