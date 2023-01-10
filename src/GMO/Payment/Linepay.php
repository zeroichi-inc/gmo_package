<?php

namespace GMO\Payment;

class Linepay extends Api
{
    use SetCredentials;


    private const METHOD_ENTRY_TRAN_LINEPAY = 'EntryTranLinepay';
    private const METHOD_EXEC_TRAN_LINEPAY = 'ExecTranLinepay';
    private const METHOD_LINEPAY_START = 'LinepayStart';
    private const METHOD_LINEPAY_SALES = 'LinepaySales';
    private const METHOD_LINEPAY_CANCEL_RETURN = 'LinepayCancelReturn';
    private const METHOD_SEARCH_TRADE_MULTI = 'SearchTradeMulti';

    private const PAYTYPE_LINEPAY = 20;


    public function __construct(string $host, array $credentials)
    {
        // Linepay only supports idPass api
        parent::__construct($host, self::API_IDPASS);

        $this->receiveCredentials($credentials);
    }

    public function entryTranPaypay(string $orderID, int $amount, int $tax, string $jobCd = self::JOBCD_AUTH)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('jobCd', $jobCd);
        $this->setParam('amount', $amount);
        if ($tax > 0) $this->setParam('tax', $tax);

        return $this->request(self::METHOD_ENTRY_TRAN_LINEPAY);
    }

    public function execTranPaypay(
        string $orderID,
        string $accessID,
        string $accessPass,
        string $retURL,
        string $errorRcvURL,
        string $productName,
        ?string $productImageUrl,
        ?string $langCd,
        ?string $userInfo,
        ?string $returnUrl,
        ?string $branchName,
        ?string $branchID,
        array $clientFields = [],
        bool $returnClientFlags = false,
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);
        $this->setParam('orderID', $orderID);
        $this->setParam('retURL', $retURL);
        $this->setParam('errorRcvURL', $errorRcvURL);
        $this->setParam('productName', $productName);

        if (!is_null($productImageUrl)) $this->setParam('productImageUrl', $productImageUrl);
        if (!is_null($langCd)) $this->setParam('langCd', $langCd);
        if (!is_null($userInfo)) $this->setParam('userInfo', $userInfo);
        if (!is_null($returnUrl)) $this->setParam('returnUrl', $returnUrl);
        if (!is_null($branchName)) $this->setParam('branchName', $branchName);
        if (!is_null($branchID)) $this->setParam('branchID', $branchID);

        // client fields
        for ($i = 1; $i <= min(count($clientFields), 3); $i++) {
            $this->setParam("clientField${i}", $clientFields[$i - 1]);
        }
        if ($returnClientFlags) {
            $this->setParam('clientFieldFlag', 1);
        }

        return $this->request(self::METHOD_EXEC_TRAN_LINEPAY);
    }

    public function linepayStart(string $accessID, string $token)
    {
        $this->setParam('accessID', $accessID);
        $this->setParam('token', $token);

        // This method does not support a JSON equivalent.
        return $this->request(self::METHOD_LINEPAY_START);
    }

    public function linepaySales(
        string $orderID,
        string $accessID,
        string $accessPass,
        int $amount,
        int $tax
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);
        $this->setParam('orderID', $orderID);

        $this->setParam('amount', $amount);
        if ($tax > 0) $this->setParam('tax', $tax);

        return $this->request(self::METHOD_LINEPAY_SALES);
    }

    public function linepayCancelReturn(
        string $accessID,
        string $accessPass,
        int $cancelAmount,
        int $cancelTax
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);

        $this->setParam('cancelAmount', $cancelAmount);
        if ($cancelTax > 0) $this->setParam('cancelTax', $cancelTax);

        return $this->request(self::METHOD_LINEPAY_CANCEL_RETURN);
    }

    public function searchTradeMulti(string $orderID)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('payType', self::PAYTYPE_LINEPAY);

        return $this->requestIdpass(self::METHOD_SEARCH_TRADE_MULTI);
    }
}
