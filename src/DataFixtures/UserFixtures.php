<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{

    public function __construct(UserPasswordEncoderInterface $password_encoder)
    {
        $this->password_encoder = $password_encoder; 
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$name, $last_name, $email, $password, $api_key, $roles])
        {
            $user = new User();
            $user->setName($name);
            $user->setLastName($last_name);
            $user->setEmail($email);
            $user->setPassword($this->password_encoder->encodePassword($user, $password));
            $user->setVideoApiKey($api_key);
            $user->setRoles($roles);
            $manager->persist($user);
        }

        $manager->flush();
    }
    private function getUserData(): array
    {   
        return [
            ['Adam', 'Wojdylo', 'adam.wojdylo.programista@gmail.com', 'learn123', 'jsdjsj', 
            ['ROLE_ADMIN']],
            ['Kamil', 'Stoch', 'skoki@PZN.com', 'learn123', null, 
            ['ROLE_ADMIN']],
            ['Mat', 'Zandstra', 'book@gmail.com', 'learn123', null, 
            ['ROLE_USER']],
        ];
    }   
}
