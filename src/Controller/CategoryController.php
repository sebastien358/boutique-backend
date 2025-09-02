<?php

// src/Controller/CategoryController.php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CategoryController extends AbstractController
{
    #[Route("/categories", methods: ["GET"])]
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