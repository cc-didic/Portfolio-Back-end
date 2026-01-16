<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/skill', name: 'app_api_project_')]

class SkillController extends AbstractController
{
    #[Route(name: 'new', methods: 'POST')]
    public function new(): Response
    {
    
    }

    #[Route(name: 'show', methods: 'GET')]
    public function new(): Response
    {
    
    }

    #[Route(name: 'edit', methods: 'PUT')]
    public function new(): Response
    {
    
    }

    #[Route(name: 'delete', methods: 'DELETE')]
    public function new(): Response
    {
    
    }
}
