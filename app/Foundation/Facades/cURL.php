<?php
namespace App\Foundation\Facades;

use Illuminate\Support\Facades\Facade;
use anlutro\cURL\cURLException;
use anlutro\cURL\Request;

/**
 * cURL facade class.
 */
class cURL extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'anlutro\cURL\cURL';
    }

    /**
     * send cURL request
     *
     * @param string $url  request url
     * @param string $method HTTP method
     * @param array $data request data
     * @param array $headers request headers
     * @param array $options curl options
     * @param int $encoding encoding
     * @return mixed array | false
     */
    public static function sendRequest($url, $method = 'GET', $data = [], $headers = [], $options = [],
                                   $encoding = Request::ENCODING_QUERY) : ?array
    {
        $baseOptions = [
            CURLOPT_TIMEOUT_MS => 30000,
            CURLOPT_CONNECTTIMEOUT_MS => 1000,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_FRESH_CONNECT => true,
        ];
        $options = $baseOptions + $options;

        try {
            $request = cURL::newRequest($method, $url)
                ->setHeaders($headers)
                ->setOptions($options);
            if ($data) {
                $request->setData($data);
                $request->setEncoding($encoding);
            }
            $response  = json_decode($request->send()->body, true);

        } catch (cURLException $exc) {
            sentryCaptureException($exc);
            $response = null;
        }

        return $response;
    }

    /**
     * Send json request
     *
     * @param $url
     * @param string $method
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return mixed array | null
     */
    public static function sendJsonRequest($url, $method = 'GET', $data = [], $headers = [], $options = []) : ?array
    {
        return self::sendRequest($url, $method, $data, $headers, $options, Request::ENCODING_JSON);
    }
}