<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    ){
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $user = new User();
        $user->setFirstName('Sébastien');
        $user->setLastName('Petit');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setEmail('admin@gmail.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'aaaaaa'));

        $manager->persist($user);
        $manager->flush();
    }
}
