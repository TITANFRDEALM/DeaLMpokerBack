<?php

namespace App\Controller;

use App\Entity\Nft;
use App\Repository\NftRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/card', name:'app_api_card_')]
final class CardController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager, 
        private NftRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator)
    {
    }

    #[Route(methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        $nft = $this->serializer->deserialize($request->getContent(), Nft::class, 'json');

        $this->manager->persist($nft);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($nft,'json');
        $location = $this->urlGenerator->generate(
            'app_api_card_show',
            ['id' => $nft->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new JsonResponse($responseData,
            Response::HTTP_CREATED, ["Location" => $location], true);
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