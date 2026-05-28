<?php

class CategoryRepository{
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

    public function getAllCategories()
    {
        $categories = array();
        foreach ($this->loadRawRows() as $row) {
            $categories[] = Category::fromArray($row);
        }
        return $categories;
    }

}