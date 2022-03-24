<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/register", name="register")
     */
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $notification = null;
        
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if(!$search_email) {
                $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($hashedPassword);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $mail = new Mail();
                $content = 'Bonjour ' . $user->getFirstname() . '<br>Bienvenue sur la première boutique en ligne de Symfony E-commerce.<br><br> Lorem ipsum dolor sit amet, consectetur adipisicing elit. Omnis, aperiam nulla. Commodi aperiam veniam sapiente necessitatibus tempora cumque quas, molestiae porro optio nostrum cupiditate modi. Numquam dolore minus delectus quis.
                    Libero quia odit voluptatum magni vitae nobis ea debitis nesciunt totam, dignissimos quaerat minima voluptates laudantium nisi cupiditate? Rerum nemo aperiam corporis error voluptatum perspiciatis reprehenderit cumque facere possimus tenetur! ';
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur Symfony E-commerce', $content);

                $notification = 'Votre inscription c\'est correctement dérouler, vous pouvez maintenant vous connecter a votre compte.';
            } else {
                $notification = 'L\'email que vous avez renseigné existe déjà.';
            }
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification,
        ]);
    }
}