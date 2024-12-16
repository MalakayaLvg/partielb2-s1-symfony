<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class EventController extends AbstractController
{
    #[Route('/api/event/all', name: 'app_event_all', methods: ['GET'])]
    public function index(EventRepository $eventRepository,ProfileRepository $profileRepository): Response
    {
        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }
        $profile = $profileRepository->find($user);

        $events = $eventRepository->findAll();

        return $this->json([$profile,$events],200,[],['groups' => ['event:detail']]);
    }

    #[Route('/api/event/create', name: 'app_event_create', methods: 'POST')]
    public function create(EventRepository $eventRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, ProfileRepository $profileRepository): Response
    {
        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }

        $data = json_decode($request->getContent(), true);

        $event = $serializer->deserialize($request->getContent(),Event::class,"json");

        $profile = $profileRepository->find($user);
        $event->setOrganizer($profile);
        $event->setStartDate(new \DateTime($data['startDate']));
        $event->setEndDate(new \DateTime($data['endDate']));

        $manager->persist($event);
        $manager->flush();


        return $this->json($event,201, [], ['groups' => ['event:detail']]);
    }

    #[Route('/api/event/delete/{id}', name: 'app_booking_delete', methods: ['GET'])]
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

//    #[Route('/api/event/delete/{id}', name: 'app_booking_delete', methods: ['GET'])]
//    public function delete(Request $request, Event $event, Security $security, EntityManagerInterface $manager): Response
//    {
//
//
//
//        return $this->json(['Event deleted successfully'], 200);
//
//    }
}
