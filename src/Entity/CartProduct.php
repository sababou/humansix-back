<?php

namespace App\Entity;

use App\Repository\CartProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartProductRepository::class)]
class CartProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'cartProducts')]
    #[ORM\JoinColumn(referencedColumnName: 'sku', nullable: false)]
    private $product;

    #[ORM\Column(type: 'integer')]
    private $quantity;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'cartProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private $cart;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //        ARRAY BUILDER
    //
    ////////////////////////////////////////////////////////////////////////////

    public function buildArray(){
      $arr = array();

      $arr['product'] = $this->getProduct()->buildArray();
      $arr['quantity'] = $this->getQuantity();

      return $arr;
    }
}
