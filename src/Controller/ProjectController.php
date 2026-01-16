<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
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
        private SkillRepository $skillRepository)
    {
    }

    #[Route('', name: 'new', methods: ['POST'])]
    #[OA\Post(
        path: '/api/project',
        description: "Données du projet à créer",
        summary: "Créer un projet",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'titre du projet'),
                    new OA\Property(property: 'description', type: 'string', example: 'Description du projet'),
                    new OA\Property(property: 'image', type: 'string', example: 'image/image.png'),
                    new OA\Property(property: 'github_url', type: 'string', example: 'http://exemple.com'),
                    new OA\Property(property: 'live_url', type: 'string', example: 'http://exemple.com'),
                    new OA\Property(property: 'skills', type: 'array', items: new OA\Items(type: 'integer', example: 1), example: [1, 2])
                ],
                type: 'object'
            )
        ),
        tags: ["Project"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Restaurant créé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'titre du projet'),
                        new OA\Property(property: 'description', type: 'string', example: 'Description du projet'),
                        new OA\Property(property: 'image', type: 'string', example: 'image/image.png'),
                        new OA\Property(property: 'github_url', type: 'string', example: 'http://exemple.com'),
                        new OA\Property(property: 'live_url', type: 'string', example: 'http://exemple.com'),
                        new OA\Property(property: 'createdAt', type: 'string', format: "date-time"),
                        new OA\Property(property: 'skills', type: 'array', items: new OA\Items(type: 'integer', example: 1), example: [1, 2])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                 description: "Requête invalide"
            )
        ]

    )]
    public function new(Request $request): JsonResponse
    {
        // Création du projet avec les données de la request
        $project = $this->serializer->deserialize($request->getContent(), Project::class, 'json', ['groups' => ['project:write']]);
       
        // Date de création
        $project->setCreatedAt (new \DateTimeImmutable());

        // Ajout des skills
        $data = json_decode($request->getContent(), true);

        foreach($data['skills'] ?? [] as $skillId){
            $skill = $this->skillRepository->find($skillId);
            if($skill){
                $project->addSkill($skill);
            }
        }

        //On envoie en base de donnée
        $this->manager->persist($project);
        $this->manager->flush();

        // On retourne le message de création avec l'id du projet
        return $this->Json($project, Response::HTTP_CREATED, [], ['groups' => ['project:read']]);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: '/api/project/{id}',
        description: "ID du projet à afficher",
        summary: "Afficher un projet par son ID",
        tags: ["Project"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Projet trouvé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'titre du projet'),
                        new OA\Property(property: 'description', type: 'string', example: 'Description du projet'),
                        new OA\Property(property: 'image', type: 'string', example: 'image/image.png'),
                        new OA\Property(property: 'github_url', type: 'string', example: 'http://exemple.com'),
                        new OA\Property(property: 'live_url', type: 'string', example: 'http://exemple.com'),
                        new OA\Property(property: 'createdAt', type: 'string', format: "date-time"),
                        new OA\Property(property: 'skills', type: 'array', items: new OA\Items(type: 'integer', example: 1), example: [1, 2])
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                 description: "Projet non Trouvé"
            ),
            new OA\Response(
                response: 500,
                 description: "Id non valide"
            )
        ]

    )]

    public function show(int $id): JsonResponse
    {
        // On va chercher le projet avec l'id demandé
        $project = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le projet existe
        if($project)
        {
            // Sérialisation du projet en ignorant skills
            $responseData = $this->serializer->serialize($project, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['skills']]);
            $responseData = json_decode($responseData, true);

            // Ajout manuel des skills
            $skills = [];

            foreach ($project->getSkills() as $skill){
                $skills[] = [
                    'id' => $skill->getId(),
                    'name' => $skill->getName(),
                    'logo' => $skill->getLogo()
                ];
            }

            $responseData['skills'] = $skills;

            // On envoie un message s'il existe avec l'id demandé
            return new JsonResponse($responseData, Response::HTTP_OK);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: '/api/project/{id}',
        description: "ID du projet à modifier",
        summary: "Modifier un projet par son ID",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'titre du projet'),
                    new OA\Property(property: 'description', type: 'string', example: 'Description du projet'),
                    new OA\Property(property: 'image', type: 'string', example: 'image/image.png'),
                    new OA\Property(property: 'github_url', type: 'string', example: 'http://exemple.com'),
                    new OA\Property(property: 'live_url', type: 'string', example: 'http://exemple.com'),
                    new OA\Property(property: 'skills', type: 'array', items: new OA\Items(type: 'integer', example: 1), example: [1, 2])
                ],
                type: 'object'
            )
        ),
        tags: ["Project"],
        responses: [
            new OA\Response(
                response: 204,
                description: "Projet modifier avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'titre du projet'),
                        new OA\Property(property: 'description', type: 'string', example: 'Description du projet'),
                        new OA\Property(property: 'image', type: 'string', example: 'image/image.png'),
                        new OA\Property(property: 'github_url', type: 'string', example: 'http://exemple.com'),
                        new OA\Property(property: 'live_url', type: 'string', example: 'http://exemple.com'),
                        new OA\Property(property: 'createdAt', type: 'string', format: "date-time"),
                        new OA\Property(property: 'skills', type: 'array', items: new OA\Items(type: 'integer', example: 1), example: [1, 2])
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                 description: "Projet non Trouvé"
            ),
            new OA\Response(
                response: 500,
                 description: "Id non valide"
            )
        ]

    )]

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
                [AbstractNormalizer::OBJECT_TO_POPULATE => $project]
            );

            // Récupération du JSON
            $data = json_decode($request->getContent(), true);

            // Gestion des skills
            if(isset($data['skills']) && is_array($data['skills'])){

                // On supprime les compétences existantes
                foreach($project->getSkills() as $skill){
                    $project->removeSkill($skill);
                }

                // On ajoute les nouvelles compétences
                foreach($data['skills'] as $skillId){
                    $skill = $this->skillRepository->find($skillId);
                    if($skill){
                        $project->addSkill($skill);
                    }
                }
            }

            

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
    #[OA\Delete(
        path: '/api/project/{id}',
        summary: "Supprimer un projet par son ID",
        tags: ["Project"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID du projet à supprimer",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Projet supprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Projet non Trouvé"
            )
        ]
    )]

    public function delete(int $id): JsonResponse
    {
        // On va chercher le projet avec l'id demandé
        $project = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le projet existe
        if($project)
        {
            // Suppression des relations skills
            foreach($project->getSkills() as $skill){
                $project->removeSkill($skill);
            }
            
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
