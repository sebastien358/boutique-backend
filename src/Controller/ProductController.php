<?php

namespace App\Controller;

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
    
    #[Route("/products", methods: ["GET"])]
    public function products(Request $request, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $offset = $request->query->getInt('offset', 0); 
            $limit = $request->query->getInt('limit', 10); 
            $products = $this->productRepository->findMoreProducts($offset, $limit);
            if (!$products) {
                return new JsonResponse(['message' => 'Produit introuvable'], 404);
            }
            $dataProducts = $this->productService->getProductsData($products, $request, $normalizer);
            return new JsonResponse($dataProducts);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route("/products/search", methods: ["GET"])]
    public function searchProducts(Request $request, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $filterSearch = $request->query->get('search');
            if (!$filterSearch) {
                return new JsonResponse(['message' => 'Search : Produit introuvable'], 404);
            }
            $products = $this->productRepository->findBySearch(['search' => $filterSearch]);
            $dataProducts = $this->productService->getProductsData($products, $request, $normalizer);
            return new JsonResponse($dataProducts);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route("/products/filtered/price", methods: ["GET"])]
    public function filteredPrice(Request $request, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $minPrice = $request->query->get('minPrice');
            $maxPrice = $request->query->get('maxPrice');   
            if (!$minPrice) {
                return new JsonResponse(['message' => 'Prix minimum obligatoire'], 404);
            }
            if (!$maxPrice) {
                return new JsonResponse(['message' => 'Prix maximum obligatoire'], 404);
            }
            $products = $this->productRepository->findByPrice($minPrice, $maxPrice);
            $dataProducts = $this->productService->getProductsData($products, $request, $normalizer);
            return new JsonResponse($dataProducts);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route("/products/filtered/category", methods: ["GET"])]
    public function filteredCategory(Request $request, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $category = $request->query->get('category'); 
            if (!$category) {
                return new JsonResponse(['message' => 'catégorie obligatoire'], 404);
            }
            $products = $this->productRepository->findByCategory($category);
            $dataProducts = $this->productService->getProductsData($products, $request, $normalizer);
            return new JsonResponse($dataProducts);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}

?>
