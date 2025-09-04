<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Test\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonDecode;

class RegisterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager, 
    ){}  

    #[Route('/api/register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $user = new User();
            $form = $this->createForm(UserType::class, $user);
            $data = json_decode($request->getContent(), true);
            $form->submit($data);
            if ($form->isValid() && $form->isValid()) {
                $user->setRoles(['ROLE_USER']);
                $user->setPassword($passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                ));
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return new JsonResponse(['message' => 'Utilisateur enregistré'], 201);
            } else {
                return new JsonResponse($this->getErrorMessages($form), 400);
            }
        } catch(\Exception $e) {
            return new JsonResponse(['error' => 'Erreur de l\'enregistrement', $e->getMessage()], 500);
        }
    }

    private function getErrorMessages(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $child) {
            if ($child->isSubmitted() && !$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }
        return $errors;
    }
}
