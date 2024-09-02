<?php

namespace GMO\Payment;

class Amazonpay extends Api
{
    use SetCredentials;


    private const METHOD_ENTRY_TRAN_AMAZONPAY = 'EntryTranAmazonpay';
    private const METHOD_EXEC_TRAN_AMAZONPAY = 'ExecTranAmazonpay';
    private const METHOD_AMAZONPAY_START = 'AmazonpayStart';
    private const METHOD_AMAZONPAY_SALES = 'AmazonpaySales';
    private const METHOD_AMAZONPAY_CANCEL = 'AmazonpayCancel';
    private const METHOD_AMAZONPAY_CHANGE = 'AmazonpayChange';
    private const METHOD_SEARCH_ADDRESS_AMAZONPAY = 'SearchAddressAmazonpay';
    private const METHOD_SEARCH_TRADE_MULTI = 'SearchTradeMulti';

    private const PAYTYPE_AMAZONPAY = 38;


    public function __construct(string $host, array $credentials)
    {
        parent::__construct($host);

        $this->receiveCredentials($credentials);
    }

    public function entryTranAmazonpay(string $orderID, int $amount, int $tax = 0, string $jobCd = self::JOBCD_AUTH)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('jobCd', $jobCd);
        $this->setParam('amount', $amount);
        if ($tax > 0) $this->setParam('tax', $tax);
        $this->setParam('amazonpayType', '3');

        return $this->request(self::METHOD_ENTRY_TRAN_AMAZONPAY);
    }

    public function execTranAmazonpay(
        string $orderID,
        string $accessID,
        string $accessPass,
        string $retURL,
        string $amazonCheckoutSessionID,
        int $paymentTermSec = 120,
        array $clientFields = []
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);
        $this->setParam('orderID', $orderID);
        $this->setParam('retURL', $retURL);
        $this->setParam('amazonCheckoutSessionID', $amazonCheckoutSessionID);
        $this->setParam('paymentTermSec', $paymentTermSec);

        // client fields
        for ($i = 1; $i <= min(count($clientFields), 3); $i++) {
            $this->setParam("clientField${i}", $clientFields[$i - 1]);
        }

        return $this->request(self::METHOD_EXEC_TRAN_AMAZONPAY);
    }

    public function amazonpayStart(string $accessID, string $token)
    {
        $this->setParam('accessID', $accessID);
        $this->setParam('token', $token);

        return $this->requestIdpass(self::METHOD_AMAZONPAY_START, true, false);
    }

    public function amazonpaySales(
        string $orderID,
        string $accessID,
        string $accessPass,
        int $amount,
        int $tax = 0
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);
        $this->setParam('orderID', $orderID);

        $this->setParam('amount', $amount);
        if ($tax > 0) $this->setParam('tax', $tax);

        return $this->request(self::METHOD_AMAZONPAY_SALES);
    }

    public function amazonpayCancel(
        string $orderID,
        string $accessID,
        string $accessPass,
        int $cancelAmount,
        int $cancelTax = 0
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);

        $this->setParam('cancelAmount', $cancelAmount);
        if ($cancelTax > 0) $this->setParam('cancelTax', $cancelTax);

        return $this->request(self::METHOD_AMAZONPAY_CANCEL);
    }

    public function amazonpayChange(
        string $orderID,
        string $accessID,
        string $accessPass,
        int $amount,
        int $tax = 0
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);
        $this->setParam('orderID', $orderID);

        $this->setParam('amount', $amount);
        if ($tax > 0) $this->setParam('tax', $tax);

        return $this->request(self::METHOD_AMAZONPAY_CANCEL);
    }

    public function searchAddressAmazonPay(
        string $orderID = null,
        string $amazonCheckoutSessionID = null,
        string $amazonBuyerToken = null,
    )
    {
        $this->setShopCredentials();

        if (!is_null($orderID)) {
            $this->setParam('orderID', $orderID);
        }
        if (!is_null($orderID)) {
            $this->setParam('amazonCheckoutSessionID', $amazonCheckoutSessionID);
        }
        if (!is_null($orderID)) {
            $this->setParam('amazonBuyerToken', $amazonBuyerToken);
        }

        return $this->request(self::METHOD_SEARCH_ADDRESS_AMAZONPAY);
    }

    public function searchTradeMulti(string $orderID)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('payType', self::PAYTYPE_AMAZONPAY);

        return $this->requestIdpass(self::METHOD_SEARCH_TRADE_MULTI);
    }
}
