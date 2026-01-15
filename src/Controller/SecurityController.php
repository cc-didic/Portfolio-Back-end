<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('/api', name: 'app_api_')]

class SecurityController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private SerializerInterface $serializer)
    {
    }

    #[Route('/registration', name: 'registration', methods: 'POST')]
    #[OA\Post(
        path: '/api/registration',
        description: "Données de l'utilisateur à inscrire",
        summary: "Inscription d'un nouveau utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'MonMotDePasse')
                ],
                type: 'object'
            )
        ),
        tags: ["user"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Utilisateur inscrit avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', type: 'string', example: 'user@example.com'),
                        new OA\Property(property: 'api_token', type: 'string', example: 'bfb389db38f9d1129058c731a1ae1291b32c1d11'),
                        new OA\Property(property: 'roles', type: 'string', example: 'ROLE_USER')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                 description: "Une authentification complète est requise pour accéder à cette ressource."
            ),
            new OA\Response(
                response: 500,
                 description: "Requête invalide"
            )
        ]

    )]

    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(
            ['user' => $user->getUserIdentifier(), 'api_token' => $user->getApiToken(), 'roles' => $user->getRoles()],
            Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'login', methods: 'POST')]
    #[OA\Post(
        path: '/api/login',
        description: "Données de l'utilisateur à inscrire",
        summary: "Connecter un utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'MonMotDePasse')
                ],
                type: 'object'
            )
        ),
        tags: ["User"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Connexion réussie",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', type: 'string', example: 'user@example.com'),
                        new OA\Property(property: 'api_token', type: 'string', example: 'bfb389db38f9d1129058c731a1ae1291b32c1d11'),
                        new OA\Property(property: 'roles', type: 'string', example: 'ROLE_USER')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Les données sont invalide"
            )
        ]
    )]

    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if(null === $user)
        {
            return new JsonResponse(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'user' => $user->getUserIdentifier(),
            'api_token' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ]);
    }
}
