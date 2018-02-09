<?php

namespace alexshadie\FnsIntegration;

use alexshadie\FnsIntegration\Data\Receipt;
use Psr\Log\LoggerInterface;

class FnsIntegration
{
    const VALIDATE_RECEIPT_URL = 'https://proverkacheka.nalog.ru:9999/v1/ofds/*/inns/*/fss/#FN#/operations/#OP#/tickets/#FD#?fiscalSign=#FISCAL_SIGN#&date=#DATE#&sum=#SUM#';
    const RETRIEVE_RECEIPT_URL = 'https://proverkacheka.nalog.ru:9999/v1/inns/*/kkts/*/fss/#FN#/tickets/#FD#?fiscalSign=#FISCAL_SIGN#&sendToEmail=no';

    /** @var string */
    private $username;
    /** @var string */
    private $password;
    /** @var string */
    private $deviceId;
    /** @var LoggerInterface */
    private $logger;

    /**
     * FnsIntegration constructor.
     *
     * @param $username string FNS username
     * @param $password string FNS password
     * @param $deviceId string Randomly generated device id, used to prevent server-side ban, if any flood-checks are performed
     * @param LoggerInterface|null $logger
     */
    public function __construct($username, $password, $deviceId = '351256985671943', LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->username = $username;
        $this->password = $password;
        $this->deviceId = $deviceId;
    }

    /**
     * Sets logger
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Gets URL for receipt validation
     *
     * @param $date string Date of receipt, format Y-m-dTH:i:s, s is usually 00. "t" in QR-code
     * @param $sum string Receipt sum. "s" in QR-code
     * @param $fiscalNumber string Usually shown as "ФН". "fn" in QR-code
     * @param $receiptId string Usually shown as "ФД№". "i" in QR-code
     * @param $fiscalSign string Usually shown as "ФПД". "fp" in QR-code
     * @param $operationType string Operation type. "n" in QR-code
     * @return string
     */
    public function getValidateReceiptUrl($date, $sum, $fiscalNumber, $receiptId, $fiscalSign, $operationType)
    {
        $replace = [
            '#FN#' => $fiscalNumber,
            '#FD#' => $receiptId,
            '#OP#' => $operationType,
            '#DATE#' => $date,
            '#SUM#' => sprintf('%.0f', floatval($sum) * 100),
            '#FISCAL_SIGN#' => $fiscalSign,
        ];
        return str_replace(array_keys($replace), array_values($replace), self::VALIDATE_RECEIPT_URL);
    }

    /**
     * Gets URL for receipt retrieving
     *
     * @param $fiscalNumber string Usually shown as "ФН". "fn" in QR-code
     * @param $receiptId string Usually shown as "ФД№". "i" in QR-code
     * @param $fiscalSign string Usually shown as "ФПД". "fp" in QR-code
     * @return string
     */
    public function getRetrieveReceiptUrl($fiscalNumber, $receiptId, $fiscalSign)
    {
        $replace = [
            '#FN#' => $fiscalNumber,
            '#FD#' => $receiptId,
            '#FISCAL_SIGN#' => $fiscalSign,
        ];
        return str_replace(array_keys($replace), array_values($replace), self::RETRIEVE_RECEIPT_URL);
    }

    /**
     * Sends query for receipt getting.
     * Due to FNS specific workflow this method must be called at least twice.
     * Firstly it schedules receipt search, an the subsequent calls it return receipt data or HTTP 406 on error.
     *
     * @todo Implement better design for return values. Mb errors should be exceptions
     *
     * @param $fiscalNumber string Usually shown as "ФН". "fn" in QR-code
     * @param $receiptId string Usually shown as "ФД№". "i" in QR-code
     * @param $fiscalSign string Usually shown as "ФПД". "fp" in QR-code
     * @return Receipt|bool
     */
    public function queryReceipt($fiscalNumber, $receiptId, $fiscalSign)
    {
        $this->logger->debug("Sending fetch receipt query");
        list($body, $responseCode, $headers) = $this->query(
            $this->getRetrieveReceiptUrl($fiscalNumber, $receiptId, $fiscalSign),
            true
        );
        switch ($responseCode) {
            case 200:
                // Receipt retrieved
                $this->logger->info("Got receipt");
                return Receipt::fromJsonStr($body);
            case 202:
                // Receipt retrieving queued
                $this->logger->info("Queued receipt");
                return true;

            case 403:
                // Invalid user credentials
                $this->logger->error("Invalid user credentials");
                return false;

            case 406:
                // Invalid receipt
                $this->logger->error("Invalid receipt credentials");
                return false;

            default:
                // Something unusual happened, needs investigation
                $this->logger->error(
                    "Unexpected response code {$responseCode}. "
                        . "Body is: " . var_export($body, 1) . ";"
                        . "Headers are: " . var_export($headers)
                );
                return false;
        }
    }

    /**
     * Validates receipt on FNS
     *
     * @param $date string Date of receipt, format Y-m-dTH:i:s, s is usually 00. "t" in QR-code
     * @param $sum string Receipt sum. "s" in QR-code
     * @param $fiscalNumber string Usually shown as "ФН". "fn" in QR-code
     * @param $receiptId string Usually shown as "ФД№". "i" in QR-code
     * @param $fiscalSign string Usually shown as "ФПД". "fp" in QR-code
     * @param $operationType string Operation type. "n" in QR-code
     * @return bool
     */
    public function validateReceipt($date, $sum, $fiscalNumber, $receiptId, $fiscalSign, $operationType)
    {
        list($body, $responseCode, $headers) = $this->query(
            $this->getValidateReceiptUrl($date, $sum, $fiscalNumber, $receiptId, $fiscalSign, $operationType)
        );

        if ($responseCode == 204) {
            return true;
        }
        if ($responseCode == 406) {
            return false;
        }

        $this->logger->error(
            "Unexpected response code {$responseCode}. "
                . "Body is: " . var_export($body, 1) . ";"
                . "Headers are: " . var_export($headers)
        );
        return false;
    }

    /**
     * Performs query to FNS servers
     *
     * @param $url
     * @param bool $need_auth
     * @return array
     */
    protected function query($url, $need_auth = false)
    {
        $headers = [
            "Device-Id: {$this->deviceId}",
            "Device-OS: Android 7.1",
            "Version: 2",
            "ClientVersion: 1.3.7.1",
            "Host: proverkacheka.nalog.ru:8888",
            "Connection: Keep-Alive",
            "Accept-Encoding: gzip",
            "User-Agent: okhttp/3.0.1",
        ];
        if ($need_auth) {
            $headers = array_merge(
                ["Authorization: Basic " . base64_encode($this->username . ":" . $this->password)],
                $headers
            );
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $body = curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        return [substr($body, $headerSize), $response, substr($body, 0, $headerSize)];
    }
}
