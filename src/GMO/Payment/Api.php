<?php

namespace GMO\Payment;

class Api
{
    protected string $apiBaseUrl;
    protected array $params = array();

    public function __construct(string $apiBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function request(string $apiMethod)
    {
        $methodUrl = $this->apiBaseUrl . '/' . $apiMethod;

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
            'response' => json_decode($curlres),
        );

        return $response;
    }
}
