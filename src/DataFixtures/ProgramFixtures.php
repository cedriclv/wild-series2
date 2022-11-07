<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        foreach (CategoryFixtures::CATEGORIES as $key => $categoryName) {
            $program = new Program();
            $program->setTitle('Film ' . $key);
            $program->setSynopsis('Un film populaire pour les amateurs du genre ' . $categoryName);
            $program->setCategory($this->getReference('category_' . $categoryName));
            $manager->persist($program);
            $manager->flush();
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
