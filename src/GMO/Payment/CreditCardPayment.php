<?php

namespace GMO\Payment;

use GMO\Payment\Api;

class CreditCardPayment
{
    private string $host;

    private string $siteID = "";
    private string $sitePass = "";

    private string $shopID = "";
    private string $shopPass = "";

    private array $apiParams = [];

    public function __construct(string $host, array $credentials)
    {
        $this->host = $host;

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

    private function setSite(Api $apiObj)
    {
        $apiObj->setParam('siteID', $this->siteID);
        $apiObj->setParam('sitePass', $this->sitePass);
    }

    private function setShop(Api $apiObj)
    {
        $apiObj->setParam('shopID', $this->shopID);
        $apiObj->setParam('shopPass', $this->shopPass);
    }

    private function setOptionalParams(array $optional)
    {
        //TODO: Implement this function
    }

    public function entryTran(string $orderID, string $jobCd = Api::JOBCD_CAPTURE, int $amount = 0, array $optional)
    {
        $api = new Api($this->host);

        $this->setShop($api);
        $api->setParam('orderID', $orderID);
        $api->setParam('jobCd', $jobCd);
        if ($jobCd != Api::JOBCD_CHECK) {
            $api->setParam('amount', $amount);
        }

        $this->setOptionalParams($optional);

        return $api->request(Api::API_ENTRY_TRAN);
    }
}
