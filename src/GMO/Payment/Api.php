<?php

namespace GMO\Payment;

class Api
{
    public const API_ENTRY_TRAN = 'EntryTran';
    public const API_EXEC_TRAN = 'ExecTran';
    public const API_ALTER_TRAN = 'AlterTran';
    public const API_SECURE_TRAN = 'SecureTran';

    public const JOBCD_CHECK = 'CHECK';
    public const JOBCD_CAPTURE = 'CAPTURE';
    public const JOBCD_AUTH = 'AUTH';
    public const JOBCD_SAUTH = 'SAUTH';
    public const JOBCD_VOID = 'VOID';
    public const JOBCD_RETURN = 'RETURN';
    public const JOBCD_RETURNX = 'RETURNX';
    public const JOBCD_SALES = 'SALES';
    public const JOBCD_CANCEL = 'CANCEL';

    protected string $apiBaseUrl;
    protected array $params = array();

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function setParam(string $name, $value)
    {
        $this->params[$name] = $value;
    }

    public function unsetParam(string $name)
    {
        unset($this->params[$name]);
    }

    public function request(string $apiMethod)
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
}
