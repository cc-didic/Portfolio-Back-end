<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

//Route primaire pour ne pas la répéter dans chaque fonction
#[Route('api/project', name: 'app_api_project_')]

class ProjectController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private ProjectRepository $repository)
    {
    }

    #[Route(name: 'new', methods: 'POST')]
    public function new(): Response
    {
        // Création du projet
        $project = new Project();

        // Nom du projet
        $project->setTitle ( title: 'PixelVerse Studio');

        // Description du projet
        $project->setDescription ( description: 'ECF');

        // Lien de l'image
        $project->setImage ( image: 'src/images/image.png');

        // Lien github
        $project->setGithubUrl ( github_url: 'http://googl.com');

        // Lien server
        $project->setLiveUrl ( live_url: 'http://google.com');

        // Date de création
        $project->setCreatedAt (new \DateTimeImmutable());

        //On envoie en base de donnée
        $this->manager->persist($project);
        $this->manager->flush();

        // On retourne le message de création avec l'id du projet
        return $this->json(
            ['message' => "Project resource created with {$project->getId()} id"], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): Response
    {
        // On va chercher le projet avec l'id demandé
        $project = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le projet existe
        if(!$project)
        {
            // On envoie un message s'il n'existe pas avec l'id demandé
            throw new \Exception("No Project found for {$id}");
        }

        return $this->json(['message' => "A Project was found: {$project->getTitle()}, for {$project->getId()} id"]);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id): Response
    {
        // On va chercher le projet avec l'id demandé
        $project = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le projet existe
        if(!$project)
        {
            // On envoie un message s'il n'existe pas avec l'id demandé
            throw new \Exception("No Project found for {$id}");
        }

        // On change le nom du projet
        $project->setTitle ('PixelVerse Studio updated');

        //On envoie en base de donnée
        // Pas besoin de remettre le persist puisqu'on a récupéré les données de la BDD avec le findOneBy.
        $this->manager->flush();

        // On retoune vers show pour voir la mise à jour
        return $this->redirectToRoute('app_api_project_show', ['id' => $project->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        // On va chercher le projet avec l'id demandé
        $project = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le projet existe
        if(!$project)
        {
            // On envoie un message s'il n'existe pas avec l'id demandé
            throw new \Exception("No Project found for {$id}");
        }

        // On supprime le projet
        $this->manager->remove($project);

        //On envoie en base de donnée
        // Pas besoin de remettre le persist puisqu'on a récupéré les données de la BDD avec le findOneBy.
        $this->manager->flush();

        // On retourne un message pour dire que le projet a bien été supprimé
        // HTTP_NO_CONTENT pour dire que la requète c'est bien executé
        return $this->json(['message' => "Project resource deleted"], Response::HTTP_NO_CONTENT);
    }
}
