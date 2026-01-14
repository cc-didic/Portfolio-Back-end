<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/project', name: 'app_api_project_')]
class ProjectController extends AbstractController
{
    #[Route(name: 'new', methods: 'POST')]
    public function new(): Response
    {

    }

    #[Route('/', name: 'show', methods: 'GET')]
    public function show(): Response
    {

    }

    #[Route('/', name: 'edit', methods: 'PUT')]
    public function edit(): Response
    {

    }

    #[Route('/', name: 'delete', methods: 'DELETE')]
    public function delete(): Response
    {

    }
}
