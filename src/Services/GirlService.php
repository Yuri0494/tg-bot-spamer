<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GirlRepository;
use App\Repository\GirlImagesRepository;
use Exception;

class GirlService {

    public function __construct(
        private EntityManagerInterface $em,
        private GirlRepository $gr,
        private GirlImagesRepository $gir,
    ) {}

    public function getGirlsInfo(int $limit, bool $withSetIsWatched = false): array
    {
        $girls = $this->gr->getSomeUnwatchedGirls($limit);
        $girlsInfo = [];

        foreach($girls as $i => $girl) {
            $pesonalInfo = $girl->getPersonalInfo();
            $links = [];

            foreach($girl->getGirlImages() as $image) {
                $link = $image->getLink();

                if (!$link) {
                    continue;
                }

                $links[] = $image->getLink();
            }

            if (count($links) < 1) {
                continue;
            }

            $girlsInfo[$i] = [
                'personal_info' => $pesonalInfo ?? '',
                'img_links' => $links, 
            ];

            if ($withSetIsWatched) {
                $girl->setIsWatched(true);
                $this->em->persist($girl);
            }
        }

        if (!empty($girlsInfo) && $withSetIsWatched) {
            $this->em->flush();
        }

        return $girlsInfo;
    }

    public function getCountOfGirls()
    {
        return $this->gr->count();
    }

    public function getGirlInfoById(int $id, bool $withSetIsWatched = false): array
    {
        $girls = $this->gr->findBy(['id' => $id]);
        $girlsInfo = [];

        foreach($girls as $i => $girl) {
            $pesonalInfo = $girl->getPersonalInfo();
            $links = [];

            foreach($girl->getGirlImages() as $image) {
                $link = $image->getLink();

                if (!$link) {
                    continue;
                }

                $links[] = $image->getLink();
            }

            if (count($links) < 1) {
                continue;
            }

            $girlsInfo[$i] = [
                'personal_info' => $pesonalInfo ?? '',
                'img_links' => $links, 
            ];

            if ($withSetIsWatched) {
                $girl->setIsWatched(true);
                $this->em->persist($girl);
            }
        }

        if (!empty($result) && $withSetIsWatched) {
            $this->em->flush();
        }

        return $girlsInfo;
    }

    public function getGirlss(int $id, int $limit): array
    {
        $girls = $this->gr->getSomeGirlsFromId($id, $limit);
        $girlsInfo = [];

        foreach($girls as $i => $girl) {
            $pesonalInfo = $girl->getPersonalInfo();
            $links = [];

            foreach($girl->getGirlImages() as $image) {
                $link = $image->getLink();

                if (!$link) {
                    continue;
                }

                $links[] = $image->getLink();
            }

            if (count($links) < 1) {
                continue;
            }

            $girlsInfo[$i] = [
                'personal_info' => $pesonalInfo ?? '',
                'img_links' => $links, 
            ];
        }

        return $girlsInfo;
    }

    private function setGirlsIsWatched(bool $isWatched = false): void
    {
        $this->em->beginTransaction();
        try {
            $girls = $this->gr->findAll();

            for($i = 0; $i < 100; $i++) {
                $girls[$i]->setIsWatched($isWatched);
                $this->em->persist($girls[$i]);
                $this->em->flush();
                echo "Сущность с id = " . (string) $i . " обновлена" . PHP_EOL;
            }
    
            $this->em->flush();
            $this->em->commit();
        } catch (Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}