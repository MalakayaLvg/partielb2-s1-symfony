<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
use App\Entity\AuthenticatedUsers;
use App\Form\RegistrationFormType;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SerializerInterface $serializer, UserRepository $userRepository): Response
    {
        $user = $serializer->deserialize($request->getContent(),User::class, 'json');

        $userExists = $userRepository->findOneBy(["username"=>$user->getUsername()]);
        if ($userExists) {
            return $this->json("username already exists", 300);
        }
        /** @var string $plainPassword */
        $plainPassword = $user->getPassword();

        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

        $entityManager->persist($user);
        $entityManager->flush();


        $profile = new Profile();
        $profile->setId($user->getId());
        $profile->setUserProfile($user);
        $user->setProfile($profile);


        $entityManager->persist($profile);
        $entityManager->flush();

        return $this->json($user, 201, [], ['groups'=>['user:detail']]);

    }


}
