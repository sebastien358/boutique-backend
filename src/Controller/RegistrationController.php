<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\MailerProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private MailerProvider $mailerProvider,
        private EntityManagerInterface $entityManager
    ){
    }

    #[Route('/registration', methods: ['POST'])]
    public function registration(Request $request): JsonResponse
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $user->setPassword($this->userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            ));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } else {
            return new JsonResponse($this->getErrorMessages($form));
        }

        $url = $this->getParameter('frontend_url') . '/login';

        $body = $this->render('emails/registration.html.twig', [
            'url' => $url
        ])->getContent();

        $this->mailerProvider->sendEmail($user->getEmail(), 'Demande d\'inscription', $body);

        return new JsonResponse();
    }

    private function getErrorMessages($form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $key => $error) {
            $errors[] =$error->getMessage();
        }
        foreach ($form->all() as $child) {
            if ($child->isSubmitted() && !$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}