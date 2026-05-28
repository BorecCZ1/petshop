<?php

class CouponRepository{
    private $filePath;

    public function __construct($filePath){
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

    public function findByCode($code)
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }
        foreach ($this->loadRawRows() as $row) {
            $coupon = Coupon::fromArray($row);
            if ($coupon->getCode() == $code) {
                return $coupon;
            }
        }
        return null;
    }
}