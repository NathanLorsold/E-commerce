<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Products;
use App\Entity\Categories;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProductsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $typesInformatique = [
            'Clavier' => ['Logitech', 'Corsair', 'Razer'],
            'Souris' => ['Logitech', 'SteelSeries', 'Razer'],
            'Écran' => ['Samsung', 'LG', 'Acer'],
            'Ordinateurs portables' => ['HyperX', 'Sony', 'Bose'],
        ];

        foreach ($typesInformatique as $type => $brands) {
            $category = $manager->getRepository(Categories::class)->findOneBy(['name' => $type]);

            if (!$category) {
                $category = new Categories();
                $category->setName($type);
                $manager->persist($category);
            }

            foreach ($brands as $brand) {
                for ($i = 0; $i < 8; $i++) {
                    $product = new Products();
                    $product->setProductName($brand . ' ' . $type . ' ' . ucfirst($faker->word()));
                    $product->setDescription($faker->sentence(10));
                    $product->setQuantityPerUnit($faker->numberBetween(1, 5));
                    $product->setUnitPrice($faker->randomFloat(2, 30, 300));
                    $product->setUnitsOnStock($faker->numberBetween(0, 20));
                    $product->setCategories($category);

                    // Image unique par produit
                    $imageUrl = 'https://loremflickr.com/320/240/' . urlencode($type) . '?random=' . uniqid();
                    $product->setImageUrl($imageUrl);

                    $manager->persist($product);
                }
            }
        }

        $this->createProductsForCategory($manager, 'Accessoires', [
            'Manettes',
            'Tapis de souris',
            'Repose poignet',
            'Siège gaming'
        ], $faker);

        $manager->flush(); // Très important
    }

    private function createProductsForCategory(ObjectManager $manager, string $parentCategoryName, array $subCategoryNames, $faker): void
    {
        $parentCategory = $manager->getRepository(Categories::class)->findOneBy(['name' => $parentCategoryName]);

        if (!$parentCategory) {
            return;
        }

        $subCategories = $manager->getRepository(Categories::class)->findBy(['parent' => $parentCategory]);

        $brands = [
            'Manettes' => ['Logitech', 'Razer', 'Sony', 'Microsoft'],
            'Tapis de souris' => ['SteelSeries', 'Corsair', 'Logitech', 'Razer'],
            'Repose poignet' => ['Cooler Master', 'HyperX', 'Glorious'],
            'Siège gaming' => ['SecretLab', 'DXRacer', 'Noblechairs'],
        ];

        foreach ($subCategories as $subCategory) {
            $name = $subCategory->getName();

            if (!in_array($name, $subCategoryNames) || !isset($brands[$name])) {
                continue;
            }

            foreach ($brands[$name] as $brand) {
                for ($i = 0; $i < 6; $i++) {
                    $product = new Products();
                    $product->setProductName($brand . ' ' . $name . ' ' . ucfirst($faker->word()));
                    $product->setDescription($faker->sentence(10));
                    $product->setQuantityPerUnit($faker->numberBetween(1, 5));
                    $product->setUnitPrice($faker->randomFloat(2, 15, 250));
                    $product->setUnitsOnStock($faker->numberBetween(0, 15));
                    $product->setCategories($subCategory);

                    // Image unique par produit
                    $imageUrl = 'https://loremflickr.com/320/240/' . urlencode($name) . '?random=' . uniqid();
                    $product->setImageUrl($imageUrl);

                    $manager->persist($product);
                }
            }
        }
    }
}
