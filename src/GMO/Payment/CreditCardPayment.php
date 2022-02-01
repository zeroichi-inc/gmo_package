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

    private bool $forceOldApi;

    private const RECURRENT_RESULTS_FILE_HEADER_CREDIT = [
        'shopID',
        'recurringID',
        'orderID',
        'chargeDate',
        'status',
        'amount',
        'tax',
        'nextChargeDate',
        'accessID',
        'accessPass',
        'forward',
        'approvalNo',
        'errCode',
        'errInfo',
        'processDate'
    ];

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

    public function entryTran(string $orderID, int $amount = 0, string $jobCd = Api::JOBCD_CAPTURE, array $optional = [])
    {
        $api = $this->createApiObject();

        $api->setParam('orderID', $orderID);
        $api->setParam('jobCd', $jobCd);
        if ($jobCd != Api::JOBCD_CHECK) {
            $api->setParam('amount', $amount);
        }

        $api->setParamArray($optional);

        return $api->request(Api::METHOD_ENTRY_TRAN);
    }

    public function execTran(string $accessID, string $accessPass, string $orderID, array $auth = [], int $method = 1, array $optional = [])
    {
        $useSavedCard = array_key_exists('memberID', $auth) && array_key_exists('cardSeq', $auth);

        $api = $this->createApiObject(true, $useSavedCard);

        $api->setParam('accessID', $accessID);
        $api->setParam('accessPass', $accessPass);
        $api->setParam('orderID', $orderID);
        $api->setParam('method', $method);
        if ($useSavedCard) {
            $api->setParam('memberID', $auth['memberID']);
            $api->setParam('cardSeq', $auth['cardSeq']);
        } else {
            $api->setParam('token', array_key_exists('token', $auth)? $auth['token'] : '');
        }

        $api->setParamArray($optional);

        return $api->request(Api::METHOD_EXEC_TRAN);
    }

    public function secureTran2(string $accessID, string $accessPass)
    {
        $api = $this->createApiObject(false, false);

        $api->setParam('accessID', $accessID);
        $api->setParam('accessPass', $accessPass);

        return $api->request(Api::METHOD_SECURE_TRAN2);
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

        return $api->request(Api::METHOD_ALTER_TRAN);
    }

    public function changeTran(string $accessID, string $accessPass, string $jobCd, int $amount, array $optional = [])
    {
        $api = $this->createApiObject();

        $api->setParam('accessID', $accessID);
        $api->setParam('accessPass', $accessPass);
        $api->setParam('jobCd', $jobCd);
        $api->setParam('amount', $amount);

        $api->setParamArray($optional);

        return $api->request(Api::METHOD_CHANGE_TRAN);
    }

    public function searchTrade(string $orderID)
    {
        $api = $this->createApiObject();

        $api->setParam('orderID', $orderID);

        return $api->request(Api::METHOD_SEARCH_TRADE);
    }

    public function saveMember(string $memberID, string $memberName = null)
    {
        $api = $this->createApiObject(false, true);

        $api->setParam('memberID', $memberID);
        if ($memberName) {
            $api->setParam('memberName', $memberName);
        }

        return $api->request(Api::METHOD_SAVE_MEMBER);
    }

    public function updateMember(string $memberID, string $memberName = null)
    {
        $api = $this->createApiObject(false, true);

        $api->setParam('memberID', $memberID);
        if ($memberName) {
            $api->setParam('memberName', $memberName);
        }

        return $api->request(Api::METHOD_UPDATE_MEMBER);
    }

    public function searchMember(string $memberID)
    {
        $api = $this->createApiObject(false, true);

        $api->setParam('memberID', $memberID);

        return $api->request(Api::METHOD_SEARCH_MEMBER);
    }

    public function deleteMember(string $memberID)
    {
        $api = $this->createApiObject(false, true);

        $api->setParam('memberID', $memberID);

        return $api->request(Api::METHOD_DELETE_MEMBER);
    }

    public function saveCard(string $memberID, string $token = '', array $optional = [])
    {
        $api = $this->createApiObject(false, true);

        $api->setParam('memberID', $memberID);
        $api->setParam('token', $token);
        $api->setParamArray($optional);

        return $api->request(Api::METHOD_SAVE_CARD);
    }

    public function tradedCard(string $orderID, string $memberID)
    {
        $api = $this->createApiObject(true, true);

        $api->setParam('orderID', $orderID);
        $api->setParam('memberID', $memberID);

        return $api->request(Api::METHOD_TRADED_CARD);
    }

    public function searchCard(string $memberID)
    {
        $api = $this->createApiObject(false, true);

        $api->setParam('memberID', $memberID);

        return $api->request(Api::METHOD_SEARCH_CARD);
    }

    public function searchCardDetail(array $params)
    {
        $api = $this->createApiObject(
            !array_key_exists('memberID') || array_key_exists('searchType', $params),
            array_key_exists('memberID', $params)
        );

        $api->setParamArray($params);

        return $api->request(Api::METHOD_SEARCH_CARD_DETAIL);
    }

    public function deleteCard(string $memberID, string $cardSeq) {
        $api = $this->createApiObject(false, true);

        $api->setParam('memberID', $memberID);
        $api->setParam('cardSeq', $cardSeq);

        return $api->request(Api::METHOD_DELETE_CARD);
    }

    // Recurrent payment methods

    public function registerRecurringCredit(string $recurringID, int $amount, string $memberID, array $chargeDate = [])
    {
        $api = $this->createApiObject(true, true, true);

        $api->setParam('recurringID', $recurringID);
        $api->setParam('amount', $amount);
        $api->setParam('memberID', $memberID);
        $api->setParam('registType', 1);

        $api->setParam('chargeDay', $chargeDate['day']);

        return $api->request(Api::METHOD_REGISTER_RECURRING_CREDIT);
    }

    public function registerRecurringAccountTrans()
    {
        throw Exception('Unimplemented');
    }

    public function unregisterRecurring(string $recurringID)
    {
        $api = $this->createApiObject(true, false, true);

        $api->setParam('recurringID', $recurringID);

        return $api->request(Api::METHOD_UNREGISTER_RECURRING);
    }

    public function changeRecurring(string $recurringID, int $amount)
    {
        $api = $this->createApiObject(true, false, true);

        $api->setParam('recurringID', $recurringID);
        $api->setParam('amount', $amount);

        return $api->request(Api::METHOD_UNREGISTER_RECURRING);
    }

    public function searchRecurring(string $recurringID)
    {
        $api = $this->createApiObject(true, false, true);

        $api->setParam('recurringID', $recurringID);

        return $api->request(Api::METHOD_SEARCH_RECURRING);
    }

    public function searchRecurringResult(string $recurringID)
    {
        $api = $this->createApiObject(true, false, true);

        $api->setParam('recurringID', $recurringID);

        return $api->request(Api::METHOD_SEARCH_RECURRING_RESULT);
    }

    public function searchRecurringResultFile(string $method, string $chargeDate, bool $convertToArray = false)
    {
        $api = $this->createApiObject(true, false, true);

        $api->setParam('method', $method);
        $api->setParam('chargeDate', $chargeDate);

        $res = $api->request(Api::METHOD_SEARCH_RECURRING_RESULT_FILE, true);

        if ($convertToArray) {
            $res['response'] = $this->convertRecurringResultFileToArray($res['response'], $method);
        }

        return $res;
    }

    private function convertRecurringResultFileToArray(string $csvData, string $method)
    {
        $entries = preg_split("/\r\n/", $csvData);
        $header = self::RECURRENT_RESULTS_FILE_HEADER_CREDIT;

        array_walk($entries, function (&$item) use ($header) {
            $item = array_combine($header, str_getcsv($item));
        });

        return $entries;
    }
}
