<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Episode;
use App\DataFixtures\SeasonFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    public const NB_EPISODE = 10;

    public function __construct(SluggerInterface $slugger){
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for($i=0; $i<(count(CategoryFixtures::CATEGORIES)*ProgramFixtures::NB_PROGRAM*SeasonFixtures::NB_SEASON*self::NB_EPISODE);$i++) {
            $episode = new Episode();
            $episode->setTitle($faker->sentence($nbWords = 6, $variableNbWords = true));
            $episode->setNumber($faker->numberBetween(1, 10));
            $episode->setSynopsis($faker->paragraphs(3, true));
            $episode->setDuration($faker->numberBetween(1, 100));
            $slug = $this->slugger->slug($episode->getTitle());
            $episode->setSlug($slug);       
            $season = $this->getReference('season_' . random_int(1,count(CategoryFixtures::CATEGORIES)*ProgramFixtures::NB_PROGRAM*SeasonFixtures::NB_SEASON)-1);
            $episode->setSeason($season);

            $manager->persist($episode);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SeasonFixtures::class,
        ];
    }

}
