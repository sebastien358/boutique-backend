<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ProductController extends AbstractController
{
    public function __construct(
        private ProductService $productService,
        private ProductRepository $productRepository,
    ){}
    
    #[Route("/product", methods: ["GET"])]
    public function products(Request $request, ProductRepository $productRepository, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $products = $productRepository->findAll();
            if (!$products) {
                return new JsonResponse(['message' => 'Produit introuvable'], 404);
            }
            $dataProducts = $this->productService->getProductsData($products, $request, $normalizer);
            return new JsonResponse($dataProducts);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/product/search', methods: ['GET'])]
    public function searchProducts(Request $request, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $filterSearch = trim($request->query->get('search'));
            if (!$filterSearch) {
                return new JsonResponse(['error' => 'Recherche obligatoire'], 404);
            }
            $products = $this->productRepository->findByFilters(['search' => $filterSearch]);
            $dataProducts = $this->productService->getProductsData($products, $request, $normalizer);
            return new JsonResponse($dataProducts);
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/product/filtered/price', methods: ['GET'])]
    public function filteredPrice(Request $request, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $minPrice = $request->query->get('minPrice');
             if (!$minPrice) {
                return new JsonResponse(['error' => 'Prix minimum obligatoire'], 404);
            }
            $maxPrice = $request->query->get('maxPrice');
            if (!$maxPrice) {
                return new JsonResponse(['error' => 'Prix maximum obligatoire'], 404);
            }
            $products = $this->productRepository->findByPrice($minPrice, $maxPrice);
            $dataProducts = $this->productService->getProductsData($products, $request, $normalizer);
            return new JsonResponse($dataProducts);
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        } 
    }

    #[Route('/product/filtered/category', methods: ['GET'])]
    public function filteredCategory(Request $request, CategoryRepository $categoryRepository, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $category = $request->query->get('category');
            if (!$category) {
                return new JsonResponse(['error' => 'Catégorie obligatoire'], 404);
            }
            $products = $this->productRepository->findByCategory($category);
            $dataProducts = $this->productService->getProductsData($products, $request, $normalizer);
            return new JsonResponse($dataProducts, 200);
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        } 
    }
}

?>
