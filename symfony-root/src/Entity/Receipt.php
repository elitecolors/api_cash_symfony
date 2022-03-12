<?php

namespace App\Entity;

use App\Repository\ReceiptRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=ReceiptRepository::class)
 * @ORM\Table(name="`receipt`")
 */
class Receipt
{
    const STATUS_RECEIPT = ['IN_PROGRESS','DONE']; // by default receipt state in progress
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, options={"default": "IN_PROGRESS"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity=ProductsReceiptDetail::class, mappedBy="receipt")
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }


    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        if(!$code)
        {
            $uuid = Uuid::v4();
            $code =  $uuid->toBase32();
        }
        $this->code = $code;

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
            $productsReceiptDetail->setReceipt($this);
        }

        return $this;
    }

    public function removeProductsReceiptDetail(ProductsReceiptDetail $productsReceiptDetail): self
    {
        if ($this->productsReceiptDetails->removeElement($productsReceiptDetail)) {
            // set the owning side to null (unless already changed)
            if ($productsReceiptDetail->getReceipt() === $this) {
                $productsReceiptDetail->setReceipt(null);
            }
        }

        return $this;
    }
}
