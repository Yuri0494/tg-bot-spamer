<?php

namespace App\Entity;

use App\Repository\GirlImagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GirlImagesRepository::class)]
class GirlImages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'girlImages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Girl $girl = null;

    #[ORM\Column(length: 10000000)]
    private string $link;

    public function getId(): int
    {
        return $this->id;
    }

    public function getGirl(): ?Girl
    {
        return $this->girl;
    }

    public function setGirl(?Girl $girl): static
    {
        $this->girl = $girl;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): static
    {
        $this->link = $link;

        return $this;
    }
}
