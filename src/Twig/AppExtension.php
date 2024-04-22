<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\User;
use App\Entity\Team;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('is_followed_by_user', [$this, 'isFollowedByUser']),
        ];
    }

    public function isFollowedByUser(Team $team, User $user): bool
    {
        foreach ($user->getFollowedTeams() as $followedTeam) {
            if ($followedTeam->getId() === $team->getId()) {
                return true;
            }
        }
        return false;
    }
}
