<?php

namespace GMO\Payment;

class PayPay extends Api
{
    private const METHOD_ENTRY_TRAN_PAYPAY = 'EntryTranPaypay';
    private const METHOD_EXEC_TRAN_PAYPAY = 'ExecTranPaypay';
    private const METHOD_PAYPAY_START = 'PaypayStart';
    private const METHOD_PAYPAY_SALES = 'PaypaySales';
    private const METHOD_PAYPAY_CANCEL_RETURN = 'PaypayCancelReturn';


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
}
