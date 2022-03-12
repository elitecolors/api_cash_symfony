<?php

namespace App\Controller;

use App\Entity\Product;
use App\Helper\ProductHandler;
use App\Helper\ReceiptHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/admin", name="api")
 */
class ApiController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/addProduct", name="_add_product", methods={"POST"})
     */
    public function addProduct(
        Request $request,
        ProductHandler $productHandler
    ): JsonResponse
    {

        try {
            $data = json_decode(
                $request->getContent(),
                true
            );

            if(!$data)
            {
                return $this->json([
                    'error' => 'data product not found'
                ],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $addProduct = $productHandler->addProduct($data['product'] ?? []);

            if(!$addProduct)
            {
                return $this->json([
                    'error' => 'error adding product check your request data or your barcode'
                ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            return $this->json([
                'success' => 'Product added! '
            ]);

        }
        catch (\Exception $exception)
        {
            return $this->json([
                'error' => $exception->getMessage()
            ],500);
        }
    }

    /**
     * @Route("/listProduct", name="_list_product")
     */
    public function listPorduct(
        ProductHandler $productHandler,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $list = $productHandler->getAllProduct();

        $data = $serializer->serialize($list, JsonEncoder::FORMAT);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/addDiscount/{barcode}", name="_add_discount")
     */
    public function addDiscount(
        string $barcode,
        ReceiptHandler $receiptHandler,
        SerializerInterface $serializer
    ): JsonResponse
    {

        try {
           $discount = $receiptHandler->addDiscount($barcode);

            if(!$discount)
            {
                return $this->json([
                    'error' => 'error no discount to add '
                ],
                    Response::HTTP_BAD_REQUEST
                );
            }
            return $this->json([
                'success' => 'discount added check receipt'
            ]);
        }
        catch (\Exception $exception)
        {
            return $this->json([
                'error' => $exception->getMessage()
            ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @Route("/totalturnover", name="_total_turnover")
     */
    public function totalTurnover(
        ReceiptHandler $receiptHandler
    ): JsonResponse
    {
        $total = $receiptHandler->getTotalTurnover();
        return new JsonResponse($total, Response::HTTP_OK, []);
    }

    /**
     * @Route("/removeProduct/{barcode}/{codeReceipt}", name="_remove_product")
     */
    public function removeProduct(
        string $barcode,
        ?string $codeReceipt = null,
        ReceiptHandler $receiptHandler
    ): JsonResponse
    {
        try {
            $remove = $receiptHandler->removeProduct($barcode,$codeReceipt);

            if(!$remove)
            {
                return $this->json([
                    'error' => 'error removing row receipt '
                ],
                    Response::HTTP_BAD_REQUEST
                );
            }
            return $this->json([
                'success' => 'row remove it '
            ]);
        }
        catch (\Exception $exception)
        {
            return $this->json([
                'error' => $exception->getMessage()
            ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
