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
        $categoryDesktop = new Category();
        $categoryDesktop->setName('Desktop');
        $manager->persist($categoryDesktop);

        $categoryGamer = new Category();
        $categoryGamer->setName('Gamer');
        $manager->persist($categoryGamer);

        $categoryStreaming = new Category();
        $categoryStreaming->setName('Streaming');
        $manager->persist($categoryStreaming);

        // Créer des produits
        $product1 = new Product();
        $product1->setTitle('Ordinateur de bureau');
        $product1->setDescription('Un ordinateur de bureau puissant');
        $product1->setPrice(1000);
        $product1->setCategory($categoryDesktop);
        $manager->persist($product1);

        $product2 = new Product();
        $product2->setTitle('Ordinateur gamer');
        $product2->setDescription('Un ordinateur gamer puissant');
        $product2->setPrice(2000);
        $product2->setCategory($categoryGamer);
        $manager->persist($product2);

        $product3 = new Product();
        $product3->setTitle('Ordinateur de streaming');
        $product3->setDescription('Un ordinateur pour le streaming');
        $product3->setPrice(1500);
        $product3->setCategory($categoryStreaming);
        $manager->persist($product3);

        $manager->flush();
    }
}


