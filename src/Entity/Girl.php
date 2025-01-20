<?php

namespace App\Entity;

use App\Repository\GirlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GirlRepository::class)]
class Girl
{
    #[ORM\Id]
    #[ORM\GeneratedValue("IDENTITY")]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 1000000, nullable: true)]
    private string $personal_info;

    #[ORM\OneToMany(targetEntity: GirlImages::class, mappedBy: 'girl', orphanRemoval: true)]
    private Collection $girlImages;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $is_watched = false;

    public function __construct()
    {
        $this->girlImages = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId($id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getPersonalInfo(): string
    {
        return $this->personal_info;
    }

    public function setPersonalInfo(string $personal_info): static
    {
        $this->personal_info = $personal_info;

        return $this;
    }

    /**
     * @return Collection<int, GirlImages>
     */
    public function getGirlImages(): Collection
    {
        return $this->girlImages;
    }

    public function addGirlImage(GirlImages $girlImage): static
    {
        if (!$this->girlImages->contains($girlImage)) {
            $this->girlImages->add($girlImage);
            $girlImage->setGirl($this);
        }

        return $this;
    }

    public function removeGirlImage(GirlImages $girlImage): static
    {
        if ($this->girlImages->removeElement($girlImage)) {
            // set the owning side to null (unless already changed)
            if ($girlImage->getGirl() === $this) {
                $girlImage->setGirl(null);
            }
        }

        return $this;
    }

    public function isIsWatched(): bool
    {
        return $this->is_watched;
    }

    public function setIsWatched(bool $is_watched): static
    {
        $this->is_watched = $is_watched;

        return $this;
    }
}
