<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/order/thanx/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() != $this->getUSer()) {
            return $this->redirectToRoute('home');
        }

        if($order->getState() == 0) {
            // Vider le panier
            $cart->remove();

            // Modifier le status state de notre commande en mettant 1
            $order->setState(1);
            $this->entityManager->flush();

            // Envoyer un mail à notre client pour lui confirmer sa commande
            $mail = new Mail();
            $content = 'Bonjour ' . $order->getUser()->getFirstname() . '<br>Merci de votre commande.<br><br> Lorem ipsum dolor sit amet, consectetur adipisicing elit. Omnis, aperiam nulla. Commodi aperiam veniam sapiente necessitatibus tempora cumque quas, molestiae porro optio nostrum cupiditate modi. Numquam dolore minus delectus quis.
                Libero quia odit voluptatum magni vitae nobis ea debitis nesciunt totam, dignissimos quaerat minima voluptates laudantium nisi cupiditate? Rerum nemo aperiam corporis error voluptatum perspiciatis reprehenderit cumque facere possimus tenetur! ';
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande sur Symfony E-commerce est bien validé', $content);
            
        }
        // Afficher les quelque information de la commande de l'utilisateur

        return $this->render('order_success/index.html.twig', [
            'order' => $order,
        ]);
    }
}
