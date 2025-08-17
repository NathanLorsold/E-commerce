<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\Categories;
use App\Form\ProductsType;
use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;


class ProductsController extends AbstractController
{
    #[Route('/products', name: 'products_index')]
    public function index(ProductsRepository $productsRepository): Response
    {
        return $this->render('products/index.html.twig');
    }

#[Route('/products/{id}', name: 'products_details')]
public function details(Products $product): Response
{
    return $this->render('products/details.html.twig', [
        'product' => $product
    ]);
}

#[Route('/produits/sous-categorie/{id}', name: 'products_par_sous_categorie')]
public function produitsParSousCategorie(
    Categories $categories,
    Request $request,
    PaginatorInterface $paginator,
    ProductsRepository $productsRepository // ← ajoute ceci
): Response {
    $query = $productsRepository->createQueryBuilder('p')
        ->where('p.categories = :cat')
        ->setParameter('cat', $categories)
        ->getQuery();

    $pagination = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        6
    );

    return $this->render('products/liste.html.twig', [
        'products' => $pagination,
        'sousCategorie' => $categories
    ]);
}
}
