<?php

class CheckoutService{
    private $couponRepository;

    public function __construct(CouponRepository $couponRepository){
        $this->couponRepository = $couponRepository;
    }

    public function calculateTotal($subtotal, $couponCode = null){
        $subtotal = (int) $subtotal;
        if ($subtotal < 0) {
            throw new Exception('Subtotal cannot be negative');
        }
        $discount = 0;
        $appliedCode = null;

        $couponCode = trim($couponCode);
        if($couponCode !== ""){
            $coupon = $this->couponRepository->findByCode($couponCode);

            if(!$coupon){
                throw new Exception("Invalid coupon code");
            }

            $appliedCode = $coupon->getCode();

            if ($coupon->getType() == "fixed"){
                $discount = min($coupon->getValue(), $subtotal);
            } elseif ($coupon->getType() == "percent"){
                $discount = (int) round($subtotal * ($coupon->getValue() / 100));
            } else {
                throw new Exception("Invalid coupon code");
            }
        }

        $total = max(0, $subtotal - $discount);

        return array(
            "subtotal" => $subtotal,
            "discount" => $discount,
            "total" => $total,
            "coupon_code" => $appliedCode,
        );
    }

}