<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountController extends AbstractController
{
    /**
     * @Route("/my-account", name="app_account_index")
     */
    public function index(Request $request, UserInterface $user): Response
    {
        return $this->render('Account/index.html.twig', [
            'user' => $user,
        ]);
    }
}
