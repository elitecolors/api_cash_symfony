<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class CreateProductRequestPayload
{

    /**
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private string $name;

    /**
     * @Assert\NotNull
     * @Assert\NotBlank()
     */
    private string $barcode;

    /**
     * @Assert\NotNull
     * @Assert\NotBlank()
     */
    private float $cost;

    /**
     * @Assert\NotNull
     * @Assert\NotBlank()
     */
    private int $vat;
}