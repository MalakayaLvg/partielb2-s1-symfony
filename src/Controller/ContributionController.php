<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Suggestion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

class ContributionController extends AbstractController
{
    #[OA\Post(
        path: '/api/event/{id}/add/suggestion',
        description: 'Allows an authenticated user to add a suggestion to a specific event.',
        summary: 'Add a suggestion to an event',
        requestBody: new OA\RequestBody(
            description: 'Suggestion details',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Better Music'),
                    new OA\Property(property: 'description', type: 'string', example: 'Add more variety to the music playlist')
                ],
                type: 'object'
            )
        ),
        tags: ['Suggestions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the event to add the suggestion to',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 201, description: 'Suggestion added successfully'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    #[Route('/api/event/{id}/add/suggestion', name: 'app_contributions_add_suggestion', methods: ['POST'])]
    public function addSuggestion(Event $event, Request $request, SerializerInterface $serializer, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }
        $profile = $user->getProfile();
        $contributions = $event->getContributions();


        $suggestion = $serializer->deserialize($request->getContent(),Suggestion::class,"json");
        $suggestion->setEventContributions($contributions);
        $suggestion->setProfile($profile);


        $manager->persist($suggestion);
        $manager->flush();

        return $this->json(['Suggestion added to event',$suggestion], 400,[], ['groups'=>['suggestion:detail']]);

    }


    #[OA\Put(
        path: '/api/suggestion/edit/{id}',
        description: 'Allows the suggestion author or the event organizer to edit a suggestion.',
        summary: 'Edit a suggestion',
        requestBody: new OA\RequestBody(
            description: 'Updated suggestion details',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Improved Catering'),
                    new OA\Property(property: 'description', type: 'string', example: 'Include more vegetarian options')
                ],
                type: 'object'
            )
        ),
        tags: ['Suggestions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the suggestion to edit',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Suggestion edited successfully'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    #[Route('/api/suggestion/edit/{id}', name: 'app_suggestion_edit', methods: 'PUT')]
    public function editSuggestion(Suggestion $suggestion, Request $request, EntityManagerInterface $manager, SerializerInterface $serializer): Response
    {

        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }
        $profileId = $user->getProfile()->getId();
        $suggestionProfileId = $suggestion->getProfile()->getId();
        $eventOrganizerId = $suggestion->getEventContributions()->getEvent()->getOrganizer()->getId();

        if (!$suggestionProfileId == ($profileId || $eventOrganizerId)){
            return $this->json(['You must be the organizer of the event or the author of the suggestion to edit this suggestion.',$profileId,$suggestionProfileId,$eventOrganizerId],401);
        }


        $suggestion = $serializer->deserialize($request->getContent(),Suggestion::class,"json");

        $manager->persist($suggestion);
        $manager->flush();

        return $this->json(['Suggestion edited successfully',$suggestion], 200,[], ['groups'=>['suggestion:detail']]);
    }


    #[OA\Delete(
        path: '/api/suggestion/delete/{id}',
        description: 'Allows the suggestion author or the event organizer to delete a suggestion.',
        summary: 'Delete a suggestion',
        tags: ['Suggestions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the suggestion to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Suggestion deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden - Not the author or organizer')
        ]
    )]
    #[Route('/api/suggestion/delete/{id}', name: 'app_suggestion_delete', methods: 'DELETE')]
    public function deleteSuggestion(Suggestion $suggestion, Request $request, EntityManagerInterface $manager): Response
    {

        $user = $this->getUser();
        if (!$user){
            return $this->json('You must be logged in to make this request.',401);
        }
        $profileId = $user->getProfile()->getId();
        $suggestionProfileId = $suggestion->getProfile()->getId();
        $eventOrganizerId = $suggestion->getEventContributions()->getEvent()->getOrganizer()->getId();

        if (!$suggestionProfileId == ($profileId || $eventOrganizerId)){
            return $this->json(['You must be the organizer of the event or the author of the suggestion to delete this suggestion.'],401);
        }

        $manager->remove($suggestion);
        $manager->flush();

        return $this->json(['Suggestion deleted successfully'], 204);
    }
}
