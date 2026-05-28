<?php

class Cart implements JsonSerializable
{
    private $items = array();
    private $subtotal = 0;

    public function addItem(CartLineItem $item)
    {
        $this->items[] = $item;
        $this->subtotal += $item->getLineTotal();
    }

    public function getItems() { return $this->items; }
    public function getSubtotal() { return $this->subtotal; }
    public function isEmpty() { return count($this->items) === 0; }

    public function toArray()
    {
        $items = array();
        foreach ($this->items as $item) {
            $items[] = $item->toArray();
        }
        return array('items' => $items, 'subtotal' => $this->subtotal);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}