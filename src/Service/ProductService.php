<?php 

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductService
{
  public function getProductsData($products, Request $request, NormalizerInterface $normalizer)
  {
    if (is_array($products)) {
      $dataProducts = $normalizer->normalize($products, 'json', [
        'groups' => ['product', 'pictures'],
        'circular_reference_handler' => function ($object) {
          return $object->getId();
        }
      ]);

      foreach ($dataProducts as &$product) {
        if (isset($product['pictures']) && is_array($product['pictures'])) {
          foreach ($product['pictures'] as &$picture) {
            if (isset($picture['filename'])) {
                $picture['url'] = $request->getSchemeAndHttpHost() . '/images/' . $picture['filename'];
            }
          }
        }
      }

      return $dataProducts;
  } else {
      $dataProduct = $normalizer->normalize($products, 'json', [
        'groups' => ['product', 'pictures'],
        'circular_reference_handler' => function ($object) {
          return $object->getId();
        }
      ]);

      if (isset($dataProduct['pictures']) && is_array($dataProduct['pictures'])) {
        foreach ($dataProduct['pictures'] as &$picture) {
          if (isset($picture['filename'])) {
            $picture['url'] = $request->getSchemeAndHttpHost() . '/images/' . $picture['filename'];
          }
        }
      }

      return $dataProduct;
    }
  }
}





