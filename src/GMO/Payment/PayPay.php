<?php

namespace GMO\Payment;

class PayPay extends Api
{
    use SetCredentials;


    private const METHOD_ENTRY_TRAN_PAYPAY = 'EntryTranPaypay';
    private const METHOD_EXEC_TRAN_PAYPAY = 'ExecTranPaypay';
    private const METHOD_PAYPAY_START = 'PaypayStart';
    private const METHOD_PAYPAY_SALES = 'PaypaySales';
    private const METHOD_PAYPAY_CANCEL_RETURN = 'PaypayCancelReturn';


    public function __construct(string $host, array $credentials, bool $forceOldApi = false)
    {
        parent::__construct($host, $this->apiType = $forceOldApi? self::API_IDPASS: self::API_JSON);

        $this->receiveCredentials($credentials);
    }

    public function entryTranPaypay(string $orderID, int $amount, int $tax, string $jobCd = self::JOBCD_AUTH)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('jobCd', $jobCd);
        $this->setParam('amount', $amount);
        if ($tax > 0) $this->setParam('tax', $tax);

        return $this->request(self::METHOD_ENTRY_TRAN_PAYPAY);
    }

    public function execTranPaypay(
        string $orderID,
        string $accessID,
        string $accessPass,
        string $retUrl,
        array $clientFields = [],
        int $paymentTermSec = 120
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);
        $this->setParam('orderID', $orderID);
        $this->setParam('retURL', $retUrl);
        $this->setParam('paymentTermSec', $paymentTermSec);

        // client fields
        for ($i = 1; $i <= min(count($clientFields), 3); $i++) {
            $this->setParam("clientField${i}", $clientFields[$i - 1]);
        }

        return $this->request(self::METHOD_EXEC_TRAN_PAYPAY);
    }

    public function paypayStart(string $accessID, string $token)
    {
    }

    public function paypaySales(
        string $orderID,
        string $accessID,
        string $accessPass,
        int $amount,
        int $tax
    )
    {
    }

    public function paypayCancelReturn(
        string $orderID,
        string $accessID,
        string $accessPass,
        int $cancelAmount,
        int $cancelTax
    )
    {
    }
}
