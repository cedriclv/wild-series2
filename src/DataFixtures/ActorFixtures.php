<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Actor;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ActorFixtures extends Fixture implements DependentFixtureInterface
{
    public const NB_ACTOR = 10;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for($i=0;$i<self::NB_ACTOR;$i++){
            $actor = new Actor();
            $actor->setName($faker->lastName);
            $this->addReference('actor_' . $i, $actor);
            for($j=0; $j<(count(CategoryFixtures::CATEGORIES)*ProgramFixtures::NB_PROGRAM);$j++) {
                    $program = $this->getReference('program_' . CategoryFixtures::CATEGORIES[random_int(0,4)] .random_int(0,2));
                    $program->addActor($actor);
                    $manager->persist($actor);
                }                
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
