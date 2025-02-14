<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function exists(int $id): bool|Exception
    {
        $user = $this->findOneBy(['tg_id' => $id]);

        if (!$user) {
            return false;
        }

        return true;
    }

    public function findByTgId(int $id):? User
    {
        return $this->findOneBy(['tg_id' => $id]);
    }

    public function createOrFind($info): User
    {
        
        $user = $this->findByTgId($info['id']);

        if ($user instanceof User) {
            return $user;
        }

        $user = User::create(
            $info['id'], 
            $info['first_name'], 
            $info['username'] ?? null,
            $info['last_name'] ?? null,
            $info['last_command'] ?? null,  
        );

        $this->save($user);

        return $user;
    }


    public function setCurrentCommand(User $user, $command)
    {
        $user->setCurrentCommand($command);
        $this->save($user);
    }

    public function setPrevCommand(User $user, $command)
    {
        $user->setPrevCommand($command);
        $this->save($user);
    }

    public function save(User $user): void
    {
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();
    }
}
