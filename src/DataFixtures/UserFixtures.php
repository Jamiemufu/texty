<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;
    private $params;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, ParameterBagInterface $params)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->params = $params;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail($this->params->get('admin_email'));
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setFirstname("Admin");
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            $this->params->get('admin_password')
        ));

        $manager->persist($user);
        $manager->flush();
    }
}
