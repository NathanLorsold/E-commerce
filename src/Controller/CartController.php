<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Products;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CartController extends AbstractController
{
    #[Route('/panier/ajouter/{id}', name: 'cart_add')]
    public function add(Products $product, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $id = $product->getId();

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += 1;
        } else {
            $cart[$id] = [
                'productName' => $product->getProductName(),
                'unitPrice' => $product->getUnitPrice(),
                'quantity' => 1,
            ];
        }

        $session->set('cart', $cart);

        $this->addFlash('success', $product->getProductName() . ' ajouté au panier');

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/panier', name: 'cart_index')]
    public function index(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        $total = 0;
        foreach ($cart as $item) {
            // Vérification de la clé avant usage
            if (isset($item['unitPrice'], $item['quantity'])) {
                $total += $item['unitPrice'] * $item['quantity'];
            }
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'total' => $total
        ]);
    }

    #[Route('/panier/augmenter/{id}', name: 'cart_increase')]
public function increase(Products $product, SessionInterface $session): Response
{
    $cart = $session->get('cart', []);
    $id = $product->getId();

    if (isset($cart[$id])) {
        $cart[$id]['quantity']++;
        $session->set('cart', $cart);
    }

    return $this->redirectToRoute('cart_index');
}

#[Route('/panier/diminuer/{id}', name: 'cart_decrease')]
public function decrease(Products $product, SessionInterface $session): Response
{
    $cart = $session->get('cart', []);
    $id = $product->getId();

    if (isset($cart[$id])) {
        $cart[$id]['quantity']--;

        if ($cart[$id]['quantity'] <= 0) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);
    }

    return $this->redirectToRoute('cart_index');
}

#[Route('/panier/vider', name: 'cart_clear')]
public function clear(SessionInterface $session): Response
{
    $session->remove('cart');

    $this->addFlash('success', 'Panier vidé avec succès');
    return $this->redirectToRoute('cart_index');
}

#[IsGranted('ROLE_USER')]
#[Route('/panier/valider', name: 'cart_validate')]
public function validate(SessionInterface $session, EntityManagerInterface $em): Response
{
    $cart = $session->get('cart', []);

    if (empty($cart)) {
        $this->addFlash('warning', 'Votre panier est vide.');
        return $this->redirectToRoute('cart_index');
    }

    // Calcul du total
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['unitPrice'] * $item['quantity'];
    }

    // Création de la commande
    $order = new Order();
    $order->setUser($this->getUser());
    $order->setCreatedAt(new \DateTimeImmutable());
    $order->setTotal($total); // 💥 NE PAS OUBLIER CETTE LIGNE

    // Générer une référence unique
    $reference = 'CMD-' . date('Ymd') . '-' . strtoupper(uniqid());
    $order->setReference($reference);

    // Statut initial
    $order->setStatus('en attente');

    // Persister et flush
    $em->persist($order);
    $em->flush();

    // $session->remove('cart'); // Vider le panier après validation

    $this->addFlash('success', 'Commande enregistrée avec succès !');

return $this->redirectToRoute('order_confirmation', [
    'reference' => $order->getReference()
]);
}

}
