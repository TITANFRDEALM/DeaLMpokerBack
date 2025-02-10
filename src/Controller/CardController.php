<?php

namespace App\Controller;

use App\Entity\Nft;
use App\Repository\NftRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/card', name:'app_api_card')]
final class CardController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private NftRepository $repository)
    {
    }

    #[Route(name: 'new', methods: 'POST')]
    public function new(): Response
    {
        $nft = new Nft();
        $nft->setName('TITAN2');
        $nft->setFirstname('Maxime2');

        $this->manager->persist($nft);
        $this->manager->flush();

        return $this->json(
            ['message' => "Card resource created with {$nft->getId()} id"],
            Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): Response
        {
        $nft = $this->repository->findOneBy(['id' => $id]);
        if (!$nft) {
            throw new \Exception("No Card found for {$id} id");
        }
        return $this->json(
            ['message' => "A Card was found : {$nft->getName()} for {$nft->getId()} id"]
        );
    } 

    #[Route('/{id}', name:'edit', methods: 'PUT')]
    public function edit(int $id): Response
    {
        $nft = $this->repository->findOneBy(['id' => $id]);
        if (!$nft) {
            throw new \Exception("No Card found for {$id} id");
        }

        $nft->setName("Card updated");
        $this->manager->flush();

        return $this->redirectToRoute('app_api_card_show', ['id' => $nft->getId()]);
    }

    #[Route('/{id}', name:'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        $nft = $this->repository->findOneBy(['id' => $id]);
        if (!$nft) {
            throw new \Exception("No Card found for {$id} id");
        }

        $this->manager->remove($nft);
        $this->manager->flush();

        return $this->json(
            ['message' => "A Card deleted"], Response::HTTP_NO_CONTENT);
    }
}