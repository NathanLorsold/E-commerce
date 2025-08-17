<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use App\Repository\CategoriesRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    #[Route('/main', name: 'app_accueil')] 
    public function index(ProductsRepository $productsRepository): Response
    {
        return $this->render('main/index.html.twig' , [
            'products' => $productsRepository->findAll()
        ]);
    }
    
}
