<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(unique:true)]
    private int $tg_id;

    #[ORM\Column(length: 255)]
    private string $first_name;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $last_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $user_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $current_command = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prev_command = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->user_name;
    }

    public function setUserName(string $user_name): static
    {
        $this->user_name = $user_name;

        return $this;
    }

    public function getCurrentCommand(): ?string
    {
        return $this->current_command;
    }

    public function setCurrentCommand(string $current_command): static
    {
        $this->current_command = $current_command;

        return $this;
    }

    public function getPrevCommand(): ?string
    {
        return $this->prev_command;
    }

    public function setPrevCommand(string $prev_command): static
    {
        $this->prev_command = $prev_command;

        return $this;
    }

    public function getTgId(): int
    {
        return $this->tg_id;
    }

    public function setTgId(int $tg_id): static
    {
        $this->tg_id = $tg_id;

        return $this;
    }

    public static function create($tg_id, $first_name, $user_name = null, $last_name = null)
    {
        $user = new User();
        $user->first_name = $first_name;
        $user->tg_id = $tg_id;
        $user->user_name = $user_name;
        $user->last_name = $last_name;
        $user->current_command = null;
        $user->prev_command = null;

        return $user;
    }
}
