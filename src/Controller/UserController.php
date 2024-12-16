<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use OpenApi\Attributes as OA;

class UserController extends AbstractController
{
    #[OA\Get(
        path: '/api/user/all',
        description: 'Returns a list of all users. Requires a valid JWT token for authentication.',
        summary: 'Retrieve all users',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of users retrieved successfully',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'username', type: 'string', example: 'john_doe'),
                            new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Invalid or missing JWT token',
                content: new OA\JsonContent(
                    type: 'string',
                    example: 'JWT Token not found'
                )
            )
        ]
    )]
    #[Route('/api/user/all', name: 'app_user_all', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->json($users,200,[],['groups'=>'user:list']);
    }
}
