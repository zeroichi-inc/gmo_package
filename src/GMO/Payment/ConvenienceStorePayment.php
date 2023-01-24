<?php

namespace GMO\Payment;

class ConvenienceStorePayment extends Api
{
    use SetCredentials;


    private const METHOD_ENTRY_TRAN_CVS = 'EntryTranCvs';
    private const METHOD_EXEC_TRAN_CVS = 'ExecTranCvs';
    private const METHOD_CVS_CANCEL = 'CvsCancel';
    private const METHOD_SEARCH_TRADE_MULTI = 'SearchTradeMulti';

    private const PAYTYPE_CONVENIENCE_STORE = 3;


    public const CONVENIENCE_SEVEN_ELEVEN = '00007';
    public const CONVENIENCE_LAWSON = '10001';
    public const CONVENIENCE_FAMILY_MART = '10002';
    public const CONVENIENCE_MINISTOP = '10005';
    public const CONVENIENCE_SEIKO_MART = '10008';


    public function __construct(string $host, array $credentials, bool $forceOldApi = false)
    {
        parent::__construct($host, $this->apiType = $forceOldApi? self::API_IDPASS: self::API_JSON);

        $this->receiveCredentials($credentials);
    }

    public function entryTranCvs(string $orderID, int $amount, int $tax = 0)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('amount', $amount);
        if ($tax > 0) $this->setParam('tax', $tax);

        return $this->request(self::METHOD_ENTRY_TRAN_CVS);
    }

    public function execTranCvs(
        string $orderID,
        string $accessID,
        string $accessPass,
        string $convenience,
        string $customerName,
        string $customerKana,
        string $telNo,
        string $paymentTermDay = null,
        string $mailAddress = null,
        string $shopMailAddress = null,
        string $reserveNo = null,
        string $memberNo = null,
        array $registerDisp = [],
        array $receiptsDisp = [],
        array $clientFields = [],
        bool $clientFieldFlag = false
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);
        $this->setParam('orderID', $orderID);
        $this->setParam('convenience', $convenience);
        $this->setParam('customerName', $customerName);
        $this->setParam('customerKana', $customerKana);
        $this->setParam('telNo', $telNo);
        if (!is_null($paymentTermDay)) $this->setParam('paymentTermDay', $paymentTermDay);
        if (!is_null($mailAddress)) $this->setParam('mailAddress', $mailAddress);
        if (!is_null($shopMailAddress)) $this->setParam('shopMailAddress', $shopMailAddress);
        if (!is_null($reserveNo)) $this->setParam('reserveNo', $reserveNo);
        if (!is_null($memberNo)) $this->setParam('memberNo', $memberNo);

        // register fields
        for ($i = 1; $i <= min(count($registerDisp), 8); $i++) {
            $this->setParam("registerDisp${i}", $registerDisp[$i - 1]);
        }

        // receipt fields
        for ($i = 1; $i <= min(count($receiptsDisp), 13); $i++) {
            $this->setParam("receiptsDisp${i}", $receiptsDisp[$i - 1]);
        }

        // client fields
        for ($i = 1; $i <= min(count($clientFields), 3); $i++) {
            $this->setParam("clientField${i}", $clientFields[$i - 1]);
        }
        if ($clientFieldFlag) {
            $this->setParam('clientFieldFlag', 1);
        }

        return $this->request(self::METHOD_EXEC_TRAN_CVS);
    }

    public function cvsCancel(
        string $orderID,
        string $accessID,
        string $accessPass
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);
        $this->setParam('orderID', $orderID);

        return $this->request(self::METHOD_CVS_CANCEL);
    }

    public function searchTradeMulti(string $orderID)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('payType', self::PAYTYPE_CONVENIENCE_STORE);

        return $this->requestIdpass(self::METHOD_SEARCH_TRADE_MULTI);
    }
}
