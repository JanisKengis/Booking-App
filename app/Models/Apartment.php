<?php

namespace App\Models;

class Apartment
{
    private string $name;
    private string $address;
    private string $description;
    private string $availableFrom;
    private string $availableTill;
    private int $price;
    private ?string $id;
    private ?string $userId;

    public function __construct(string $name, string $address, string $description,
                                string $availableFrom, string $availableTill, int $price,?string $id, ?string $userId)
    {
        $this->name = $name;
        $this->address = $address;
        $this->description = $description;
        $this->availableFrom = $availableFrom;
        $this->availableTill = $availableTill;
        $this->price = $price;
        $this->id = $id;
        $this->userId = $userId;

    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getAddress(): string
    {
        return $this->address;
    }


    public function getDescription(): string
    {
        return $this->description;
    }


    public function getAvailableFrom(): string
    {
        return $this->availableFrom;
    }


    public function getAvailableTill(): string
    {
        return $this->availableTill;
    }


    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }


    public function getPrice(): int
    {
        return $this->price;
    }

}
