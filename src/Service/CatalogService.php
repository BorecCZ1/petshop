<?php

class CatalogService{

    private $productRepository;
    private $categoryRepository;

    public function __construct(ProductRepository $productRepository, CategoryRepository $categoryRepository) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function getCategories() {
        return $this->categoryRepository->getAllCategories();
    }

    public function getProducts($categoryId = null) {
        if ($categoryId) {
            return $this->productRepository->findProductByCategoryId($categoryId);
        }else {
            return $this->productRepository->getAllProducts();
        }
    }

    public function getProductDetail($productId) {
        $product = $this->productRepository->findProductById($productId);

        if(!$product) {
            throw new Exception('Product not found');
        }
        return $product;
    }

}