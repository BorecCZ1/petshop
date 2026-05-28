<?php

class OrderService{

    private $orderRepository;
    private $cartService;
    private $productRepository;
    private $checkoutService;

    public function __construct(OrderRepository $orderRepository, CartService $cartService, ProductRepository $productRepository, CheckoutService $checkoutService){
        $this->orderRepository = $orderRepository;
        $this->cartService = $cartService;
        $this->productRepository = $productRepository;
        $this->checkoutService = $checkoutService;
    }

    public function placeOrder($customerName, $customerEmail, $couponCode = null){
        $customerName = trim($customerName);
        $customerEmail = trim($customerEmail);

        if ($customerName == "" || $customerEmail == "") {
            throw new Exception("Customer name and email can't be empty");
        }

        if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        $cart = $this->cartService->getCartView();

        if ($cart->isEmpty()) {
            throw new Exception("No cart items found");
        }

        foreach ($cart->getItems() as $item) {
            $product = $this->productRepository->findProductById($item->getProductId());
            if (!$product) {
                throw new Exception("Product not found");
            }
            if ($item->getQty() > $product->getStock()) {
                throw new Exception("Insufficient stock");
            }
        }

        $totals = $this->checkoutService->calculateTotal(
            $cart->getSubtotal(),
            $couponCode
        );

        $customer = new Customer($customerName, $customerEmail);
        $order = new Order(
            $this->orderRepository->generateOrderNumber(),
            date('c'),
            $customer,
            $cart->getItems(),
            $totals['subtotal'],
            $totals['discount'],
            $totals['total'],
            $totals['coupon_code']
        );

        $this->orderRepository->saveOrder($order);
        $this->cartService->clearCart();

        return array(
            'order_number' => $order->getOrderNumber(),
            'subtotal' => $order->getSubtotal(),
            'discount' => $order->getDiscount(),
            'total' => $order->getTotal(),
            'coupon_code' => $order->getCouponCode(),
        );
    }

    public function getCartPreview($couponCode = null)
    {
        $cart = $this->cartService->getCartView();
        if ($cart->isEmpty()) {
            throw new Exception('No cart items found');
        }
        $totals = $this->checkoutService->calculateTotal(
            $cart->getSubtotal(),
            $couponCode
        );
        return array(
            'cart' => $cart,
            'subtotal' => $totals['subtotal'],
            'discount' => $totals['discount'],
            'total' => $totals['total'],
            'coupon_code' => $totals['coupon_code'],
        );
    }

    public function getAllOrders()
    {
        return $this->orderRepository->getAllOrders();
    }

    public function getOrderDetail($orderNumber){
        $order = $this->orderRepository->findByOrderNumber($orderNumber);
        if (!$order) {
            throw new Exception("Order not found");
        }
        return $order;
    }

}