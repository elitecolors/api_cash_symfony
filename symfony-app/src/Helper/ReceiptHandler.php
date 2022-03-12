<?php

namespace App\Helper;

use App\Entity\DiscountReceipt;
use App\Entity\Product;
use App\Entity\ProductsReceiptDetail;
use App\Entity\Receipt;
use Doctrine\ORM\EntityManagerInterface;

class ReceiptHandler
{
    private EntityManagerInterface $entityManager;
    private ProductHandler $productHandler;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProductHandler $productHandler
    ) {
        $this->entityManager = $entityManager;
        $this->productHandler = $productHandler;
    }

    public function createReceipt()
    {
        $receipt = new Receipt();
        $receipt->setStatus(Receipt::STATUS_RECEIPT[0]);
        $receipt->setDate(new \DateTime());
        $receipt->setCode(null);

        $this->entityManager->persist($receipt);
        $this->entityManager->flush();
    }

    private function addProductDetaisToReceipt(Receipt $receipt, Product $product)
    {
        $receiptDetail = $this
            ->entityManager
            ->getRepository(ProductsReceiptDetail::class)
            ->findOneBy(
                ['receipt' => $receipt, 'product' => $product]
            );

        if (!$receiptDetail) {
            $receiptDetail = new ProductsReceiptDetail();
        }

        $receiptDetail->setReceipt($receipt);
        $receiptDetail->setProduct($product);
        $receiptDetail->setQuantity($receiptDetail->getQuantity() ? $receiptDetail->getQuantity() + 1 : 1);
        $receiptDetail->setDate(new \DateTime());
        $this->entityManager->persist($receiptDetail);
        $this->entityManager->flush();
    }

    public function updateStatusReceipt(Receipt $receipt, string $status): self
    {
        $receipt->setStatus($status);

        return $this;
    }

    public function getReceiptByCode(string $code): ?Receipt
    {
        $repo = $this->entityManager->getRepository(Receipt::class);

        return $repo->findOneBy(['code' => $code]);
    }

    public function addProductReceipt(string $codeReceipt, string $barcode): bool
    {
        $receipt = $this->getReceiptByCode($codeReceipt);
        $product = $this->productHandler->getProduct($barcode);

        if (!$receipt || !$product) {
            return false;
        }

        $this->addProductDetaisToReceipt($receipt, $product);

        return true;
    }

    public function updateProduct(int $newQty): bool
    {
        // get last receipt product
        $repo = $this->entityManager->getRepository(ProductsReceiptDetail::class);

        /** @var ProductsReceiptDetail $lastProductData */
        $lastProductData = $repo->findOneBy([], ['date' => 'DESC']);

        if (!$lastProductData) {
            return false;
        }

        $lastProductData->setQuantity($newQty);
        $this->entityManager->persist($lastProductData);
        $this->entityManager->flush();

        return true;
    }

    public function finishReceipt(?string $code = null): bool
    {
        $repository = $this->entityManager->getRepository(Receipt::class);
        if ($code) {
            $receipt = $this->getReceiptByCode($code);
        } else {
            // get last receipt
            $receipt = $this->getLastReceipt();
        }

        if (!$receipt) {
            return false;
        }

        $receipt->setStatus(Receipt::STATUS_RECEIPT[1]);

        $this->entityManager->persist($receipt);
        $this->entityManager->flush();

        return true;
    }

    public function getDataReceipt(?string $code = null): array
    {
        if ($code) {
            $receipt = $this->getReceiptByCode($code);
        } else {
            $receipt = $this->getLastReceipt();
        }

        $receiptDetail = $receipt->getProductsReceiptDetails();

        $dataFiltred = [];

        foreach ($receiptDetail as $data) {
            $discount = $this->getDiscount($data->getReceipt(), $data->getProduct());
            $discountCost = $discount?->getProduct()->getCost();

            /**
             * discount rule if exist
             * the 3 rd is for free.
             */
            $totalWithoutVat = $discount ? $data->getProduct()->getCost() * ($data->getQuantity() - 1) : $data->getProduct()->getCost() * $data->getQuantity();
            $totalWithVat = $discount ? $this->priceWithVat($data->getProduct()->getVat(), $data->getProduct()->getCost() * ($data->getQuantity() - 1)) : $this->priceWithVat($data->getProduct()->getVat(), $data->getProduct()->getCost() * $data->getQuantity());

            $dataFiltred[] = [
                'product_name' => $data->getProduct()->getName(),
                'product_amount' => $data->getQuantity(),
                'cost_product_cost_without_vat' => $data->getProduct()->getCost(),
                'cost_product_total_without_vat' => number_format($totalWithoutVat, 2),
                'cost_product_total_wit_vat' => number_format($totalWithVat, 2),
                ($discount ? ['discount_product' => $discount->getProduct()->getName(), 'discount_cost' => $discountCost] : []),
            ];

            $discount = null;
        }

        return $dataFiltred;
    }

    private function getLastReceipt(): Receipt
    {
        $repository = $this->entityManager->getRepository(Receipt::class);

        return $repository->findOneBy([], ['date' => 'DESC']);
    }

    private function priceWithVat(int $vatClass, float $priceExcludingVat): float
    {
        $vatComponent = ($priceExcludingVat / 100) * $vatClass;
        $endPrice = $priceExcludingVat + $vatComponent;

        return number_format($endPrice, 2);
    }

    public function addDiscount(string $barcode): bool
    {
        $receiptDetails = $this->entityManager->getRepository(ProductsReceiptDetail::class);
        $db = $receiptDetails->createQueryBuilder('p');

        $result = $db
            ->leftJoin('p.product', 'pr')
            ->groupBy('p.receipt')
            ->where('p.quantity >= 0')
            ->andWhere('pr.barcode  = :barcode')
            ->setParameter('barcode', $barcode)
            ->getQuery()->getResult();

        /** @var ProductsReceiptDetail $data */
        foreach ($result as $data) {
            $discount = new DiscountReceipt();
            $discount->setReceipt($data->getReceipt());
            $discount->setProduct($data->getProduct());
            $this->entityManager->persist($discount);
        }
        $this->entityManager->flush();

        return true;
    }

    private function getDiscount(Receipt $receipt, Product $product): ?DiscountReceipt
    {
        $repo = $this
            ->entityManager
            ->getRepository(DiscountReceipt::class);
        $discount = $repo->findOneBy(['receipt' => $receipt, 'product' => $product]);

        return $discount ?? null;
    }

    public function getTotalTurnover(): array
    {
        $repo = $this->entityManager->getRepository(ProductsReceiptDetail::class);
        $db = $repo->createQueryBuilder('pd');

        $query = $db
            ->addSelect('hour(pd.date) as hourDate,sum(p.cost) as total')
            ->leftJoin('pd.product', 'p')
            ->groupBy('pd.receipt')
            ->groupBy('hourDate');

        $result = $query
            ->getQuery()
            ->getResult();

        return $result ? ['hour' => $result[0]['hourDate'],
            'total' => $result[0]['hourDate'], ] : [];
    }

    public function removeProduct(string $barcode, ?string $codeReceipt = null): bool
    {
        if (!$codeReceipt) {
            $receipt = $this->getLastReceipt();
        } else {
            $receipt = $this->getReceiptByCode($barcode);
        }

        /*
         * firstly we check if status not finished
         */

        if ($receipt->getStatus() == Receipt::STATUS_RECEIPT[1]) {
            return false;
        }

        $product = $this->productHandler->getProduct($barcode);

        $repoReceiptDetail = $this
            ->entityManager
            ->getRepository(ProductsReceiptDetail::class);

        $rowProductReceipt = $repoReceiptDetail
            ->findOneBy(
                ['receipt' => $receipt, 'product' => $product]
            );

        if ($rowProductReceipt) {
            $this->entityManager->remove($rowProductReceipt);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }
}
