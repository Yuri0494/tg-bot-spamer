<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Sketches;
use App\Entity\Girl;
use App\Entity\GirlImages;
use App\Repository\SketchesRepository;
use App\Repository\GirlRepository;
use App\Repository\GirlImagesRepository;

class DbQueries {

    public function __construct(
        private EntityManagerInterface $em,
        private SketchesRepository $sr,
        private GirlRepository $gr,
        private GirlImagesRepository $gir,
    ) {}

    public function createGirl($data)
    {
        $girl = new Girl();
        $girl->setPersonalInfo($data['text'] ?? 'Описание отсутствует');
        $this->em->persist($girl);

        if(!empty($data['attachments'])) {
            foreach($data['attachments'] as $attach) {
                if($attach['type'] === 'photo') {
                    $image = new GirlImages();
                    $image->setLink($attach['photo']['orig_photo']['url'] ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR9wEBDQeRddGon9QAN-mgU9Yb4mcJ9trSAhw&s');
                    $image->setGirl($girl);
                    $this->em->persist($image);
                }
            }
        }
        $this->em->flush();
    }

    // public function createGirlImages($id, $images)
    // {
    //     foreach($images as $image) {
    //         if($image['type' === 'photo']) {
    //             $im = new GirlImages();
    //             $im->setGirlId($id);
    //             $im->setLink($image['photo']['orig_photo']['url']);
    //         }
    //     }
    // }


    public function setIsWatched() {
        $dulins = $this->sr->findBy(['sketch_name' => 'taganrog'], ['series_number' => 'ASC']);
        $counter = 1;
        foreach($dulins as $dulin) {
            if($counter > 3) {
                break;
            }
            $dulin->setIsWatched(true);
            $this->em->persist($dulin);
            $this->em->flush();
            $counter++;
        }
    }

    // public function deleteGirls() {
    //     $girls = $this->gr->findBy();
    //     foreach($girls as $girl) {
    //         $this->em->remove($girl);
    //         $this->em->flush();
    //     }
    // }

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