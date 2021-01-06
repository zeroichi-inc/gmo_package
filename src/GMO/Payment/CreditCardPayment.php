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

    public function entryTran(string $orderID, int $amount = 0, string $jobCd = Api::JOBCD_CAPTURE, array $optional = [])
    {
        $api = $this->createApiObject();

        $api->setParam('orderID', $orderID);
        $api->setParam('jobCd', $jobCd);
        if ($jobCd != Api::JOBCD_CHECK) {
            $api->setParam('amount', $amount);
        }

        $api->setParamArray($optional);

        return $api->request(Api::API_ENTRY_TRAN);
    }

    public function execTran(string $accessID, string $accessPass, string $orderID, string $token, int $method = 1, array $optional = [])
    {
        $api = $this->createApiObject();

        $api->setParam('accessID', $accessID);
        $api->setParam('accessPass', $accessPass);
        $api->setParam('orderID', $orderID);
        $api->setParam('token', $token);
        $api->setParam('method', $method);

        $api->setParamArray($optional);

        return $api->request(Api::API_EXEC_TRAN);
    }

    public function alterTran(string $accessID, string $accessPass, string $jobCd, int $amount = 0, int $method = 1)
    {
        $api = $this->createApiObject();

        $api->setParam('accessID', $accessID);
        $api->setParam('accessPass', $accessPass);
        $api->setParam('jobCd', $jobCd);

        if (!in_array($jobCd, array(Api::JOBCD_VOID, Api::JOBCD_RETURN, Api::JOBCD_RETURNX, Api::JOBCD_CANCEL))) {
            $api->setParam('amount', $amount);
            $api->setParam('method', $method);
        }

        return $api->request(Api::API_ALTER_TRAN);
    }

    public function searchTrade(string $orderID)
    {
        $api = $this->createApiObject();

        $api->setParam('orderID', $orderID);

        return $api->request(Api::API_SEARCH_TRADE);
    }

    public function saveMember(string $memberID, string $memberName = null)
    {
        $api = $this->createApiObject(false, true);

        $api->setParam('memberID', $memberID);
        if ($memberName) {
            $api->setParam('memberName', $memberName);
        }

        return $api->request(Api::API_SAVE_MEMBER);
    }

    public function updateMember(string $memberID, string $memberName = null)
    {
        $api = $this->createApiObject(false, true);

        $api->setParam('memberID', $memberID);
        if ($memberName) {
            $api->setParam('memberName', $memberName);
        }

        return $api->request(Api::API_UPDATE_MEMBER);
    }

    public function searchMember(string $memberID)
    {
        $api = $this->createApiObject(false, true);

        $api->setParam('memberID', $memberID);

        return $api->request(Api::API_SEARCH_MEMBER);
    }

    public function deleteMember(string $memberID)
    {
        $api = $this->createApiObject(false, true);

        $api->setParam('memberID', $memberID);

        return $api->request(Api::API_DELETE_MEMBER);
    }
}
