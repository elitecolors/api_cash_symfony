<?php

namespace App\Helper;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductHandler
{
    private EntityManagerInterface $entityManager;
    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    public function addProduct(array $array):bool
    {
        // firstly we check if product exist
        if($this->getProduct($array['barcode']))
        {
            return false;
        }

        $product = new Product();
        $product->setBarcode($array['barcode']);
        $product->setName($array['name']);
        $product->setCost($array['cost']);
        $product->setVat($array['vat'] ?? 6);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
        return true;
    }

    public function getProduct(string $barcode):?Product
    {
        $productRepository =  $this->entityManager->getRepository(Product::class);

        $product = $productRepository->findOneBy(["barcode" => $barcode]);

        return $product ?? null;
    }

    public function getAllProduct(): array
    {
        $repo = $this->entityManager->getRepository(Product::class);
        $list = $repo->findAll();

        return $list ?? [];
    }

}