<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoriesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Catégorie principale : informatique
        $informatique = $this->createCategory('Informatique', null, $manager);
        $this->createCategory('Ordinateurs portables', $informatique, $manager);
        $this->createCategory('Écran', $informatique, $manager);
        $this->createCategory('Souris', $informatique, $manager);
        $this->createCategory('Clavier', $informatique, $manager);

        // Catégorie principale : accessoires
        $accessoires = $this->createCategory('Accessoires', null, $manager);
        $this->createCategory('Manettes', $accessoires, $manager);
        $this->createCategory('Tapis de souris', $accessoires, $manager);
        $this->createCategory('Siège gaming', $accessoires, $manager);
        $this->createCategory('Repose poignet', $accessoires, $manager);

        $manager->flush();
    }

    private function createCategory(string $name, ?Categories $parent, ObjectManager $manager): Categories
    {
        $category = new Categories();
        $category->setName($name);
        if ($parent) {
            $category->setParent($parent);
        }

        $manager->persist($category);
        return $category;
    }
}
