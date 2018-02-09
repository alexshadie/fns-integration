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

    public static function fromArray(array $data) {
        $item = new ReceiptItem();

        $item->name= isset($data['name']) ? $data['name'] : "";
        $item->nds0 = isset($data['nds0']) ? intval($data['nds0']) / 100.0 : 0.0;
        $item->nds10 = isset($data['nds10']) ? intval($data['nds10']) / 100.0 : 0.0;
        $item->nds18 = isset($data['nds18']) ? intval($data['nds18']) / 100.0 : 0.0;
        $item->price = isset($data['price']) ? intval($data['price']) / 100.0 : 0.0;
        $item->quantity = isset($data['quantity']) ? floatval($data['quantity']) : 0.0;
        $item->sum = isset($data['sum']) ? intval($data['sum']) / 100.0 : 0.0;
        $item->modifiers  = isset($data['modifiers']) ? $data['modifiers'] : [];

        return $item;
    }
}
