<?php

class Coupon implements JsonSerializable{

    private $code;
    private $type;
    private $value;

    public static function fromArray(array $data){
        $coupon = new self();
        $coupon->code = $data['code'];
        $coupon->type = $data['type'];
        $coupon->value = (int) $data['value'];
        return $coupon;
    }

    public function toArray(){
        return array(
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value
        );
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function getCode() { return $this->code; }
    public function getType() { return $this->type; }
    public function getValue() { return $this->value; }

}