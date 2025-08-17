<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Order;
use App\Entity\Products;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Service\EmailService;




class OrderController extends AbstractController
{

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

#[Route('/commande/valider', name: 'order_validate')]
public function validate(
    SessionInterface $session,
    EntityManagerInterface $em
): Response {
    $cart = $session->get('cart', []);

    if (empty($cart)) {
        $this->addFlash('warning', 'Votre panier est vide.');
        return $this->redirectToRoute('cart_index');
    }

    // Calcul total
    $total = 0;

    // Création de la commande
    $order = new Order();
    $order->setUser($this->getUser());
    $order->setCreatedAt(new \DateTimeImmutable());
    $order->setStatus('en attente');

    // Référence unique
    $reference = 'CMD-' . date('Ymd') . '-' . strtoupper(uniqid());
    $order->setReference($reference);

    foreach ($cart as $productId => $item) {
        // On récupère l'objet Product depuis l'ID
        $product = $em->getRepository(Products::class)->find($productId);
        if (!$product) {
            continue; // ou lancer une exception
        }

        $orderItem = new OrderItem();
        $orderItem->setProduct($product);
        $orderItem->setQuantity($item['quantity']);
        $orderItem->setPrice($item['unitPrice']);
        $orderItem->setTotal($item['quantity'] * $item['unitPrice']);

        $em->persist($orderItem);

        $total += $item['unitPrice'] * $item['quantity'];
    }

    $order->setTotal($total);
    $em->persist($order);
    $em->flush();

    $session->remove('cart');

    $this->addFlash('success', 'Commande validée avec succès !');

    return $this->redirectToRoute('order_confirmation', [
        'reference' => $order->getReference()
    ]);
}

#[Route('/commande/confirmation/{reference}', name: 'order_confirmation')]
public function confirmation(string $reference, OrderRepository $orderRepo, EmailService $emailService): Response
{
    $order = $orderRepo->findOneBy(['reference' => $reference]);

    if (!$order || $order->getUser() !== $this->getUser()) {
        throw $this->createNotFoundException('Commande introuvable');
    }

    $emailService->sendOrderConfirmation(
        $order->getUser()->getEmail(),
        'Confirmation de votre commande',
        ['order' => $order]
    );

    return $this->render('order/confirmation.html.twig', [
        'order' => $order
    ]);
}


#[Route('/mon-compte/commandes', name: 'account_orders')]    
public function accountOrders(OrderRepository $orderRepo): Response
{
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $orders = $orderRepo->findBy(['user' => $this->getUser()], ['createdAt' => 'DESC']);

    return $this->render('account/orders.html.twig', [
        'orders' => $orders
    ]);
}

#[Route('/commande/checkout', name: 'order_checkout')]
public function checkout(SessionInterface $session,Security $security, UrlGeneratorInterface $urlGenerator): Response
{
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $cart = $session->get('cart', []);
    $user = $security->getUser();

    if (empty($cart)) {
        $this->addFlash('warning', 'Votre panier est vide');
        return $this->redirectToRoute('cart_index');
    }

    return $this->render('order/checkout.html.twig', [
        'cart' => $cart
    ]);
}


#[Route('/commande/stripe-session', name: 'order_stripe_session', methods: ['POST'])]
public function stripeSession(
    SessionInterface $session,
): Response {
    \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    $cart = $session->get('cart', []);
    $lineItems = [];


    foreach ($cart as $id => $item) {
        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $item['unitPrice'] * 100,
                'product_data' => [
                    'name' => $item['productName'],
                ],
            ],
            'quantity' => $item['quantity'],
        ];
    }

    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => $this->generateUrl('order_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
        'cancel_url' => $this->generateUrl('cart_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
    ]);

    return $this->redirect($checkout_session->url, 303);
}

#[IsGranted('ROLE_USER')]
#[Route('/commande/success', name: 'order_payment_success')]
public function paymentSuccess(SessionInterface $session, EntityManagerInterface $em): Response
{
    $cart = $session->get('cart', []);
    
    if (empty($cart)) {
        $this->addFlash('error', 'Aucune commande à enregistrer.');
        return $this->redirectToRoute('cart_index');
    }

    $total = 0;

    // Création de la commande
    $order = new Order();
    $order->setUser($this->getUser());
    $order->setCreatedAt(new \DateTimeImmutable());
    $order->setReference('CMD-' . date('Ymd') . '-' . strtoupper(uniqid()));
    $order->setStatus('payée');

    foreach ($cart as $productId => $item) 
    {
        $product = $em->getRepository(Products::class)->find($productId);
        if (!$product) {
            continue; // Ignore si produit introuvable
        }

        $orderItem = new OrderItem();
        $orderItem->setoorder($order); // Associe à la commande
        $orderItem->setProduct($product); // ← ici on passe bien un objet Products
        $orderItem->setQuantity($item['quantity']);
        $orderItem->setPrice($item['unitPrice']);
        $orderItem->setTotal($item['unitPrice'] * $item['quantity']);

        $em->persist($orderItem);
        $total += $item['unitPrice'] * $item['quantity'];
    }

    $order->setTotal($total);

    $em->persist($order);
    $em->flush();

    $session->remove('cart');

    $this->addFlash('success', 'Paiement confirmé ! Commande enregistrée.');
    return $this->redirectToRoute('order_confirmation', [
        'reference' => $order->getReference()
    ]);
}
    #[Route('/mon-compte/commandes/{reference}', name: 'account_order_show')]
    public function showUserOrder(string $reference, OrderRepository $orderRepo): Response
    {
        $order = $orderRepo->findOneBy(['reference' => $reference]);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande introuvable');
        }

        return $this->render('account/order_show.html.twig', [
            'order' => $order
        ]);
    }

}