<?php

namespace App\Controller\Admin;

use App\Entity\Picture;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\PictureRepository;
use App\Repository\ProductRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
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
    private EntityManagerInterface $entityManager,
    private FileUploader $fileUploader
  ) {}

  #[Route('/product', methods: ['GET'])]
  public function products(Request $request, NormalizerInterface $normalizer): JsonResponse
  {
    try {
      $products = $this->productRepository->findAll();
      if(!$products) {
        return new JsonResponse(['message' => 'Produits introuvables'], 404);
      }
      $dataProducts = $normalizer->normalize($products, 'json', ['groups' => 'product', 'circular_reference_handler' => function ($object) {
          return $object->getId();
        }
      ]);
      $picturePath = $request->getSchemeAndHttpHost() . '/images/';
      foreach ($dataProducts as &$product) {
        if (isset($product['pictures'])) {
          foreach ($product['pictures'] as &$picture) {
            if (isset($picture['filename'])) {
              $picture['url'] = $picturePath . $picture['filename'];
            }
          }
        }
      }
      return new JsonResponse($dataProducts);
    } catch (\Exception $e) {
      return new JsonResponse(['message' => false, 'error' => $e->getMessage()], 500);
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
        if (!$category) {
          return new JsonResponse(['error' => 'La catégorie sélectionnée n\'existe pas'], 400);
        }
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
        return new JsonResponse(['message' => 'Le produit a bien été ajouté']);
      } else {
        return new JsonResponse($this->getErrorMessages($form), 400);
      }
    } catch (\Exception $e) {
      return new JsonResponse(['message' => 'Erreur de l\'envoi d\'un produit', 'error' => $e->getMessage()], 500);
    }
  }

  #[Route('/product/{id}', methods: ['GET'])]
  public function productId(int $id, NormalizerInterface $normalizer, Request $request): JsonResponse
  {
    try {
      $product = $this->productRepository->find($id);
      if (!$product) {
        return new JsonResponse(['error' => 'Produit non trouvé'], 404);
      }
      $dataProduct = $normalizer->normalize($product, 'json', ['groups' => ['product', 'pictures'], 'circular_reference_handler' => function ($object) {
        return $object->getId();
      }
      ]);
      $picturePath = $request->getSchemeAndHttpHost(). '/images/';
      if (isset($dataProduct['pictures'])) {
        foreach ($dataProduct['pictures'] as &$picture) {
          if (isset($picture['filename'])) {
            $picture['filename'] = $picturePath . $picture['filename'];
          }
        }
      }
      return new JsonResponse($dataProduct);
    } catch (\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  #[Route('/product/{productId}/image/{pictureId}', methods: ['DELETE'])]
  public function deleteImage(int $productId, int $pictureId, PictureRepository $pictureRepository): JsonResponse
  {
    try {
      $product = $this->productRepository->find($productId);
      if (!$product) {
        return new JsonResponse(['message' => 'Produit est introuvable'], 404);
      }
      $picture = $pictureRepository->find($pictureId);
      if (!$picture) {
        return new JsonResponse(['message' => 'Image introuvable'], 404);
      }
      if ($picture->getProduct()->getId() !== $productId) {
        return new JsonResponse(['message' => 'L\'image ne correspont pas au produit'], 404);
      }
      $filePath = $this->getParameter('images_directory') . '/' . $picture->getFilename();
      if (file_exists($filePath)) {
        unlink($filePath);
      }
      $product->removePicture($picture);
      $this->entityManager->remove($picture);
      $this->entityManager->flush();
      return new JsonResponse(['message' => 'Image supprimée avec succès'], 200);
    } catch(\Exception $e) {
      return new JsonResponse(['message' => 'Erreur de la suppresion d\'un produit', 'error' => $e->getMessage()], 500);
    }
  }

 #[Route('/product/update/{id}', methods: ['POST'])]
  public function updateProduct(int $id, Request $request): JsonResponse
  {
    try {
      $product = $this->productRepository->find($id);
      if (!$product) {
        return new JsonResponse(['message' => 'Le produit n\'existe pas'], 404);
      }
      $form = $this->createForm(ProductType::class, $product);
      $form->submit($request->request->all());
      if ($form->isValid() && $form->isSubmitted()) {      
        $category = $form->get('category')->getData();
        if (!$category) {
          return new JsonResponse(['error' => 'La catégorie sélectionnée n\'existe pas'], 400);
        }
        $product->setCategory($category);                        
        $newPictures = $request->files->get('filename', []);
        if (!empty($newPictures)) {
          foreach ($newPictures as $picture) {
            $newFilename = $this->fileUploader->upload($picture);
            $picture = new Picture();
            $picture->setFilename($newFilename);
            $product->addPicture($picture);
            $this->entityManager->persist($picture);
          }
        }                                       
        $this->entityManager->flush();
        return new JsonResponse(['message' => 'Le produit a bien été modifié'], 200);
      } else {
        return new JsonResponse($this->getErrorMessages($form), 400);
      }
    } catch (\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  #[Route('/product/delete/{id}', methods: ['DELETE'])]
  public function deleteProduct(int $id): JsonResponse
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
      return new JsonResponse(['message' => 'Produit produit supprimé'], 200);
    } catch (\Exception $e) {
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
