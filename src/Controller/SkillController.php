<?php

namespace App\Controller;

use App\Entity\Skill;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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

    #[Route(name: 'new', methods: 'POST')]
    public function new(): Response
    {
        // Création du skill
        $skill = new Skill();

        // Nom du skill
        $skill->setName(name: 'Symfony');

        // Logo du skill
        $skill->setLogo(logo: 'image/image.png');

        // On envoie en base de donnée
        $this->manager->persist($skill);
        $this->manager->flush();

        // On retourne le message de création avec l'id du skill
        return $this->json(
            ['message' => "Skill resource created whit {$skill->getId()} id"],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): Response
    {
        // On va chercher le skill avce l'id demandé
        $skill = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le skill existe
        if(!$skill){
            // On envoie un message s'il n'existe pas avec l'id demandé
            throw new \Exception("No Skill found for {$id}");
        }

        return $this->json(
            ['message' => "A skill was found: {$skill->getName()}, for {$skill->getId()} id"]
        );
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id): Response
    {
        // On va chercher le skill avce l'id demandé
        $skill = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le skill existe
        if(!$skill){
            // On envoie un message s'il n'existe pas avec l'id demandé
            throw new \Exception("No Skill found for {$id}");
        }

        // Modification du nom du skill
        $skill->setName('Symfony name updated');

        // On encoie en bade de donnée
        $this->manager->flush();

        // On retourne vers show pour voir la mise à jour
        return $this->redirectToRoute('app_api_skill_show', ['id' => $skill->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        // On va chercher le skill avce l'id demandé
        $skill = $this->repository->findOneBy(['id' => $id]);

        // On vérifie si le skill existe
        if(!$skill){
            // On envoie un message s'il n'existe pas avec l'id demandé
            throw new \Exception("No Skill found for {$id}");
        }

        // Supprimer le skill
        $this->manager->remove($skill);

        // On envoie en base de donnée
        $this->manager->flush();

        // On retourne un message pour dire que le skill a bien été supprimé
        return $this->json(['message' => 'Skill resource deleted'], Response::HTTP_NO_CONTENT);
    }
}
