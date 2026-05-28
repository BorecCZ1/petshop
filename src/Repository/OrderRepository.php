<?php
class OrderRepository{

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

    public function getAllOrders(){
        $orders = array();
        foreach ($this->loadRawRows() as $row) {
            $orders[] = Order::fromArray($row);
        }
        return $orders;
    }

    public function findByOrderNumber($orderNumber){
        foreach ($this->getAllOrders() as $order) {
            if ($order->getOrderNumber() == $orderNumber) {
                return $order;
            }
        }
        return null;
    }

    public function saveOrder(Order $order){
        $rows = $this->loadRawRows();
        $rows[] = $order->toArray();
        file_put_contents(
            $this->filePath,
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        return $order;
    }

    public function generateOrderNumber(){
        return 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
    }

}



