<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    /**
     * @Route("/auth/register", name="register", methods={"POST"})
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $password = $request->get('password');
        $email = $request->get('email');
        $role = $request->get('role');

        if (!$role || !$email || !$password) {
            return $this->json(['requirement' => 'Please check requirement email,password,role'], Response::HTTP_UNAUTHORIZED);
        }

        // check if user exist
        $dbUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($dbUser) {
            return $this->json(['user found' => 'user exist'], Response::HTTP_FORBIDDEN);
        }

        $user = new User();

        $user->setPassword($passwordHasher->hashPassword($user, $password));
        $user->setEmail($email);
        $user->setRoles([$role]);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'user' => $user->getEmail(),
        ]);
    }
}
