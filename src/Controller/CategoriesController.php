<?php

namespace App\Controller;

use App\Entity\Categories;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoriesController extends AbstractController
{
    
#[Route('/categorie/{id}', name: 'category_products')]
public function show(Categories $categories): Response
{
    $products = $categories->getProducts();

    return $this->render('categories/show.html.twig', [
        'categories' => $categories,
        'products' => $products,
        'children' => $categories->getChildren()
    ]);
}

}