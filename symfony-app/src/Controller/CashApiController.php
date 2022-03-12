<?php

namespace App\Controller;

use App\Helper\ProductHandler;
use App\Helper\ReceiptHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/cash", name="api_cash_register")
 */
class CashApiController extends AbstractController
{
    /**
     * @Route("/getProduct/{barcode}", name="_get_product", methods={"GET","HEAD"})
     */
    public function get(
        string $barcode,
        ProductHandler $productHandler,
        SerializerInterface $serializer
    ): Response
    {
        $product = $productHandler->getProduct($barcode);

        $data = $serializer->serialize($product, JsonEncoder::FORMAT,[AbstractNormalizer::IGNORED_ATTRIBUTES => ['receipt']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/createReceipt", name="_create_receipt", methods={"GET","HEAD"})
     */
    public function createReceipt(
        ReceiptHandler $receiptHandler
    ): Response
    {
        try {
            $receiptHandler->createReceipt();
            return $this->json([
                'success' => 'receipt created! '
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
     * @Route("/addProductRceipt/{receipt_code}/{barcode}", name="_add_product_receipt", methods={"GET","HEAD"})
     */
    public function addProductToReceipt(
        string $receipt_code,
        string $barcode,
        ReceiptHandler $receiptHandler
    ): Response
    {
        try {
            $addProduct = $receiptHandler->addProductReceipt($receipt_code,$barcode);
            if(!$addProduct)
            {
                return $this->json([
                    'error' => 'error adding product check code receipt or barcode '
                ],
                    Response::HTTP_BAD_REQUEST
                );
            }
            return $this->json([
                'success' => 'product added to receipt! '
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
     * @Route("/changeAmount/{new_amount}", name="_create_receipt", methods={"GET","HEAD"})
     */
    public function changeAmount(
        int $new_amount,
        ReceiptHandler $receiptHandler
    ): Response
    {
        try {
            $update = $receiptHandler->updateProduct($new_amount);

            if(!$update)
            {
                return $this->json([
                    'error' => 'error updating quantity '
                ],
                    Response::HTTP_BAD_REQUEST
                );
            }
            return $this->json([
                'success' => 'quantity updated '
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
     * @Route("/finishReceipt", name="_finish_receipt", methods={"GET","HEAD"})
     */
    public function finishReceipt(
        ReceiptHandler $receiptHandler
    ): Response
    {
        try {
            $update = $receiptHandler->finishReceipt();

            if(!$update)
            {
                return $this->json([
                    'error' => 'error no receipt found '
                ],
                    Response::HTTP_BAD_REQUEST
                );
            }
            return $this->json([
                'success' => 'receipt status finished '
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
     * @Route("/getReceipt/{code}", name="_get_receipt", methods={"GET","HEAD"})
     */
    public function getReceipt(
        ?string $code = null,
        Request $request,
        ReceiptHandler $receiptHandler,
        SerializerInterface $serializer
    ): Response
    {
        $receiptCode = $request->get('code');
        $dataReceipt = $receiptHandler->getDataReceipt($receiptCode);

        $data = $serializer->serialize($dataReceipt, JsonEncoder::FORMAT);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
