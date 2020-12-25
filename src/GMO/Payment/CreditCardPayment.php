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

    private function createApiObject(bool $withShop = true, bool $withSite = false)
    {
        $api = new Api($this->host);

        if ($withShop) {
            $api->setParam('shopID', $this->shopID);
            $api->setParam('shopPass', $this->shopPass);
        }

        if ($withSite) {
            $api->setParam('siteID', $this->siteID);
            $api->setParam('sitePass', $this->sitePass);
        }

        return $api;
    }

    private function setOptionalParams(array $optional)
    {
        //TODO: Implement this function
    }

    public function entryTran(string $orderID, string $jobCd = Api::JOBCD_CAPTURE, int $amount = 0, array $optional)
    {
        $api = $this->createApiObject();

        $api->setParam('orderID', $orderID);
        $api->setParam('jobCd', $jobCd);
        if ($jobCd != Api::JOBCD_CHECK) {
            $api->setParam('amount', $amount);
        }

        $this->setOptionalParams($optional);

        return $api->request(Api::API_ENTRY_TRAN);
    }

    public function execTran(string $accessID, string $accessPass, string $token, int $method = 1, array $optional)
    {
        $api = $this->createApiObject();

        $api->setParam('accessID', $accessID);
        $api->setParam('accessPass', $accessPass);
        $api->setParam('token', $token);
        $api->setParam('method', $method)

        $this->setOptionalParams($optional)

        return $api->request(Api::API_EXEC_TRAN);
    }
}
