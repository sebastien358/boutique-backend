<?php

namespace App\Controller\admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/admin')]
class UserAdminController extends AbstractController {
    #[Route('/me')]
    public function me(NormalizerInterface $normalizer): JsonResponse
    {
        $user = $this->getUser();
        $data = $normalizer->normalize($user, 'json', ['groups' => 'user']);

        return new JsonResponse($data);
    }
}