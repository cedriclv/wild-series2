<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    const NB_PROGRAM = 3;
    
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < self::NB_PROGRAM; $i++) {
            foreach (CategoryFixtures::CATEGORIES as $key => $categoryName) {
                $program = new Program();
                $program->setTitle('Film ' . $key . $i);
                $program->setSynopsis('Un film populaire pour les amateurs du genre ' . $categoryName);
                $program->setCategory($this->getReference('category_' . $categoryName));
                $this->addReference('program_' . $categoryName. $i, $program);
                $manager->persist($program);
                $manager->flush();
            }
        }
    }
    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            CategoryFixtures::class,
        ];
    }
}
