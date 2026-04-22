<?php
namespace w3des\AdminBundle\Repository;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository implements UserLoaderInterface
{

    public function loadUserByUsername($username)
    {
        return $this->loadUserByEmail($username);
    }

    public function loadUserByEmail($email)
    {
        return $this->findOneBy([
            'email' => strtolower($email)
        ]);
    }
}

