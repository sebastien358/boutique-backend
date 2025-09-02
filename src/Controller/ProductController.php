<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ProductController extends AbstractController
{
    private function getProductsData($products, Request $request, NormalizerInterface $normalizer)
    {
        $dataProducts = $normalizer->normalize($products, 'json', [
            'groups' => 'product', 'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        $picturePath = $request->getSchemeAndHttpHost() . '/images/';
        foreach ($dataProducts as &$product) {
            if (isset($product['pictures'])) {
                foreach ($product['pictures'] as &$picture) {
                    if (isset($picture['filename']))
                    $picture['url'] = $picturePath . $picture['filename'];
                }
            }
        }

        return $dataProducts;
    }
    
    #[Route("/product", methods: ["GET"])]
    public function products(Request $request, ProductRepository $productRepository, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $products = $productRepository->findAll();
            if (!$products) {
                return new JsonResponse(['message' => 'Produits introuvables'], 404);
            }

            $dataProducts = $this->getProductsData($products, $request, $normalizer);
            return new JsonResponse($dataProducts);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/product/search', methods: ['GET'])]
    public function searchProducts(Request $request, ProductRepository $productRepository, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $filterSearch = $request->query->get('search');
            $products = $productRepository->findByFilters(['search' => $filterSearch]);
            $dataProducts = $this->getProductsData($products, $request, $normalizer);

            return new JsonResponse($dataProducts);
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/product/filtered/price', methods: ['GET'])]
    public function filteredPrice(Request $request, ProductRepository $productRepository, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $minPrice = $request->query->get('minPrice');
            $maxPrice = $request->query->get('maxPrice');
            $products = $productRepository->findByPrice($minPrice, $maxPrice);
            $dataProducts = $this->getProductsData($products, $request, $normalizer);
            
            return new JsonResponse($dataProducts);
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        } 
    }

    #[Route('/product/filtered/category', methods: ['GET'])]
    public function filteredCategory(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $category = $request->query->get('category');
            if (!$category) {
                return new JsonResponse(['error' => 'Catégorie non trouvée'], 404);
            }
            $products = $productRepository->findByCategory($category);

            $dataProducts = $this->getProductsData($products, $request, $normalizer);
            return new JsonResponse($dataProducts, 200);
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        } 
    }
}

?>
