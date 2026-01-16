<?php

namespace App\Controller;

use App\Entity\Skill;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/skill', name: 'app_api_skill_')]

class SkillController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private SkillRepository $repository,
        private SerializerInterface $serializer)
    {
    }

    #[Route('', name: 'new', methods: 'POST')]
    #[OA\Post(
        path: '/api/skill',
        description: "Données de la compétence à créer",
        summary: "Créer une compétence",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nom de la compétence'),
                    new OA\Property(property: 'logo', type: 'string', example: 'image/image.png'),
                ],
                type: 'object'
            )
        ),
        tags: ["Skill"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Compétence créé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Nom de la compétence'),
                        new OA\Property(property: 'logo', type: 'string', example: 'image/image.png'),
                        new OA\Property(property: 'project', type: 'string', example: "[]")
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
        // Création du skill avec les données de la request
        $skill = $this->serializer->deserialize($request->getContent(), Skill::class, 'json');

        // On envoie en base de donnée
        $this->manager->persist($skill);
        $this->manager->flush();

        // On retourne le message de création avec l'id du skill
        return $this->Json($skill, Response::HTTP_CREATED, [], ['groups' => ['skill:read']]);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: '/api/skill/{id}',
        description: "ID de la compétence à afficher",
        summary: "Afficher une compétence par son ID",
        tags: ["Skill"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Compétence trouvé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Nom de la compétence'),
                        new OA\Property(property: 'logo', type: 'string', example: 'image/image.png'),
                        new OA\Property(property: 'project', type: 'string', example: "[]")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                 description: "Compétence non Trouvé"
            ),
            new OA\Response(
                response: 500,
                 description: "Id non valide"
            )
        ]

    )]

    public function show(int $id): JsonResponse
    {
        // On va chercher le skill avce l'id demandé
        $skill = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le skill existe
        if($skill){
            $skillData = $this->serializer->serialize($skill, 'json');

            // On envoie un message s'il existe avec l'id demandé
            return new JsonResponse($skillData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: '/api/skill/{id}',
        description: "ID de la compétence à modifier",
        summary: "Modifier une compétence par son ID",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nom de la compétence'),
                    new OA\Property(property: 'logo', type: 'string', example: 'image/image.png')
                ],
                type: 'object'
            )
        ),
        tags: ["Skill"],
        responses: [
            new OA\Response(
                response: 204,
                description: "Compétence modifier avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Nom de la compétence'),
                        new OA\Property(property: 'logo', type: 'string', example: 'image/image.png'),
                        new OA\Property(property: 'project', type: 'string', example: "[]")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                 description: "Compétence non Trouvé"
            ),
            new OA\Response(
                response: 500,
                 description: "Id non valide"
            )
        ]

    )]

    public function edit(int $id, Request $request): JsonResponse
    {
        // On va chercher le skill avce l'id demandé
        $skill = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le skill existe
        if($skill){
            $skill = $this->serializer->deserialize(
                $request->getContent(),
                Skill::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $skill]);

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
        path: '/api/skill/{id}',
        summary: "Supprimer une compétence par son ID",
        tags: ["Skill"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID de la compétence à supprimer",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Compétence supprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Projet non Trouvé"
            )
        ]
    )]

    public function delete(int $id): JsonResponse
    {
        // On va chercher le skill avce l'id demandé
        $skill = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le skill existe
        if($skill){
            // On supprime le projet
            $this->manager->remove($skill);

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
