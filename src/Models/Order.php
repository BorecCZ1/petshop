<?php

class Order implements JsonSerializable
{
    private $orderNumber;
    private $createdAt;
    private $customer;
    private $items;
    private $subtotal;
    private $discount;
    private $total;
    private $couponCode;

    public function __construct(
        $orderNumber,
        $createdAt,
        Customer $customer,
        array $items,
        $subtotal,
        $discount,
        $total,
        $couponCode
    ) {
        $this->orderNumber = $orderNumber;
        $this->createdAt = $createdAt;
        $this->customer = $customer;
        $this->items = $items;
        $this->subtotal = (int) $subtotal;
        $this->discount = (int) $discount;
        $this->total = (int) $total;
        $this->couponCode = $couponCode;
    }

    public static function fromArray(array $data)
    {
        $items = array();

        foreach ($data['items'] as $row) {
            $items[] = new CartLineItem(
                $row['productId'],
                $row['name'],
                $row['price'],
                $row['qty'],
                $row['lineTotal']
            );
        }

        return new self(
            $data['order_number'],
            $data['created_at'],
            Customer::fromArray($data['customer']),
            $items,
            $data['subtotal'],
            $data['discount'],
            $data['total'],
            $data['coupon_code']
        );
    }

    public function toArray()
    {
        $items = array();
        foreach ($this->items as $item) {
            $items[] = $item->toArray();
        }
        return array(
            'order_number' => $this->orderNumber,
            'created_at' => $this->createdAt,
            'customer' => $this->customer->toArray(),
            'items' => $items,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'total' => $this->total,
            'coupon_code' => $this->couponCode,
        );
    }

    public function jsonSerialize() { return $this->toArray(); }

    public function getOrderNumber() { return $this->orderNumber; }
    public function getTotal() { return $this->total; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getCustomer() { return $this->customer; }
    public function getItems() { return $this->items; }
    public function getSubtotal() { return $this->subtotal; }
    public function getDiscount() { return $this->discount; }
    public function getCouponCode() { return $this->couponCode; }


}