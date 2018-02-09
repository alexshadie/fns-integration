<?php

namespace alexshadie\FnsIntegration\Data;

class Receipt
{
    /** @var int */
    private $dateTime;
    /** @var string (Numeric) */
    private $kktRegId;
    /** @var int */
    private $requestNumber;
    /** @var int */
    private $fiscalDocumentNumber;
    /** @var array  */
    private $modifiers = [];
    /** @var array  */
    private $stornoItems = [];
    /** @var int */
    private $shiftNumber;
    /** @var int */
    private $receiptCode;
    /** @var string */
    private $user;
    /** @var string */
    private $operator;
    /** @var string */
    private $rawDatacashTotalSum;
    /** @var float */
    private $taxationType;
    /** @var ReceiptItem[] */
    private $items;
    /** @var float */
    private $ecashTotalSum;
    /** @var int */
    private $operationType;
    /** @var float */
    private $nds0;
    /** @var float */
    private $nds10;
    /** @var float */
    private $nds18;
    /** @var float */
    private $totalSum;
    /** @var string */
    private $fiscalDriveNumber;
    /** @var int */
    private $fiscalSign;
    /** @var string */
    private $userInn;

    /** @var float */
    private $discount;
    /** @var float */
    private $discountSum;

    /**
     * @return int
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @return string
     */
    public function getKktRegId()
    {
        return $this->kktRegId;
    }

    /**
     * @return int
     */
    public function getRequestNumber()
    {
        return $this->requestNumber;
    }

    /**
     * @return int
     */
    public function getFiscalDocumentNumber()
    {
        return $this->fiscalDocumentNumber;
    }

    /**
     * @return array
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * @return array
     */
    public function getStornoItems()
    {
        return $this->stornoItems;
    }

    /**
     * @return int
     */
    public function getShiftNumber()
    {
        return $this->shiftNumber;
    }

    /**
     * @return int
     */
    public function getReceiptCode()
    {
        return $this->receiptCode;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return string
     */
    public function getRawDatacashTotalSum()
    {
        return $this->rawDatacashTotalSum;
    }

    /**
     * @return float
     */
    public function getTaxationType()
    {
        return $this->taxationType;
    }

    /**
     * @return ReceiptItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return float
     */
    public function getEcashTotalSum()
    {
        return $this->ecashTotalSum;
    }

    /**
     * @return int
     */
    public function getOperationType()
    {
        return $this->operationType;
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
    public function getTotalSum()
    {
        return $this->totalSum;
    }

    /**
     * @return string
     */
    public function getFiscalDriveNumber()
    {
        return $this->fiscalDriveNumber;
    }

    /**
     * @return int
     */
    public function getFiscalSign()
    {
        return $this->fiscalSign;
    }

    /**
     * @return string
     */
    public function getUserInn()
    {
        return $this->userInn;
    }

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @return float
     */
    public function getDiscountSum()
    {
        return $this->discountSum;
    }

    /**
     * Generates receipt from JSON string
     * @param $str
     * @return Receipt
     */
    public static function fromJsonStr($str) {
        $data = json_decode($str, 1);
        $data = isset($data['document']) ? $data['document']['receipt'] : $data;
        return self::fromArray($data);
    }

    public static function fromArray(array $data) {
        $receipt = new Receipt();

        $receipt->dateTime = isset($data['dateTime']) ? strtotime($data['dateTime']) : 0;
        $receipt->kktRegId = isset($data['kktRegId']) ? $data['kktRegId'] : "";
        $receipt->requestNumber = isset($data['requestNumber']) ? intval($data['requestNumber']) : 0;
        $receipt->fiscalDocumentNumber = isset($data['fiscalDocumentNumber']) ? intval($data['fiscalDocumentNumber']) : 0;
        $receipt->modifiers  = isset($data['modifiers']) ? $data['modifiers'] : [];
        $receipt->stornoItems  = isset($data['stornoItems ']) ? $data['stornoItems '] : [];
        $receipt->shiftNumber = isset($data['shiftNumber']) ? intval($data['shiftNumber']) : 0;
        $receipt->receiptCode = isset($data['receiptCode']) ? intval($data['receiptCode']) : 0;
        $receipt->user = isset($data['user']) ? $data['user'] : "";
        $receipt->operator = isset($data['operator']) ? $data['operator'] : "";
        $receipt->rawDatacashTotalSum = isset($data['rawDatacashTotalSum']) ? intval($data['rawDatacashTotalSum']) / 100.0 : 0.0;
        $receipt->taxationType = isset($data['taxationType']) ? intval($data['taxationType']) : 0;

        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $receipt->items[] = ReceiptItem::fromArray($item);
            }
        } else {
            $receipt->items = [];
        }

        $receipt->ecashTotalSum = isset($data['ecashTotalSum']) ? intval($data['ecashTotalSum']) / 100 : 0.0;
        $receipt->operationType = isset($data['operationType']) ? intval($data['operationType']) : 0;
        $receipt->nds0 = isset($data['nds0']) ? intval($data['nds0']) / 100.0 : 0.0;
        $receipt->nds10 = isset($data['nds10']) ? intval($data['nds10']) / 100.0 : 0.0;
        $receipt->nds18 = isset($data['nds18']) ? intval($data['nds18']) / 100.0 : 0.0;
        $receipt->totalSum = isset($data['totalSum']) ? intval($data['totalSum']) / 100.0 : 0.0;
        $receipt->fiscalDriveNumber = isset($data['fiscalDriveNumber']) ? $data['fiscalDriveNumber'] : "";
        $receipt->fiscalSign = isset($data['fiscalSign']) ? intval($data['fiscalSign']) : null;
        $receipt->userInn = isset($data['userInn']) ? intval($data['userInn']) : null;
        $receipt->discount = isset($data['discount']) ? floatval($data['discount']) : 0.0;
        $receipt->discountSum = isset($data['discountSum']) ? floatval($data['discountSum']) : 0.0;

        return $receipt;
    }
}