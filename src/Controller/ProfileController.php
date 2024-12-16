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

class ProfileController extends AbstractController
{
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
