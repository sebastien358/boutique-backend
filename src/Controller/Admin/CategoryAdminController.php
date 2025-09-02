<?php 

// src/Controller/Admin/CategoryAdminController.php

namespace App\Controller\Admin;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/admin/category')]
#[IsGranted('ROLE_USER')]
class CategoryAdminController extends AbstractController
{
    #[Route('/list', methods: ['GET'])]
    public function getCategories(CategoryRepository $categoryRepository, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $categories = $categoryRepository->findAll();
            $dataCategories = $normalizer->normalize($categories, 'json', ['groups' => 'product']);
            return new JsonResponse($dataCategories);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}

