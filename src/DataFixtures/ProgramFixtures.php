<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    const NB_PROGRAM = 3;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < self::NB_PROGRAM; $i++) {
            foreach (CategoryFixtures::CATEGORIES as $key => $categoryName) {
                $program = new Program();
                $program->setTitle('Film ' . $key . $i);
                $program->setSynopsis('Un film populaire pour les amateurs du genre ' . $categoryName);
                $program->setCategory($this->getReference('category_' . $categoryName));
                $program->setOwner($this->getReference('user_' . 'contributor@monsite.com'));
                $slug = $this->slugger->slug('Film ' . $key . $i);
                $program->setSlug($slug);        
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
