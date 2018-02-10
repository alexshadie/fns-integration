<?php

namespace alexshadie\FnsIntegration\Data;

class ReceiptItem
{
    /** @var string */
    private $name;
    /** @var float */
    private $sum;
    /** @var float */
    private $nds0;
    /** @var float */
    private $nds10;
    /** @var float */
    private $nds18;
    /** @var float */
    private $price;
    /** @var array */
    private $modifiers = [];
    /** @var float */
    private $quantity;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * @return float
     */
    public function getNds0()
    {
        return $this->nds0;
    }

    /**
     * @return float
     */
    public function getNds10()
    {
        return $this->nds10;
    }

    /**
     * @return float
     */
    public function getNds18()
    {
        return $this->nds18;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return array
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    public static function fromArray(array $data)
    {
        $item = new ReceiptItem();

        $item->name = isset($data['name']) ? $data['name'] : "";
        $item->nds0 = isset($data['nds0']) ? intval($data['nds0']) / 100.0 : 0.0;
        $item->nds10 = isset($data['nds10']) ? intval($data['nds10']) / 100.0 : 0.0;
        $item->nds18 = isset($data['nds18']) ? intval($data['nds18']) / 100.0 : 0.0;
        $item->price = isset($data['price']) ? intval($data['price']) / 100.0 : 0.0;
        $item->quantity = isset($data['quantity']) ? floatval($data['quantity']) : 0.0;
        $item->sum = isset($data['sum']) ? intval($data['sum']) / 100.0 : 0.0;
        $item->modifiers = isset($data['modifiers']) ? $data['modifiers'] : [];

        return $item;
    }
}
