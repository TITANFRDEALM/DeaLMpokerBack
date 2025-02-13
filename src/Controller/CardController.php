<?php

namespace App\Controller;

use App\Entity\Nft;
use App\Repository\NftRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Schema;
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
    #[Route('/card', name: 'card', methods: 'POST')]
    #[OA\Post(
        path: "/api/card",
        summary: "NFT à créer",
        requestBody: new RequestBody(
            required: true,
            description: "Données de la carte NFT à créer",
            content: [
                new MediaType(
                    mediaType: "application/json",
                    schema: new Schema(
                        type: "object",
                        properties: [
                            new Property(property: "name", type: "string", example: "titan"),
                            new Property(property: "firstname", type: "string", example: "fr")
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'NFT créée avec succès',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: "id", type: "integer", example: "id"),
                new Property(property: "numero", type: "integer", example: "numéro card"),
                new Property(property: "firstname", type: "string", example: "prenom"),
                new Property(property: "name", type: "string", example: "nom"),
                new Property(property: "createdAt", type: "string", format:"date-time"),
            ]
        )
    )]
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
    #[OA\Get(
        path: "/api/card/{id}",
        summary: "Afficher NFT par son ID",
        parameters: [ // Use 'parameters' for path parameters
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la card à afficher",
                schema: new OA\Schema(type: "integer") // Use OA\Schema for type definition
            )
        ],
        responses: [ // Use 'responses' (plural)
            new OA\Response(
                response: 200,
                description: 'NFT trouvé', // More accurate description
                content: new JsonContent(
                    type: 'object',
                    properties: [
                        new Property(property: "id", type: "integer", example: "123"), // Use a concrete example
                        new Property(property: "numero", type: "integer", example: "456"), // Use a concrete example
                        new Property(property: "firstname", type: "string", example: "John"), // Use a concrete example
                        new Property(property: "name", type: "string", example: "Doe"), // Use a concrete example
                        new Property(property: "createdAt", type: "string", format:"date-time", example: "2024-10-27T10:00:00+00:00"), // Use a concrete example and ISO 8601 format
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'NFT non trouvé'
            )
        ]
    )]

    public function show(int $id): Response
    {
        $nft = $this->repository->findOneBy(['id' => $id]);
        if (!$nft) {
            // It's better to throw a NotFoundHttpException for a 404
            throw $this->createNotFoundException("No Card found for id {$id}");
        }

        return $this->json($nft, 200, [], ['groups' => 'card']); // Serialize the entity, potentially with groups
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