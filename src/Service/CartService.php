<?php

class CartService{

    const MAX_QUANTITY = 999;

    private $productRepository;

    public function __construct(ProductRepository $productRepository){
        $this->productRepository = $productRepository;
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }
    }

    private function assertProductId($productId)
    {
        if (trim($productId) === '') {
            throw new Exception('Product id is required');
        }
    }

    private function assertQtyInRange($qty)
    {
        $qty = (int) $qty;
        if ($qty < 1) {
            throw new Exception('Quantity must be at least 1');
        }
        if ($qty > self::MAX_QUANTITY) {
            throw new Exception('Quantity cannot exceed 999');
        }
        return $qty;
    }

    private function getMaxAllowedForProduct($product)
    {
        return min($product->getStock(), self::MAX_QUANTITY);
    }

    private function assertQtyWithinLimits($qty, $product)
    {
        if ($qty > $this->getMaxAllowedForProduct($product)) {
            throw new Exception('Product out of stock');
        }
    }

    public function addToCart($productId, $qty = 1){
        $this->assertProductId($productId);
        $qty = $this->assertQtyInRange($qty);

        $product = $this->productRepository->findProductById($productId);

        if (!$product) {
            throw new Exception('Product not found');
        }

        if ($product->getStock() <= 0) {
            throw new Exception('Product out of stock');
        }

        $currentCartQuantity = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] : 0;
        $newQty = $currentCartQuantity + $qty;

        if ($newQty > self::MAX_QUANTITY) {
            throw new Exception('Quantity cannot exceed 999');
        }

        $this->assertQtyWithinLimits($newQty, $product);

        $_SESSION['cart'][$productId] = $newQty;

        return $this->getCartView();
    }

    public function removeFromCart($productId){
        $this->assertProductId($productId);
        unset($_SESSION['cart'][$productId]);
        return $this->getCartView();
    }

    public function setQuantity($productId, $qty)
    {
        $this->assertProductId($productId);
        $qty = $this->assertQtyInRange($qty);

        $product = $this->productRepository->findProductById($productId);

        if (!$product) {
            throw new Exception('Product not found');
        }

        $this->assertQtyWithinLimits($qty, $product);

        $_SESSION['cart'][$productId] = $qty;

        return $this->getCartView();
    }

    public function getCartView(){
        $cart = new Cart();
        foreach ($_SESSION['cart'] as $productId => $qty) {

            $prod = $this->productRepository->findProductById($productId);

            if (!$prod) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }

            $qty = (int) $qty;
            if ($qty < 1) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }

            $maxAllowed = $this->getMaxAllowedForProduct($prod);
            if ($qty > $maxAllowed) {
                $qty = $maxAllowed;
                $_SESSION['cart'][$productId] = $qty;
            }

            $lineTotal = $prod->getPrice() * $qty;
            $cart->addItem(new CartLineItem(
                $prod->getId(),
                $prod->getName(),
                $prod->getPrice(),
                $qty,
                $lineTotal
            ));
        }

        return $cart;
    }

    public function clearCart(){
        $_SESSION['cart'] = array();
        return $this->getCartView();
    }

}