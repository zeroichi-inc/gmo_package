<?php

namespace GMO\Payment;

class Api
{
    public const METHOD_ENTRY_TRAN = 'EntryTran';
    public const METHOD_EXEC_TRAN = 'ExecTran';
    public const METHOD_SECURE_TRAN = 'SecureTran';
    public const METHOD_SECURE_TRAN2 = 'SecureTran2';
    public const METHOD_ALTER_TRAN = 'AlterTran';
    public const METHOD_CHANGE_TRAN = 'ChangeTran';
    public const METHOD_SEARCH_TRADE = 'SearchTrade';
    public const METHOD_SAVE_MEMBER = 'SaveMember';
    public const METHOD_UPDATE_MEMBER = 'UpdateMember';
    public const METHOD_SEARCH_MEMBER = 'SearchMember';
    public const METHOD_DELETE_MEMBER = 'DeleteMember';
    public const METHOD_SAVE_CARD = 'SaveCard';
    public const METHOD_TRADED_CARD = 'TradedCard';
    public const METHOD_SEARCH_CARD = 'SearchCard';
    public const METHOD_SEARCH_CARD_DETAIL = 'SearchCardDetail';
    public const METHOD_DELETE_CARD = 'DeleteCard';
    // Recurring payments
    public const METHOD_REGISTER_RECURRING_CREDIT = 'RegisterRecurringCredit';
    public const METHOD_REGISTER_RECURRING_ACCOUNT_TRANS = 'RegisterRecurringAccountTrans';
    public const METHOD_UNREGISTER_RECURRING = 'UnregisterRecurring';
    public const METHOD_CHANGE_RECURRING = 'ChangeRecurring';
    public const METHOD_SEARCH_RECURRING = 'SearchRecurring';
    public const METHOD_SEARCH_RECURRING_RESULT = 'SearchRecurringResult';
    public const METHOD_SEARCH_RECURRING_RESULT_FILE = 'SearchRecurringResultFile';


    public const JOBCD_CHECK = 'CHECK';
    public const JOBCD_CAPTURE = 'CAPTURE';
    public const JOBCD_AUTH = 'AUTH';
    public const JOBCD_SAUTH = 'SAUTH';
    public const JOBCD_VOID = 'VOID';
    public const JOBCD_RETURN = 'RETURN';
    public const JOBCD_RETURNX = 'RETURNX';
    public const JOBCD_SALES = 'SALES';
    public const JOBCD_CANCEL = 'CANCEL';

    public const API_JSON = 'api_json';
    public const API_IDPASS = 'api_idpass';

    protected string $apiBaseUrl;
    protected array $params = array();
    protected string $apiType;

    public function __construct(string $apiBaseUrl, string $apiType = self::API_JSON)
    {
        $this->apiBaseUrl = $apiBaseUrl;
        $this->apiType = $apiType;
    }

    public function setParam(string $name, $value)
    {
        $this->params[$name] = $value;
    }

    public function setParamArray(array $params)
    {
        foreach ($params as $key => $value) {
            $this->setParam($key, $value);
        }
    }

    public function unsetParam(string $name)
    {
        unset($this->params[$name]);
    }

    public function request(string $apiMethod, bool $rawResponse = false)
    {
        if ($this->apiType == self::API_JSON) {
            return $this->requestJson($apiMethod, $rawResponse);
        } else if ($this->apiType == self::API_IDPASS) {
            return $this->requestIdpass($apiMethod, $rawResponse);
        }
    }

    public function requestJson(string $apiMethod, bool $rawResponse)
    {
        $methodUrl = $this->apiBaseUrl . '/' . $apiMethod . '.json';

        $ch = curl_init();

        $opts = array(
            CURLOPT_URL => $methodUrl,
            CURLOPT_POSTFIELDS => json_encode($this->params),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json;charset=UTF-8', 'Accept: application/json'),
            CURLOPT_RETURNTRANSFER => true,
            CURLINFO_HEADER_OUT => true, // Track request headers
        );
        curl_setopt_array($ch, $opts);

        $curlres = curl_exec($ch);
        $curlinfo = curl_getinfo($ch);
        curl_close($ch);

        // In case cURL fails to connect
        if ($curlres === false) {
            return NULL;
        }

        $response = array(
            'status' => $curlinfo['http_code'],
            'response' => $rawResponse? $curlres: json_decode($curlres, true),
        );

        return $response;
    }

    public function requestIdpass(string $apiMethod, bool $rawResponse)
    {
        $methodUrl = $this->apiBaseUrl . '/' . $apiMethod . '.idPass';

        $ch = curl_init();

        $opts = array(
            CURLOPT_URL => $methodUrl,
            CURLOPT_POSTFIELDS => $this->getUrlEncodedParams(),
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded;charset=windows-31j'),
            CURLOPT_RETURNTRANSFER => true,
            CURLINFO_HEADER_OUT => true, // Track request headers
        );
        curl_setopt_array($ch, $opts);

        $curlres = curl_exec($ch);
        $curlinfo = curl_getinfo($ch);
        curl_close($ch);

        // In case cURL fails to connect
        if ($curlres === false) {
            return NULL;
        }

        // Convert the response to utf-8
        $curlres = mb_convert_encoding($curlres, 'UTF-8', 'SJIS');

        $response = array(
            'status' => $this->getIDPassResponseCode($curlres),
            'response' => $rawResponse? $curlres: $this->convertIDPassToJson($curlres),
        );

        return $response;
    }

    protected function getUrlEncodedParams()
    {
        $camelCaseParams = array();

        foreach($this->params as $key => $value) {
            $key[0] = strtoupper($key[0]);
            $camelCaseParams[$key] = $value;
        }

        // The old api requires Shift-JIS (Windows31j) encoding
        return mb_convert_encoding(http_build_query($camelCaseParams), 'SJIS');
    }

    protected convertArrayToXML(string $name, array $data)
    {
        $itemQueue = [];

        foreach ($data as $item) {
            foreach ($item as $key => $value) {
                if (is_array($value)) {
                    $itemQueue[] = $this->convertArrayToXML($key, $value);
                } else {
                    $itemQueue[] = "<${key}>${value}</${key}>";
                }
            }
        }

        return "<${name}>" . implode($itemQueue) . "</${name}>";
    }

    protected function convertIDPassToJson(string $idPassRes)
    {
        $res = array();

        parse_str($idPassRes, $parsedIdPassRes);

        $isListOfArrays = true;
        $prevSize = NULL;

        foreach ($parsedIdPassRes as $key => $value) {
            // Match JSON api's casing
            $key[0] = strtolower($key[0]);

            $subvalues = explode('|', $value);

            // Determine if the response is a list of arrays
            // All items have to be the same size, or in other
            // words, the first item has to be the same size as the
            // following ones
            if ($isListOfArrays) {
                if (is_null($prevSize)) {
                    $prevSize = sizeof($subvalues);
                } else if ($prevSize != sizeof($subvalues)) {
                    $isListOfArrays = false;
                }
            }

            // Set correct value for null properties
            foreach ($subvalues as $index => $subvalue) {
                if (strlen($subvalue) <= 0) {
                    $subvalues[$index] = NULL;
                }
            }

            $res[$key] = sizeof($subvalues) > 1? $subvalues: (sizeof($subvalues) == 1? $subvalues[0]: NULL);
        }

        if ($isListOfArrays && $prevSize > 1) {
            $tmpRes = array();
            for ($i = 0; $i < $prevSize; $i++) {
                $item = array();
                foreach ($res as $key => $value) {
                    $item[$key] = $value[$i];
                }

                $tmpRes[] = $item;
            }

            $res = $tmpRes;
        }

        return $res;
    }

    protected function getIDPassResponseCode(string $idPassRes)
    {
        $resCode = 200;
        parse_str($idPassRes, $parsedIdPassRes);

        if (array_key_exists('ErrCode', $parsedIdPassRes)) {
            // The circumstances in which each error happens are not clear.
            // Other response codes may be implemented later.
            $resCode = 400;
        }

        return $resCode;
    }
}
