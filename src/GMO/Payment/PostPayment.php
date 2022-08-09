<?php

namespace GMO\Payment;

use GMO\Payment\Api;

class PostPayment extends Api
{
    private const METHOD_ENTRY_TRAN_POSTPAY = 'EntryTranPostpay';
    private const METHOD_EXEC_TRAN_POSTPAY = 'ExecTranPostpay';
    private const METHOD_POSTPAY_INVOICE_DATA = 'PostpayInvoiceData';
    private const METHOD_POSTPAY_SHIPPING = 'PostpayShipping';
    private const METHOD_POSTPAY_CHANGE = 'PostpayChange';
    private const METHOD_POSTPAY_CANCEL = 'PostpayCancel';
    private const METHOD_POSTPAY_SHIPPING_CHANGE = 'PostpayShippingChange';
    private const METHOD_POSTPAY_SHIPPING_CANCEL = 'PostpayShippingCancel';
    private const METHOD_POSTPAY_REISSUE_INVOICE = 'PostpayReissueInvoice';
    private const METHOD_SEARCH_TRADE_POSTPAY = 'SearchTradePostpay';
    private const METHOD_POSTPAY_REDUCTION = 'PostpayReduction';

    private const REISSUE_DESTINATION_CURRENT = '1';
    private const REISSUE_DESTINATION_NEW = '2';

    private const REISSUE_REASON_LOST = '01';
    private const REISSUE_REASON_NOT_RECEIVED = '02';
    private const REISSUE_REASON_RELOCATION = '03';
    private const REISSUE_REASON_OTHER = '99';


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

    private function setShopCredentials()
    {
        $this->setParam('shopID', $this->shopID);
        $this->setParam('shopPass', $this->shopPass);
    }


    public function entryTranPostpay(string $orderID, int $amount, int $tax = null)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('amount', $amount);
        if (!is_null($tax)) $this->setParam('tax', $jobCd);

        return $this->request(self::METHOD_ENTRY_TRAN_POSTPAY);
    }

    public function execTranPostpay(
        string $orderID,
        string $accessID,
        string $accessPass,
        array $customerInfo,
        array $details,
        array $deliveryInfo = [],
        array $httpHeaderInfo = [],
        array $clientFields = []
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);
        $this->setParam('orderID', $orderID);

        // TODO: set header info

        // shared parameters with postpayChange
        $this->setPostpayOrderInfo($customerInfo, $deliveryInfo, $details);

        // client fields
        for ($i = 1; $i <= min(count($clientFields), 3); $i++) {
            $this->setParam("clientField${i}", $clientFields[$i - 1]);
        }

        return $this->request(self::METHOD_EXEC_TRAN_POSTPAY);
    }

    private function convertDetailsArrayToXML(array $details)
    {
        $detailItems = [];
        foreach ($details as $detail) {
            $properties = [];
            foreach ($detail as $key => $value) {
                $properties[] = "<${key}>${value}</${key}>";
            }
            $detailItems[] = "<detail>" . implode($properties) . "</detail>";
        }

        return base64_encode("<detailsInfo>" . implode($detailItems) . "</detailsInfo>");
    }

    public function postpayInvoiceData(string $orderID, string $accessID, string $accessPass)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);

        return $this->request(self::METHOD_POSTPAY_INVOICE_DATA);
    }

    public function postpayShipping(string $orderID, string $accessID, string $accessPass, $pdCompanyCode, $slipNumber)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);
        $this->setParam('pdCompanyCode', $pdCompanyCode);
        $this->setParam('slipNo', $slipNumber);

        return $this->request(self::METHOD_POSTPAY_SHIPPING);
    }

    public function postpayChange(
        string $orderID,
        string $accessID,
        string $accessPass,
        array $customerInfo,
        array $deliveryInfo,
        array $details
    )
    {
        $this->setShopCredentials();

        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);
        $this->setParam('orderID', $orderID);

        // shared parameters with execTranPostpay
        $this->setPostpayOrderInfo($customerInfo, $deliveryInfo, $details);

        return $this->request(self::METHOD_POSTPAY_CHANGE);
    }

    private function setPostpayOrderInfo(array $customerInfo, array $deliveryInfo, array $details)
    {
        // Customer info
        foreach ($customerInfo as $key => $value) {
            $key[0] = strtoupper($key[0]);
            $this->setParam("customer${key}", $value);
        }

        // Delivery info
        foreach ($deliveryInfo as $key => $value) {
            $key[0] = strtoupper($key[0]);
            $this->setParam("delivery${key}", $value);
        }

        $this->setDetails($details);
    }

    private function setDetails(array $details)
    {
        // Details
        if ($this->apiType == self::API_IDPASS) {
            if (count($details) > 1) {
                $this->setParam('multiItem', $this->convertDetailsArrayToXML($details));
            } else {
                $detail = $details[0];
                foreach ($detail as $key => $value) {
                    $key[0] = strtoupper($key[0]);
                    $this->setParam("detail${key}", $value);
                }
            }
        } else {
            $this->setParam('details', $details);
        }
    }

    public function postpayCancel(string $orderID, string $accessID, string $accessPass)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);

        return $this->request(self::METHOD_POSTPAY_CANCEL);
    }

    public function postpayShippingChange(string $orderID, string $accessID, string $accessPass, $pdCompanyCode, $slipNumber)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);
        $this->setParam('pdCompanyCode', $pdCompanyCode);
        $this->setParam('slipNo', $slipNumber);

        return $this->request(self::METHOD_POSTPAY_SHIPPING_CHANGE);
    }

    public function postpayShippingCancel(string $orderID, string $accessID, string $accessPass)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);

        return $this->request(self::METHOD_POSTPAY_SHIPPING_CANCEL);
    }

    public function postpayReissueInvoice(
        string $orderID,
        string $accessID,
        string $accessPass,
        string $destination,
        string $reason,
        string $reasonOther = null,
        array $customerInfo = []
    )
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);

        $this->setParam('reasonCode', $reason);
        if ($reason == self::REISSUE_REASON_OTHER) {
            $this->setParam('otherReason', $reasonOther);
        }

        // Customer info
        if ($destination == self::REISSUE_DESTINATION_NEW) {
            foreach ($customerInfo as $key => $value) {
                $key[0] = strtoupper($key[0]);
                $this->setParam("customer${key}", $value);
            }
        }

        return $this->request(self::METHOD_POSTPAY_REISSUE_INVOICE);
    }

    public function postpayReduction(string $orderID, string $accessID, string $accessPass, $amount, $tax = null, array $details)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);
        $this->setParam('accessID', $accessID);
        $this->setParam('accessPass', $accessPass);

        $this->setParam('amount', $amount);
        if (!is_null($tax)) $this->setParam('tax', $tax);

        $this->setDetails($details);

        return $this->request(self::METHOD_POSTPAY_REDUCTION);
    }

    public function searchTradePostpay(string $orderID)
    {
        $this->setShopCredentials();

        $this->setParam('orderID', $orderID);

        return $this->request(self::METHOD_SEARCH_TRADE_POSTPAY);
    }
}
