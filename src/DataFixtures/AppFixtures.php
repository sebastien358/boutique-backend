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

        $product4 = new Product();
        $product4->setTitle('Ordinateur de bureau');
        $product4->setDescription('Un ordinateur de bureau puissant');
        $product4->setPrice(1000);
        $product4->setCategory($categoryDesktop);
        $manager->persist($product4);

        $product5 = new Product();
        $product5->setTitle('Ordinateur gamer');
        $product5->setDescription('Un ordinateur gamer puissant');
        $product5->setPrice(2000);
        $product5->setCategory($categoryGamer);
        $manager->persist($product5);

        $product6 = new Product();
        $product6->setTitle('Ordinateur de streaming');
        $product6->setDescription('Un ordinateur pour le streaming');
        $product6->setPrice(1500);
        $product6->setCategory($categoryStreaming);
        $manager->persist($product6);

        $product7 = new Product();
        $product7->setTitle('Ordinateur de streaming');
        $product7->setDescription('Un ordinateur pour le streaming');
        $product7->setPrice(1500);
        $product7->setCategory($categoryStreaming);
        $manager->persist($product7);

        $product8 = new Product();
        $product8->setTitle('Ordinateur de bureau');
        $product8->setDescription('Un ordinateur de bureau puissant');
        $product8->setPrice(1000);
        $product8->setCategory($categoryDesktop);
        $manager->persist($product8);

        $product9 = new Product();
        $product9->setTitle('Ordinateur gamer');
        $product9->setDescription('Un ordinateur gamer puissant');
        $product9->setPrice(2000);
        $product9->setCategory($categoryGamer);
        $manager->persist($product9);

        $product10 = new Product();
        $product10->setTitle('Ordinateur de streaming');
        $product10->setDescription('Un ordinateur pour le streaming');
        $product10->setPrice(1500);
        $product10->setCategory($categoryStreaming);
        $manager->persist($product10);

        $manager->flush();
    }
}


