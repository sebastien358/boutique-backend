<?php

namespace App\Controller\admin;

use App\Entity\Picture;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\UploadProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/admin')]
class ProductAdminController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private UploadProvider $uploadProvider,
        private EntityManagerInterface $entityManager
    ){
    }

    #[Route('/products', methods: ['GET'])]
    public function index(Request $request, NormalizerInterface $normalizer): JsonResponse
    {
        $title = $request->query->has('title') ? $request->query->get('title') : null;

        $products = $this->productRepository->findFiltersProducts($title);
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

    #[Route('/new-product', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $product = new Product();

        foreach ($request->files->all() as $file) {
            $fileName = $this->uploadProvider->upload($file);
            $picture = new Picture();
            $picture->setFileName($fileName);
            $product->addPicture($picture);
        }
        $form = $this->createForm(ProductType::class, $product);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        } else {
            return new JsonResponse($this->getErrorMessages($form));
        }

        return new JsonResponse();
    }

    #[Route('/product/{id}', methods: ['GET'])]
    public function show(Request$request, Product $product, NormalizerInterface $normalizer): JsonResponse
    {
        $data = $normalizer->normalize($product, 'json', ['groups' => 'product']);
        $pictures = [];
        foreach ($product->getPictures() as $picture) {
            $pictures[] = ['id' => $picture->getId(), 'url' => $request->getUriForPath('/images/') . $picture->getFileName()];
        }
        $data['pictures'] = $pictures;

        return new JsonResponse($data);
    }

    #[Route('/edit-product/{id}', methods: ['POST'])]
    public function edit(Request $request, Product $product, NormalizerInterface $normalizer): JsonResponse
    {
        foreach ($request->files->all() as $file) {
            $fileName = $this->uploadProvider->upload($file);
            $picture = new Picture();
            $picture->setFileName($fileName);
            $product->addPicture($picture);
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $this->entityManager->flush();
        } else {
            return new JsonResponse($this->getErrorMessages($form));
        }

        $data = $normalizer->normalize($product, 'json', ['groups' => 'product']);
        $pictures = [];
        foreach ($product->getPictures() as $picture) {
            $pictures[] = ['id' => $picture->getId(), 'url' => $request->getUriForPath('/images/') . $picture->getFileName()];
        }
        $data['pictures'] = $pictures;

        return new JsonResponse($data);
    }

    #[Route('/delete-product/{id}', methods: ['DELETE'])]
    public function delete(Product $product): JsonResponse
    {
        $fileSystem = new Filesystem();

        foreach ($product->getPictures() as $picture) {
            $fileSystem->remove($this->getParameter('picture_path') . $picture->getFileName());
        }
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return new JsonResponse();
    }

    #[Route('/delete-picture/{id}', methods: ['DELETE'])]
    public function picture(Picture $picture): JsonResponse
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->getParameter('picture_path') . $picture->getFileName());
        $this->entityManager->remove($picture);
        $this->entityManager->flush();

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