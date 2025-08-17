<?php

namespace App\Controller;

use App\Entity\Products;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Categories;
use Doctrine\ORM\EntityManagerInterface;

class TestController extends AbstractController
{
    #[Route('/test-relation', name: 'test_relation')]
    public function test(EntityManagerInterface $em): Response
    {
        // Création d'une catégorie
        $category = new Categories();
        $category->setName('Ecrans');

        // Création d’un produit lié à la catégorie
        $product = new Products();
        $product->setProductName('Écran 27 pouces');
        $product->setDescription('Un écran 4K de qualité.');
        $product->setUnitPrice(299.99);
        $product->setQuantityPerUnit(0, 10);
        $product->setUnitsOnStock(0, 10);

        $product->setCategories($category); // LIEN ICI 👈

        $em->persist($product);
        $em->persist($category);

        $em->flush();

        return new Response('Produit et catégorie créés et liés.');
    }
}
