<?php

namespace GMO\Payment;

use GMO\Payment\Api;

class PostPayment extends Api
{
    private const METHOD_ENTRY_TRAN_POSTPAY = 'EntryTranPostpay';
    private const METHOD_EXEC_TRAN_POSTPAY = 'ExecTranPostpay';


    private string $siteID = "";
    private string $sitePass = "";

    private string $shopID = "";
    private string $shopPass = "";


    public function __construct(string $host, array $credentials, bool $forceOldApi = false)
    {
        parent::__construct($host, $this->apiType = $forceOldApi? self::API_IDPASS: self::API_JSON);

        if (array_key_exists('siteID', $credentials)) {
            $this->siteID = $credentials['siteID'];
        }
        if (array_key_exists('sitePass', $credentials)) {
            $this->sitePass = $credentials['sitePass'];
        }
        if (array_key_exists('shopID', $credentials)) {
            $this->shopID = $credentials['shopID'];
        }
        if (array_key_exists('shopPass', $credentials)) {
            $this->shopPass = $credentials['shopPass'];
        }
    }

    private function setShopCredentials()
    {
        $this->setParam('siteID', $this->siteID);
        $this->setParam('sitePass', $this->sitePass);
    }


    public function entryTranPostpay(string $orderID, int $amount, int $tax = null)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('amount', $amount);
        if (!is_null($tax)) $this->setParam('tax', $jobCd);

        return $this->request(self::METHOD_ENTRY_TRAN_POSTPAY);
    }

    public function execTranPostpay(
        string $orderID,
        array $accessInfo,
        array $customerInfo,
        array $deliveryInfo,
        array $details,
        array $httpHeaderInfo = [],
        array $clientFields = []
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessInfo['id']);
        $this->setParam('accessPass', $accessInfo['pass']);
        $this->setParam('orderID', $orderID);

        // TODO: set header info

        // TODO: set delivery info

        //details


        // TODO: set client fields

        return $this->request(self::METHOD_EXEC_TRAN_POSTPAY);
    }
}
