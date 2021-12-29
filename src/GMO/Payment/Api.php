<?php

namespace GMO\Payment;

class Api
{
    public const API_ENTRY_TRAN = 'EntryTran';
    public const API_EXEC_TRAN = 'ExecTran';
    public const API_SECURE_TRAN = 'SecureTran';
    public const API_SECURE_TRAN2 = 'SecureTran2';
    public const API_ALTER_TRAN = 'AlterTran';
    public const API_CHANGE_TRAN = 'ChangeTran';
    public const API_SEARCH_TRADE = 'SearchTrade';
    public const API_SAVE_MEMBER = 'SaveMember';
    public const API_UPDATE_MEMBER = 'UpdateMember';
    public const API_SEARCH_MEMBER = 'SearchMember';
    public const API_DELETE_MEMBER = 'DeleteMember';
    public const API_SAVE_CARD = 'SaveCard';
    public const API_TRADED_CARD = 'TradedCard';
    public const API_SEARCH_CARD = 'SearchCard';
    public const API_SEARCH_CARD_DETAIL = 'SearchCardDetail';
    public const API_DELETE_CARD = 'DeleteCard';

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

    public function request(string $apiMethod)
    {
        if ($this->apiType == self::API_JSON) {
            return $this->requestJson($apiMethod);
        } else if ($this->apiType == self::API_IDPASS) {
            return $this->requestIdpass($apiMethod);
        }
    }

    public function requestJson(string $apiMethod)
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
            'response' => json_decode($curlres, true),
        );

        return $response;
    }

    public function requestIdpass(string $apiMethod)
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

        $response = array(
            'status' => $this->getIDPassResponseCode($curlres),
            'response' => $this->convertIDPassToJson($curlres),
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

    protected function convertIDPassToJson(string $idPassRes)
    {
        $res = array();

        // Convert the response to utf-8
        $idPassRes = mb_convert_encoding($idPassRes, 'UTF-8', 'SJIS');
        parse_str($idPassRes, $parsedIdPassRes);

        // Change keys casing
        foreach ($parsedIdPassRes as $key => $value) {
            $key[0] = strtolower($key[0]);

            if (strpos($value, '|') !== false) {
                $values = explode('|', $value);

                foreach($values as $index => $val) {
                    if (is_string($val) && strlen($val) == 0) {
                        $val = NULL;
                    }

                    $res[$index][$key] = $val;
                }
            } else {
                if (is_string($value) && strlen($value) == 0) {
                    $value = NULL;
                }

                $res[$key] = $value;
            }
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
