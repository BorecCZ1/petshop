<?php

class Customer implements JsonSerializable
{
    private $name;
    private $email;

    public function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    public static function fromArray(array $data)
    {
        return new self($data['name'], $data['email']);
    }

    public function toArray()
    {
        return array('name' => $this->name, 'email' => $this->email);
    }

    public function jsonSerialize() { return $this->toArray(); }

    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
}