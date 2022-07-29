<?php

namespace GMO\Payment;

use GMO\Payment\Api;

class PostPayment
{
    private string $host;

    private string $siteID = "";
    private string $sitePass = "";

    private string $shopID = "";
    private string $shopPass = "";

    private array $apiParams = [];

    private bool $forceOldApi;

    public function __construct(string $host, array $credentials, bool $forceOldApi = false)
    {
        $this->host = $host;
        $this->forceOldApi = $forceOldApi;

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

    private function createApiObject(bool $withShop = true, bool $withSite = false, bool $forceOldApi = false)
    {
        $api = new Api($this->host, ($this->forceOldApi || $forceOldApi)? Api::API_IDPASS: Api::API_JSON);

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
}
