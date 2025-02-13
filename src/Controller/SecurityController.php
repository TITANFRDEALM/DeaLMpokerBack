<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable; // Assurez-vous d'utiliser DateTimeImmutable
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Schema;
use OpenApi\Attributes\Items;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api', name: 'app_api_')]
final class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private LoggerInterface $logger
    ) {
    }

    #[Route('/registration', name: 'registration', methods: 'POST')]
    #[OA\Post(
        path: "/api/registration",
        summary: "Inscription d'un nouvel utilisateur",
        requestBody: new RequestBody(
            required: true,
            description: "Données de l'utilisateur à inscrire",
            content: [
                new MediaType(
                    mediaType: "application/json",
                    schema: new Schema(
                        type: "object",
                        properties: [
                            new Property(property: "email", type: "string", example: "adresse@mail.com"),
                            new Property(property: "password", type: "string", example: "Mot de passe")
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Utilisateur inscrit avec succès',
        content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: "user", type: "string", example: "nom_utilisateur"),
                new Property(property: "apiToken", type: "string", example: "token_aleatoire"),
                new Property(
                    property: "roles",
                    type: "array",
                    items: new Items(type: "string"),
                    example: ["ROLE_USER"]
                ),
            ]
        )
    )]
    
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                throw new BadRequestHttpException(json_encode($errorMessages));
            }

            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
            $user->setCreatedAt(new DateTimeImmutable()); // Correct usage

            $this->manager->persist($user);
            $this->manager->flush();

            return new JsonResponse(
                ['user' => $user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()],
                Response::HTTP_CREATED
            );
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['message' => 'Cette adresse email est déjà utilisée.'], Response::HTTP_CONFLICT);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(json_decode($e->getMessage(), true), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error($e);
            return new JsonResponse(['message' => 'Une erreur est survenue lors de l\'inscription.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/login', name: 'login', methods: 'POST')]
    #[OA\Post(
        path: "/api/login",
        summary: "Connecter un utilisateur",
        requestBody: new RequestBody(
            required: true,
            description: "Données de l'utilisateur",
            content: [
                new MediaType(
                    mediaType: "application/json",
                    schema: new Schema(
                        type: "object",
                        properties: [
                            new Property(property: "email", type: "string", example: "adresse@mail.com"),
                            new Property(property: "password", type: "string", example: "Mot de passe")
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Connexion réussie', content: new JsonContent(
            type: 'object',
            properties: [
                new Property(property: "user", type: "string", example: "nom_utilisateur"),
                new Property(property: "apiToken", type: "string", example: "token_aleatoire"),
                new Property(
                    property: "roles",
                    type: "array",
                    items: new Items(type: "string"),
                    example: ["ROLE_USER"]
                ),
            ]
        ))]
    #[OA\Response(response: 401, description: 'Mauvais credentials')]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(
            ['user' => $user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()],
            Response::HTTP_OK
        );
    }
}