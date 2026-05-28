<?php

class Category implements JsonSerializable
{
    private $id;
    private $name;
    private $description;

    public static function fromArray(array $data)
    {
        $c = new self();
        $c->id = $data['id'];
        $c->name = $data['name'];
        $c->description = $data['description'];
        return $c;
    }

    public function toArray()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        );
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
}