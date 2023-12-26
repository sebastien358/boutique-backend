<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
    ){
    }

    #[Route('/products', methods: ['GET'])]
    public function index(Request $request, NormalizerInterface $normalizer): JsonResponse
    {
        $title = $request->query->has('title') ? $request->query->get('title') : null;
        $price = $request->query->has('price') ? $request->query->get('price') : null;
        $category = $request->query->has('category') ? $request->query->get('category') : null;

        $products = $this->productRepository->findFiltersProducts($title, $price, $category);
        $productData = $normalizer->normalize($products, 'json', ['groups' => 'products']);

        foreach ($productData as $key => $data) {
            $product = $this->productRepository->find($data['id']);
            $pictures = [];
            foreach ($product->getPictures() as $picture) {
                $pictures[] = $request->getUriForPath('/images/') . $picture->getFileName();
            }
            $productData[$key]['pictures'] = $pictures;
        }

        return new JsonResponse($productData);
    }
}