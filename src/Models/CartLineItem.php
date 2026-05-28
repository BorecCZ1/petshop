<?php

class CartLineItem implements JsonSerializable
{
    private $productId;
    private $name;
    private $price;
    private $qty;
    private $lineTotal;

    public function __construct($productId, $name, $price, $qty, $lineTotal)
    {
        $this->productId = $productId;
        $this->name = $name;
        $this->price = $price;
        $this->qty = $qty;
        $this->lineTotal = $lineTotal;
    }

    public function toArray()
    {
        return array(
            'productId' => $this->productId,
            'name' => $this->name,
            'price' => $this->price,
            'qty' => $this->qty,
            'lineTotal' => $this->lineTotal,
        );
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function getProductId() { return $this->productId; }
    public function getName() { return $this->name; }
    public function getQty() { return $this->qty; }
    public function getPrice() { return $this->price; }
    public function getLineTotal() { return $this->lineTotal; }

}