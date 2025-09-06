<?php

namespace App\Controller\Admin;

use App\Entity\Picture;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\PictureRepository;
use App\Repository\ProductRepository;
use App\Service\FileUploader;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/admin')]
#[IsGranted('ROLE_USER')]
final class ProductAdminController extends AbstractController
{
  public function __construct(
    private ProductRepository $productRepository,
    private PictureRepository $pictureRepository,
    private EntityManagerInterface $entityManager,
    private ProductService $productService,
    private FileUploader $fileUploader
  ) {}

  #[Route('/products', methods: ['GET'])]
  public function products(Request $request, NormalizerInterface $normalizer, LoggerInterface $logger): JsonResponse
  {
    try {
      $page = $request->query->getInt('page', 1);
      $limit = $request->query->getInt('limit', 4);
      $products = $this->productRepository->findAllProducts($page, $limit);
      $total = $this->productRepository->countAllProducts();
      $dataProducts = $this->productService->getProductsData($products, $request, $normalizer);
      return new JsonResponse([
        'products' => $dataProducts,
        'total' => $total
      ]);
    } catch (\Exception $e) {
      $logger->error($e->getMessage());
      return new JsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  #[Route('/product/{id}', methods: ['GET'])]
  public function product(int $id, Request $request, NormalizerInterface $normalizer): JsonResponse
  {
    try {
      $product = $this->productRepository->find($id);
      if (!$product) {
        return new JsonResponse(['message' => 'Le produit est introuvable']);
      }
      $dataProduct = $this->productService->getProductsData($product, $request, $normalizer);
      return new JsonResponse($dataProduct);
    } catch(\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  #[Route('/product/new', methods: ['POST'])]
  public function addProduct(Request $request): JsonResponse
  {
    try {
      $product = new Product();
      $form = $this->createForm(ProductType::class, $product);
      $form->submit($request->request->all());
      if ($form->isValid() && $form->isSubmitted()) {
        $category = $form->get('category')->getData();
        $product->setCategory($category);
        $images = $request->files->get('filename', []);
        if (!empty($images)) {
          foreach ($images as $image) {
            $newFilename = $this->fileUploader->upload($image);
            $picture = new Picture();
            $picture->setFilename($newFilename);
            $picture->setProduct($product);
            $this->entityManager->persist($picture);
          }
        } 
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        return new JsonResponse(['message' => 'Le produit a bien été ajouté'], 201);
      } else {
        return new JsonResponse([$this->getErrorMessages($form)], 500);
      }
    } catch(\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  #[Route('/product/update/{id}', methods: ['POST'])]
  public function updateProduct(int $id, Request $request): JsonResponse
  {
    try {
      $product = $this->productRepository->find($id);
      $form = $this->createForm(ProductType::class, $product);
      $form->submit($request->request->all());
      if ($form->isValid() && $form->isSubmitted()) {
        $category = $form->get('category')->getData();
        $product->setCategory($category);
        $images = $request->files->get('filename', []);
        if (!empty($images)) {
          foreach ($images as $image) {
            $newFilename = $this->fileUploader->upload($image);
            $picture = new Picture();
            $picture->setFilename($newFilename);
            $product->addPicture($picture);
            $this->entityManager->persist($picture);
          }
        } 
        $this->entityManager->flush();
        return new JsonResponse(['message' => 'Le produit a bien été modifié'], 201);
      } else {
        return new JsonResponse([$this->getErrorMessages($form)], 500);
      }
    } catch(\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  #[Route('/product/{productId}/picture/{pictureId}', methods: ['DELETE'])]
  public function deletePicture(int $productId, int $pictureId): JsonResponse
  {
    try {
      $product = $this->productRepository->find($productId);
      if (!$product) {
        return new JsonResponse(['message' => 'Produit introuvable'], 404);
      }
      $picture = $this->pictureRepository->find($pictureId);
      if (!$picture) {
        return new JsonResponse(['message' => 'Image introuvable'], 404);
      }
      if ($picture->getProduct()->getId() !== $productId) {
        return new JsonResponse(['message' => 'l\'image ne correspond pas au produit'], 404);
      }
      $filePath = $this->getParameter('images_directory') . '/' . $picture->getFilename();
      if (file_exists($filePath)) {
        unlink($filePath);
      }
      $product->removePicture($picture);
      $this->entityManager->remove($picture);
      $this->entityManager->flush();
      return new JsonResponse(['message' => 'L\'image a bien été supprimé'], 200);
    } catch(\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  #[Route('/product/delete/{id}', methods: ['DELETE'])]
  public function delete(int $id): JsonResponse
  {
    try {
      $product = $this->productRepository->find($id);
      if (!$product) {
        return new JsonResponse(['message' => 'Produit introuvable'], 404);
      }
      foreach ($product->getPictures() as $picture) {
        $filePath = $this->getParameter('images_directory') . '/' . $picture->getFilename();
        if (file_exists($filePath)) {
          unlink($filePath);
        }
        $this->entityManager->remove($picture);
      }
      $this->entityManager->remove($product);
      $this->entityManager->flush();
      return new JsonResponse(['message' => 'Le produit a bien été supprimé'], 200);
    } catch(\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  private function getErrorMessages(FormInterface $form): array
  {
    $errors = [];
    foreach ($form->getErrors(true) as $error) {
      $errors[] = $error->getMessage();
      error_log($error->getMessage());
    }
    error_log('Nombre d\'erreurs : ' . count($errors));
    return $errors;
  }
}
