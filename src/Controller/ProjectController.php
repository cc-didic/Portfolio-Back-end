<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

//Route primaire pour ne pas la répéter dans chaque fonction
#[Route('api/project', name: 'app_api_project_')]

class ProjectController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private ProjectRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,)
    {
    }

    #[Route('', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): JsonResponse
    {
        // Création du projet avec les données de la request
        $project = $this->serializer->deserialize($request->getContent(), Project::class, 'json');
       
        // Date de création
        $project->setCreatedAt (new \DateTimeImmutable());

        //On envoie en base de donnée
        $this->manager->persist($project);
        $this->manager->flush();

        // On retourne le message de création avec l'id du projet
        return new JsonResponse($request, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): JsonResponse
    {
        // On va chercher le projet avec l'id demandé
        $project = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le projet existe
        if($project)
        {
            $responseData = $this->serializer->serialize($project, 'json');

            // On envoie un message s'il existe avec l'id demandé
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id, Request $request): JsonResponse
    {
        // On va chercher le projet avec l'id demandé
        $project = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le projet existe
        if($project)
        {
            $project = $this->serializer->deserialize(
                $request->getContent(),
                Project::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $project]);

            //On envoie en base de donnée
            // Pas besoin de remettre le persist puisqu'on a récupéré les données de la BDD avec le findOneBy.
            $this->manager->flush();

            // On envoie un message s'il existe avec l'id demandé
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        // On envoie un message s'il n'existe pas avec l'id demandé
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        // On va chercher le projet avec l'id demandé
        $project = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le projet existe
        if($project)
        {
            // On supprime le projet
            $this->manager->remove($project);

            //On envoie en base de donnée
            // Pas besoin de remettre le persist puisqu'on a récupéré les données de la BDD avec le findOneBy.
            $this->manager->flush();

            // On envoie un message s'il existe avec l'id demandé
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        //On envoie un message s'il n'existe pas avec l'id demandé
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
