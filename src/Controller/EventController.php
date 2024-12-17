<?php

namespace App\Controller;

use App\Entity\Contributions;
use App\Entity\Event;
use App\Entity\Profile;
use App\Repository\EventRepository;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

use OpenApi\Attributes as OA;

class EventController extends AbstractController
{
    #[OA\Get(
        path: '/api/event/all',
        description: 'Fetch a list of all events. Requires authentication.',
        summary: 'Retrieve all events',
        tags: ['Event'],
        responses: [
            new OA\Response(response: 200, description: 'List of events retrieved'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    #[Route('/api/event/all', name: 'app_event_all', methods: ['GET'])]
    public function index(EventRepository $eventRepository,ProfileRepository $profileRepository): Response
    {
        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }

        $events = $eventRepository->findAll();

        return $this->json([$events],200,[],['groups' => ['event:detail']]);
    }

    #[OA\Post(
        path: '/api/event/create',
        description: 'Creates a new event with the authenticated user as organizer.',
        summary: 'Create a new event',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'description', type: 'string', example: 'Sample Event'),
                    new OA\Property(property: 'place', type: 'string', example: 'Place'),
                    new OA\Property(property: 'startDate', type: 'string', format: 'date-time', example: '2024-06-01T10:00:00'),
                    new OA\Property(property: 'endDate', type: 'string', format: 'date-time', example: '2024-06-01T12:00:00'),
                    new OA\Property(property: 'status', type: 'boolean', default: false),
                    new OA\Property(property: 'place_type', type: 'boolean', default: false),
                ],
                type: 'object'
            )
        ),
        tags: ['Event'],
        responses: [
            new OA\Response(response: 201, description: 'Event created successfully'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    #[Route('/api/event/create', name: 'app_event_create', methods: 'POST')]
    public function create(EventRepository $eventRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, ProfileRepository $profileRepository): Response
    {
        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }

        $data = json_decode($request->getContent(), true);

        $event = $serializer->deserialize($request->getContent(),Event::class,"json");

        $profile = $user->getProfile();
        $event->setCanceled(false);
        $event->setOrganizer($profile);
        $event->setContributions(new Contributions());


        $startDate = new \DateTime($data['startDate']);
        $endDate = new \DateTime($data['endDate']);

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

    #[OA\Delete(
        path: '/api/event/delete/{id}',
        description: 'Deletes an event if the authenticated user is the organizer.',
        summary: 'Delete an event',
        tags: ['Event'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID of the event to delete')
        ],
        responses: [
            new OA\Response(response: 200, description: 'Event deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized or forbidden'),
            new OA\Response(response: 404, description: 'Event not found')
        ]
    )]
    #[Route('/api/event/delete/{id}', name: 'app_booking_delete', methods: ['DELETE'])]
    public function delete(Request $request, Event $event, Security $security, EntityManagerInterface $manager): Response
    {
        if (!$event) {
            return $this->json(['error' => 'booking not found'], 404);
        }

        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }

        $idFromUser = $user->getId();

        $profileFromEvent = $event->getOrganizer();
        $userProfileFromEvent = $profileFromEvent->getUserProfile();
        $idFromEvent = $userProfileFromEvent->getId();

        if ($idFromUser != $idFromEvent){
            return $this->json(['Only event organizer can remove this event',$idFromEvent,$idFromUser],401);
        }


        return $this->json(['Event deleted successfully'], 200);

    }

    #[OA\Get(
        path: '/api/event/public/show/{id}',
        description: 'Retrieve details of a specific event.',
        summary: 'Show event details',
        tags: ['Event'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID of the event')
        ],
        responses: [
            new OA\Response(response: 200, description: 'Event details retrieved'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    #[Route('/api/event/public/show/{id}', name: 'app_event_public_show', methods: ['GET'])]
    public function showEventPublic(Event $event): Response
    {
        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }

        return $this->json([$event], 200,[],['groups'=>['event:detail']]);
    }

    #[OA\Put(
        path: '/api/event/join/{id}',
        description: 'Allows a user to join a public event.',
        summary: 'Join a public event',
        tags: ['Event'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID of the event to join')
        ],
        responses: [
            new OA\Response(response: 200, description: 'User joined the event successfully'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    #[Route('/api/event/join/{id}', name: 'app_event_join', methods: ['PUT'])]
    public function joinEvent(Event $event, EntityManagerInterface $manager): Response
    {

        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }
        $profile = $user->getProfile();

        if (!$event->isStatus()){
            return $this->json('This event is private',401);
        }
        $event->addParticipant($profile);

        $manager->persist($event);
        $manager->flush();

        return $this->json(['Participant add !',$event], 200,[],['groups'=>['event:detail']]);
    }


    #[OA\Get(
        path: '/api/event/private/show/{id}',
        description: 'Retrieve details of a private event. Requires authentication.',
        summary: 'Show private event details',
        tags: ['Event'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the private event to retrieve',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Private event details retrieved'),
            new OA\Response(response: 401, description: 'Unauthorized - Token missing or invalid')
        ]
    )]
    #[Route('/api/event/private/show/{id}', name: 'app_event_private_show', methods: ['GET'])]
    public function showEventPrivate(Event $event): Response
    {
        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }
//        $profile = $user->getProfile();

        return $this->json([$event], 200,[],['groups'=>['event:private']]);
    }

}
