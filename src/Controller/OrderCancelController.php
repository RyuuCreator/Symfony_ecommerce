<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderCancelController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/order/cancel/{stripeSessionId}", name="order_cancel")
     */
    public function index($stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() != $this->getUSer()) {
            return $this->redirectToRoute('home');
        }

        // envoyer un mail  pour echec de payment. 
        $mail = new Mail();
        $content = 'Bonjour ' . $order->getUser()->getFirstname() . '<br>Votre commande n\'est pas aller au bout de la validation.<br><br> Lorem ipsum dolor sit amet, consectetur adipisicing elit. Omnis, aperiam nulla. Commodi aperiam veniam sapiente necessitatibus tempora cumque quas, molestiae porro optio nostrum cupiditate modi. Numquam dolore minus delectus quis.
            Libero quia odit voluptatum magni vitae nobis ea debitis nesciunt totam, dignissimos quaerat minima voluptates laudantium nisi cupiditate? Rerum nemo aperiam corporis error voluptatum perspiciatis reprehenderit cumque facere possimus tenetur! ';
        $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Commande annuler.', $content);

        return $this->render('order_cancel/index.html.twig', [
            'order' => $order,
        ]);
    }
}
