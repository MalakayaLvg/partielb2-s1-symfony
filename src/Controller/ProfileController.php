<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Invitation;
use App\Entity\Profile;
use App\Repository\EventRepository;
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
        path: '/api/profile/show',
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
    #[Route('/api/profile/show', name: 'app_profile_show', methods: 'GET')]
    public function showProfile(ProfileRepository $profileRepository, Security $security): Response
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
    public function profileEdit(Request $request, Profile $profile, EntityManagerInterface $manager): Response
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['display_name'])) {
            $profile->setDisplayName($data['display_name']);
        }

        $manager->persist($profile);
        $manager->flush();

        return $this->json(['Profile updated !',$profile], 201, [], ['groups'=>['profile:get']]);

    }


    #[OA\Get(
        path: '/api/profile/invitation/all',
        description: 'Retrieve all invitations for the currently authenticated user.',
        summary: 'Get all invitations for the user',
        tags: ['Profile'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of invitations retrieved successfully',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'object'))
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Token missing or invalid')
        ]
    )]
    #[Route('/api/profile/invitation/all', name: 'app_profile_invitation_all', methods: 'GET')]
    public function showAllInvitation(): Response
    {

        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }

        $profile = $user->getProfile();
        $invitations = $profile->getInvitations();

        return $this->json($invitations, 200,[],['groups'=>['invitation:detail']]);
    }


    #[OA\Put(
        path: '/api/profile/invitation/edit/{id}',
        description: 'Allows the guest to update the status of their invitation. Event must not have started.',
        summary: 'Edit an invitation status',
        requestBody: new OA\RequestBody(
            description: 'Updated invitation status',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'accepted', description: 'New status (accepted, denied, pending)')
                ],
                type: 'object'
            )
        ),
        tags: ['Profile'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the invitation to edit',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 201, description: 'Status updated successfully'),
            new OA\Response(response: 400, description: 'Bad request or event already started'),
            new OA\Response(response: 401, description: 'Unauthorized or not the owner of the invitation')
        ]
    )]
    #[Route('/api/profile/invitation/edit/{id}', name: 'app_profile_invitation_edit', methods: 'PUT')]
    public function editInvitation(Request $request, Invitation $invitation, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }
        $profile = $user->getProfile();
        if ($profile->getId() !== $invitation->getGuest()->getId()){
            return $this->json('You are not the owner of this invitation',401);
        }

        $currentDate = new \DateTime();
        $eventStartDate = $invitation->getEvent()->getStartDate();

        if ($currentDate >= $eventStartDate) {
            $invitation->setStatus("denied");
            $manager->persist($invitation);
            $manager->flush();
            return $this->json(['error' => 'Event already started, cant be modified now.'], 400);
        }

        if($invitation->getEvent()->isCanceled() === true){
            return $this->json(['Event is canceled :('], 400);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['status'])) {
            $status = $data['status'];
            switch ($status) {
                case 'accepted':
                case 'denied':
                case 'pending':
                    $invitation->setStatus($status);
                    break;

                default:
                    return $this->json(['error' => 'Invalid status. Accepted value : accepted, denied, pending.'], 400);
            }
        }

        $manager->persist($invitation);
        $manager->flush();

        return $this->json(['Status updated !',$invitation], 201, [], ['groups'=>['invitation:detail']]);

    }


    #[OA\Put(
        path: '/api/profile/my-event/edit/{id}',
        description: 'Allows the event organizer to edit their event details.',
        summary: 'Edit an event as organizer',
        requestBody: new OA\RequestBody(
            description: 'Updated event details',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'description', type: 'string', example: 'Sample Event'),
                    new OA\Property(property: 'place', type: 'string', example: 'Place'),
                    new OA\Property(property: 'canceled', type: 'boolean', example: true, description: 'Cancel the event'),
                    new OA\Property(property: 'starting_date', type: 'string', format: 'date-time', example: '2024-06-01T10:00:00'),
                    new OA\Property(property: 'ending_date', type: 'string', format: 'date-time', example: '2024-06-01T12:00:00'),
                    new OA\Property(property: 'status', type: 'boolean', default: false),
                    new OA\Property(property: 'place_type', type: 'boolean', default: false),
                ],
                type: 'object'
            )
        ),
        tags: ['Profile'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the event to edit',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 201, description: 'Event updated successfully'),
            new OA\Response(response: 400, description: 'Invalid input or request'),
            new OA\Response(response: 401, description: 'Unauthorized or not the organizer')
        ]
    )]
    #[Route('/api/profile/my-event/edit/{id}', name: 'app_profile_my_event_edit', methods: 'PUT')]
    public function editMyEvent(Event $event, Request $request, EntityManagerInterface $manager): Response
    {

        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }
        $profileId = $user->getProfile()->getId();
        $eventOrganizerId = $event->getOrganizer()->getId();

        if ($profileId !== $eventOrganizerId){
            return $this->json(['You must be the organizer to edit this event.'],401);
        }


        $data = json_decode($request->getContent(), true);

        if (isset($data['canceled'])) {
            $event->setCanceled($data['canceled']);
        }
        if (isset($data['starting_date'])) {
            $startDate = new \DateTime($data['starting_date']);
        }
        if (isset($data['ending_date'])) {
            $endDate = new \DateTime($data['ending_date']);
        }

        try {
            $event->setStartDate($startDate);
            $event->setEndDate($endDate);
            $manager->persist($event);
            $manager->flush();

            return $this->json($event,201, [], ['groups' => ['event:detail']]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
