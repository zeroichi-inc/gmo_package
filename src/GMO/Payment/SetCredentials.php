<?php

namespace GMO\Payment;

trait SetCredentials
{
    private string $siteID = "";
    private string $sitePass = "";
    private string $shopID = "";
    private string $shopPass = "";

    private function receiveCredentials(array $credentials)
    {
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

    private function setSiteCredentials()
    {
        $this->setParam('siteID', $this->siteID);
        $this->setParam('sitePass', $this->sitePass);
    }

    private function setShopCredentials()
    {
        $this->setParam('shopID', $this->shopID);
        $this->setParam('shopPass', $this->shopPass);
    }
}
