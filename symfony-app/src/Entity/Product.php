<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @ORM\Table(name="`product`")
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $barcode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float", length=255)
     */
    private $cost;

    /**
     * @ORM\Column(type="integer", options={"default" : 6})
     */
    private $vat;

    /**
     * @ORM\OneToMany(targetEntity=ProductsReceiptDetail::class, mappedBy="product")
     */
    private $productsReceiptDetails;

    public function __construct()
    {
        $this->productsReceiptDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(string $barcode): self
    {
        $this->barcode = $barcode;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(float $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getVat(): ?int
    {
        return $this->vat;
    }

    public function setVat(int $vat): self
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * @return Collection<int, ProductsReceiptDetail>
     */
    public function getProductsReceiptDetails(): Collection
    {
        return $this->productsReceiptDetails;
    }

    public function addProductsReceiptDetail(ProductsReceiptDetail $productsReceiptDetail): self
    {
        if (!$this->productsReceiptDetails->contains($productsReceiptDetail)) {
            $this->productsReceiptDetails[] = $productsReceiptDetail;
            $productsReceiptDetail->setProduct($this);
        }

        return $this;
    }

    public function removeProductsReceiptDetail(ProductsReceiptDetail $productsReceiptDetail): self
    {
        if ($this->productsReceiptDetails->removeElement($productsReceiptDetail)) {
            // set the owning side to null (unless already changed)
            if ($productsReceiptDetail->getProduct() === $this) {
                $productsReceiptDetail->setProduct(null);
            }
        }

        return $this;
    }
}
