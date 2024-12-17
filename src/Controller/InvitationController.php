<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Repository\EventRepository;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class InvitationController extends AbstractController
{
    #[OA\Post(
        path: '/api/event/{eventId}/invite/{profileId}',
        description: 'Allows the organizer of an event to send an invitation to a specific user profile. Requires authentication.',
        summary: 'Send an invitation to a user for an event',
        tags: ['Invitations'],
        parameters: [
            new OA\Parameter(
                name: 'eventId',
                description: 'ID of the event to invite the user to',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
            new OA\Parameter(
                name: 'profileId',
                description: 'ID of the user profile to invite',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 10)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Invitation sent successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'event', type: 'string', example: 'Music Festival 2024'),
                        new OA\Property(property: 'guest', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'status', type: 'string', example: 'pending'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Token missing or invalid',
                content: new OA\JsonContent(type: 'string', example: 'You must be logged in to make this request.')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - User is not the organizer',
                content: new OA\JsonContent(type: 'string', example: 'You have to be the organizer to invite participant')
            ),
            new OA\Response(
                response: 400,
                description: 'Event is canceled',
                content: new OA\JsonContent(type: 'string', example: 'Event is canceled')
            )
        ]
    )]
    #[Route('/api/event/{eventId}/invite/{profileId}', name: 'invite_user_to_event', methods: 'POST')]
    public function eventSendInvitation(int $eventId,int $profileId, EventRepository $eventRepository, ProfileRepository $profileRepository,EntityManagerInterface $manager): Response
    {

        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }
        $userId = $user->getId();

        $profile = $profileRepository->find($profileId);
        $event = $eventRepository->find($eventId);

        $eventOrganizerId = $event->getOrganizer()->getId();

        if ($userId !== $eventOrganizerId){
            return $this->json('You have to be the organizer to invite participant',401);
        }

        if ($event->isCanceled() === true){
            return $this->json('Event is canceled',401);
        }

        $invitation = new Invitation();
        $invitation->setEvent($event);
        $invitation->setGuest($profile);
        $invitation->setStatus("pending");

        $manager->persist($invitation);
        $manager->flush();

        return $this->json($invitation, 200,[],['groups'=>['invitation:detail']]);
    }





}
