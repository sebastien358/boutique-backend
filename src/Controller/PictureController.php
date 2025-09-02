<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


final class PictureController extends AbstractController
{
    #[Route("/pictures", methods: ["GET"])]
    public function pictures(Request $request, PictureRepository $pictureRepository, NormalizerInterface $normalizer): JsonResponse
    {
        try {
            $pictures = $pictureRepository->findAll();
            if (!$pictures) {
                return new JsonResponse(['message' => 'Images introuvables'], 404);
            }
            $dataPictures = $normalizer->normalize($pictures, 'json', [
                'groups' => 'product',
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            $picturePath = $request->getSchemeAndHttpHost() . '/images/';
            foreach ($dataPictures as &$picture) {
                $picture['url'] = $picturePath . $picture['filename'];
            }
            return new JsonResponse($dataPictures);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

}

?>