<?php

namespace App\Repository;

use App\Entity\Chat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    public function exists(string $id): bool
    {
        $chat = $this->findOneBy(['chat_id' => $id]);

        if (!$chat) {
            return false;
        }

        return true;
    }

    public function findByTgId(int $id)
    {
        return $this->findOneBy(['chat_id' => $id]);
    }

    public function createOrFind($info)
    {
        $chat = $this->findOneBy(['chat_id' => $info['id']]);

        if ($chat instanceof Chat) {
            return $chat;
        }

        $chat = Chat::create(
            $info['id'], 
            $info['type'], 
            $info['title'] ?? null, 
        );

        $em = $this->getEntityManager();
        $em->persist($chat);
        $em->flush();

        return $chat;
    }
}
