<?php

namespace App\Entity;

use App\Repository\SketchesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SketchesRepository::class)]
class Sketches
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $sketch_name;

    #[ORM\Column]
    private int $series_number;

    #[ORM\Column]
    private bool $isWatched;

    #[ORM\Column(length: 255)]
    private string $link;

    #[ORM\Column(length: 255)]
    private ?int $season;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSketchName(): string
    {
        return $this->sketch_name;
    }

    public function setSketchName(string $sketch_name): static
    {
        $this->sketch_name = $sketch_name;

        return $this;
    }

    public function getSeriesNumber(): int
    {
        return $this->series_number;
    }

    public function setSeriesNumber(int $series_number): static
    {
        $this->series_number = $series_number;

        return $this;
    }

    public function isIsWatched(): bool
    {
        return $this->isWatched;
    }

    public function setIsWatched(bool $isWatched): static
    {
        $this->isWatched = $isWatched;

        return $this;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(int $season): static
    {
        $this->season = $season;

        return $this;
    }
}
