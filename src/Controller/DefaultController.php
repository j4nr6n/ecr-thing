<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('', name: 'homepage')]
    public function homepage(): Response
    {
        if ($this->getUser()) {
            return $this->render('default/dashboard.html.twig');
        }

        return $this->render('default/homepage.html.twig');
    }
}
