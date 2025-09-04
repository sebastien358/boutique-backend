<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository
    ){   
    }

    #[Route('/users', methods: ['GET'])]
    public function users(NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $users = $this->userRepository->findAll();
            if (!$users) {
                return new JsonResponse(['message' => 'Utilisateur introuvable'], 404);
            }
            $dataUsers = $normalizer->normalize($users, 'json', ['groups' => 'users']);
            return new JsonResponse($dataUsers);
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }  
    }

    #[Route('/api/emailExists', methods: ['POST'])]
    public function emailExists(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $data['email'] ?? null;
            if (!$user) {
                return new JsonResponse(['message' => 'Email utilisateur introuvable'], 404);
            }
            $userExists = $this->userRepository->findOneBy(['email' => $user]);
            if ($userExists) {
                return new JsonResponse(['exists' => true]);
            } else {
                return new JsonResponse(['exists' => false]);
            }
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
