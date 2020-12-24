<?php

namespace GMO\Payment;

class CreditCardPayment
{
    private string $host;

    private string $siteId = "";
    private string $sitePass = "";

    private string $shopId = "";
    private string $shopPass = "";

    public function __construct(string $host, array $credentials)
    {
        if (array_key_exists('siteId', $credentials)) {
            $this->siteId = $credentials['siteId'];
        }
        if (array_key_exists('sitePass', $credentials)) {
            $this->sitePass = $credentials['sitePass'];
        }
        if (array_key_exists('shopId', $credentials)) {
            $this->shopId = $credentials['shopId'];
        }
        if (array_key_exists('shopPass', $credentials)) {
            $this->shopPass = $credentials['shopPass'];
        }
    }
}
