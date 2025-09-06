<?php 

// src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer des catégories
        $categories = [];
        $categoryNames = ['Desktop', 'Gamer', 'Streaming'];
        foreach ($categoryNames as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
            $categories[] = $category;
        }

        // Créer des produits
        for ($i = 0; $i < 100; $i++) {
            $product = new Product();
            $product->setTitle('Ordinateur ' . $i);
            $product->setDescription('Un ordinateur puissant');
            $product->setPrice(mt_rand(1000, 2000));
            $product->setCategory($categories[mt_rand(0, count($categories) - 1)]);
            $manager->persist($product);
        }

        $manager->flush();
    }
}


