<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class LoginController extends AbstractController
{
    /**
     * Authentification et récupération du token JWT.
     * Cette méthode ne sera jamais exécutée car le firewall 'login' intercepte la requête le succès (lexik_jwt).
     * Elle sert uniquement à définir la route et la documentation.
     */
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Identifiants de connexion',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'admin@bookapi.com'),
                new OA\Property(property: 'password', type: 'string', example: 'password')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Token JWT généré avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Identifiants invalides'
    )]
    #[OA\Tag(name: 'Auth')]
    public function login(): JsonResponse
    {
        // Si le code arrive ici, c'est que le firewall n'a pas intercepté la requête (mauvaise config)
        // Ou que vous testez la méthode directement sans passer par le firewall
        return new JsonResponse(['message' => 'Cette route est gérée par le Firewall JWT'], 401);
    }
}
