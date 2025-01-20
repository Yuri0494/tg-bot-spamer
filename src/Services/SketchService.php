<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SketchesRepository;
use App\Entity\Sketches;
use Exception;

class SketchService {

    public function __construct(
        private EntityManagerInterface $em,
        private SketchesRepository $sr,
    ) {}

    public function getSketchLink(string $sketchName, int $series_number, bool $withSetIsWatched = false): ?string
    {
        $sketch = $this->sr->findOneBy(['sketch_name' => $sketchName, 'series_number' => $series_number]);

        if (!$sketch) {
            return null;
        }

        if ($withSetIsWatched) {
            $sketch->setIsWatched(true);
            $this->em->persist($sketch);
            $this->em->flush();
        }

        return $sketch->getLink();
    }

    public function getAllSketchesByName(string $sketchName): array
    {
        $sketches = $this->sr->findBy(
            ['sketch_name' => $sketchName], 
            ['series_number' => 'ASC']
        );

        if (!$sketches) {
            return null;
        }

        return $sketches;
    }

    public function getSeriesCountOfSketch($sketchName)
    {
        return $this->sr->count(['sketch_name' => $sketchName]);
    }

    public function setAllSketchesNonWatched(): void
    {
        $this->em->beginTransaction();
        try {
            $sketches = $this->sr->findAll();

            foreach($sketches as $sketch) {
                $sketch->setIsWatched(false);
                $this->em->persist($sketch);
            }
    
            $this->em->flush();
            $this->em->commit();
        } catch (Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }
    // Проставляем флаг "просмотрено" на определенное количество серий
    public function setIsWatchedByListOfSeries(string $sketch_name, int $count): void
    {
        $this->em->beginTransaction();
        try {
            $sketches = $this->sr->findBy(['sketch_name' => $sketch_name], ['series_number' => 'ASC'], limit: $count);

            $counter = 1;
            foreach($sketches as $sketch) {
    
                if($counter > $count) {
                    break;
                }
    
                $sketch->setIsWatched(true);
                $this->em->persist($sketch);
            }
            
            $this->em->flush();
            $this->em->commit();
        } catch (Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    public function publishSketchesToDb(array $sketches, $sketch_name): void
    {
        $counter = 1;
        foreach($sketches as $link) {
            $sketch = new Sketches();
            $sketch->setSketchName($sketch_name);
            $sketch->setSeriesNumber($counter);
            $sketch->setLink($link);
            $sketch->setIsWatched(false);

            $this->em->persist($sketch);
            $this->em->flush();

            $counter++;
        }
    }
}