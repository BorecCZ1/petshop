<?php

class Product implements JsonSerializable {

    private $id;
    private $name;
    private $categoryId;
    private $price;
    private $description;
    private $stock;

    public static function fromArray(array $data){
        $product = new self();
        $product->id = $data['id'];
        $product->name = $data['name'];
        $product->categoryId = $data['category_id'];
        $product->price = (int) $data['price'];
        $product->description = $data['description'];
        $product->stock = (int) $data['stock'];
        return $product;
    }

    public function toArray() {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'price' => $this->price,
            'description' => $this->description,
            'stock' => $this->stock
        );
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getCategoryId() { return $this->categoryId; }
    public function getPrice() { return $this->price; }
    public function getDescription() { return $this->description; }
    public function getStock() { return $this->stock; }

}