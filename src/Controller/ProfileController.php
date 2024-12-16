<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use OpenApi\Attributes as OA;

class ProfileController extends AbstractController
{
    #[OA\Get(
        path: '/api/profile/get',
        description: 'Retrieve the profile of the currently authenticated user.',
        summary: 'Get user profile',
        tags: ['Profile'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'display_name', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'bio', type: 'string', example: 'This is my bio.'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Token missing or invalid',
                content: new OA\JsonContent(type: 'string', example: 'You must be logged in to make this request.')
            )
        ]
    )]
    #[Route('/api/profile/get', name: 'app_profile', methods: 'GET')]
    public function index(ProfileRepository $profileRepository, Security $security): Response
    {
        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }
        $profile = $user->getProfile();


        return $this->json($profile,200,[],['groups'=>['profile:get']]);
    }


    #[OA\Put(
        path: '/api/profile/edit/{id}',
        description: 'Edit the display name of a user profile. Requires authentication.',
        summary: 'Edit user profile',
        requestBody: new OA\RequestBody(
            description: 'Profile data to update',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'display_name', type: 'string', example: 'New Display Name')
                ],
                type: 'object'
            )
        ),
        tags: ['Profile'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the profile to edit',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Profile updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Profile updated!'),
                        new OA\Property(property: 'profile', type: 'object')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request - Invalid input'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Token missing or invalid'
            )
        ]
    )]
    #[Route('/api/profile/edit/{id}', name: 'app_profile_edit', methods: 'PUT')]
    public function edit(Request $request, Profile $profile, EntityManagerInterface $manager): Response
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['display_name'])) {
            $profile->setDisplayName($data['display_name']);
        }

        $manager->persist($profile);
        $manager->flush();

        return $this->json(['Profile updated !',$profile], 201, [], ['groups'=>['profile:get']]);

    }
}
