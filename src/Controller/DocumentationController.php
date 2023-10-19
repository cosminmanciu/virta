<?php

// src/Controller/CompanyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DocumentationController extends AbstractController
{

    #[Route('/doc', name: 'documentation')]
    public function renderDocumentation(): Response
    {
        return $this->render('documentation.html.twig');
    }

}
