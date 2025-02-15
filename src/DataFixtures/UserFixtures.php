<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private function __construct(private UserPasswordHasherInterface $PasswordHasher)
    {
    }
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 20; $i++){
        $user = (new User())
        -> setEmail("email.$1@dtudi.com");

        $user->setPassword($this->PasswordHasher->hashPassword($user, "password$1"));

        $manager->persist($user);
        }
        $manager->flush();
    }
}
