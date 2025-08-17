<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ProductName = null;

    #[ORM\Column(type: 'integer')]
    private ?int $QuantityPerUnit = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private ?string $unitPrice = null;

    #[ORM\Column(type: 'integer')]
    private ?int $UnitsOnStock = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Suppliers $suppliers = null;

    #[ORM\Column(length: 255)]
    private ?string $Description = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categories $categories = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductName(): ?string
    {
        return $this->ProductName;
    }

    public function setProductName(string $ProductName): static
    {
        $this->ProductName = $ProductName;
        return $this;
    }

    public function getQuantityPerUnit(): ?int
    {
        return $this->QuantityPerUnit;
    }

    public function setQuantityPerUnit(int $QuantityPerUnit): static
    {
        $this->QuantityPerUnit = $QuantityPerUnit;
        return $this;
    }


    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }

    public function getUnitsOnStock(): ?int
    {
        return $this->UnitsOnStock;
    }

    public function setUnitsOnStock(int $UnitsOnStock): static
    {
        $this->UnitsOnStock = $UnitsOnStock;
        return $this;
    }

    public function getSuppliers(): ?Suppliers
    {
        return $this->suppliers;
    }

    public function setSuppliers(?Suppliers $suppliers): static
    {
        $this->suppliers = $suppliers;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;
        return $this;
    }

    public function getCategories(): ?Categories
    {
        return $this->categories;
    }

    public function setCategories(?Categories $categories): static
    {
        $this->categories = $categories;
        return $this;
    }

public function getImageUrl(): ?string
{
    return $this->imageUrl;
}

public function setImageUrl(?string $imageUrl): self
{
    $this->imageUrl = $imageUrl;
    return $this;
}

}
