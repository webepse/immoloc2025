<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Image;
use Cocur\Slugify\Slugify;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');  
        $slugify = new Slugify();

        // gestion des annonces
        for($i = 1; $i <= 30; $i++)
        {
            $ad = new Ad();
            $title = $faker->sentence();
            // $slug = $slugify->slugify($title);
            $coverImage = "https://picsum.photos/id/".$i."/1000";
            $introduction = $faker->paragraph(2);
            $content = "<p>".join('</p><p>',$faker->paragraphs(5))."</p>";

            $ad->setTitle($title)
                ->setCoverImage($coverImage)
                ->setIntroduction($introduction)
                ->setContent($content)
                ->setPrice(rand(40,200))
                ->setRooms(rand(1,5));
            
            $manager->persist($ad);

            // gestion de l'image de l'annonce
            for($j = 1; $j <= rand(2,5); $j++)
            {
                $image = new Image();
                $image->setUrl("https://picsum.photos/id/".$j."/900")
                    ->setCaption($faker->sentence())
                    ->setAd($ad);
                $manager->persist($image);
            }
        }
        $manager->flush();
    }
}
