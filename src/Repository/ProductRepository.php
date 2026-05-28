<?php

class ProductRepository {
    private $filePath;

    public function __construct($filePath) {
        $this->filePath = $filePath;
    }

    private function loadRawRows()
    {
        if (!file_exists($this->filePath)) {
            return array();
        }
        $data = file_get_contents($this->filePath);
        $rows = json_decode($data, true);
        return is_array($rows) ? $rows : array();
    }

    public function getAllProducts()
    {
        $products = array();
        foreach ($this->loadRawRows() as $row) {
            $products[] = Product::fromArray($row);
        }
        return $products;
    }

    public function findProductById($productId) {
        foreach ($this->getAllProducts() as $product) {
            if ($product->getId() == $productId) {
                return $product;
            }
        }
        return null;
    }

    public function findProductByCategoryId($categoryId) {
        $filtered = array();
        foreach ($this->getAllProducts() as $product) {
            if ($product->getCategoryId() == $categoryId) {
                $filtered[] = $product;
            }
        }
        return $filtered;
    }

}
