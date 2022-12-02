<?php

namespace App\DataFixtures;

use Faker\Factory;;
use App\Entity\Season;
use App\DataFixtures\ProgramFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    public const NB_SEASON = 5;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for($i=0; $i<(count(CategoryFixtures::CATEGORIES)*ProgramFixtures::NB_PROGRAM*self::NB_SEASON);$i++) {
            $season = new Season();
            $season->setNumber($faker->numberBetween(1, 10));
            $season->setYear($faker->year());
            $season->setDescription($faker->paragraphs(3, true));
            $this->addReference('season_' . $i, $season);
            $program = $this->getReference('program_' . CategoryFixtures::CATEGORIES[random_int(0,4)] .random_int(0,2));
            $season->setProgram($program);
            $manager->persist($season);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProgramFixtures::class,
        ];
    }
}