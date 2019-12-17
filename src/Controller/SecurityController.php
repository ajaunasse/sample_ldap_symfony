<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils) {

        $error  = $authenticationUtils->getLastAuthenticationError();

        $username = $authenticationUtils->getLastUsername();

        return $this->render('Security/login.html.twig', array(
            'last_username' => $username,
            'error' => $error
        ));
    }

}