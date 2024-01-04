<?php

namespace App\Controller;

use App\Form\ResetPasswordType;
use App\Service\MailerProvider;
use DateTime;
use App\Entity\User;
use App\Form\RequestResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController {

    public function __construct(
        private MailerProvider $mailerProvider,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager
    ){
    }

    #[Route('/request/reset-password', methods: ['POST'])]
    public function requestPassword(Request $request): JsonResponse
    {
        $form = $this->createForm(RequestResetPasswordType::class);
        $form->submit($request->request->all());

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $request->request->get('email')]);
        if (!$user) {
            throw new \Exception('Aucun compte ne correspond à cet identifiant');
        }

        $token = uniqid();
        $user->setToken($token);
        $user->setRequestResetPasswordAt(new DateTime('now'));

        $url = $this->getParameter('frontend_url') . '/reset-password/' . $token;

        $body = $this->render('emails/reset-password.html.twig', [
            'url' => $url,
            'username' => $user->getFirstName()
        ])->getContent();

        $this->mailerProvider->sendEmail($user->getEmail(), 'Vous avez fait une demande de réinitialisation de mot de passe', $body);

        if ($form->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } else {
            return new JsonResponse($this->getErrorMessages($form));
        }

        return new JsonResponse();
    }

    #[Route('/reset-password/{token}', methods: ['POST'])]
    public function resetPassword(Request $request, string $token): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['token' => $token]);
        if (!$user) {
            throw new \Exception('Le token n\'existe pas');
        }

        $interval = $user->getRequestResetPasswordAt()->diff(new DateTime('now'));
        if ($interval->format('%h%') > 2) {
            throw new \Exception('Le token n\'est plus valide');
        }

        $form = $this->createForm(ResetPasswordType::class);
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