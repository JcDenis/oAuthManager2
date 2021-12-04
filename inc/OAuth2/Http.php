<?php
/**
 * @brief oAuthManager2, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Jean-Christian Denis and contributors
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace OAuth2;

use CurlHandle;

class Http
{
    /** @var CurlHandle [description] */
    protected $handler;

    /** @var array Response headers */
    protected $headers = [];

    /** @var array Curl options */
    protected $curl_opt = [
        CURLOPT_USERAGENT      => 'Dotclear - OAuth20Client',
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY         => false,
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    ];

    public function __construct()
    {
        if (!($handler = curl_init())) {
            throw new Exception('Failed to initialize curl handler');
        }
        $this->handler = $handler;
    }

    public function __destruct()
    {
        if ($this->handler instanceof CurlHandle) {
            curl_close($this->handler);
        }
    }

    public function request(string $method, string $url, array $parameters = [], array $headers = []): array
    {
        $this->headers = [];
        $method        = strtoupper($method);
        $options       = array_replace($this->curl_opt, [
            CURLOPT_URL            => $url . '?' . http_build_query($parameters, '', '&'),
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $this->linearizeHeaders($headers),
            CURLOPT_HEADERFUNCTION => [$this, 'parseHeaders'],
        ]);

        switch ($method) {
            case 'HEAD':
                $options[CURLOPT_NOBODY] = true;

                break;

            case 'GET':
                $options[CURLOPT_HTTPGET] = true;

                break;

            case 'POST':
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_URL]  = $url;
                if (!empty($parameters)) {
                    $options[CURLOPT_POSTFIELDS] = $parameters;
                }

                break;

            case 'DELETE':
            case 'PATCH':
            case 'OPTIONS':
            case 'PUT':
            default:
                $options[CURLOPT_CUSTOMREQUEST] = $method;
                if (!empty($parameters)) {
                    $options[CURLOPT_POSTFIELDS] = $parameters;
                }
        }

        curl_setopt_array($this->handler, $options);
        $content = curl_exec($this->handler);

        // see: https://www.php.net/manual/en/function.curl-getinfo
        $rsp = [
            'status'  => curl_getinfo($this->handler, CURLINFO_HTTP_CODE),
            'header'  => $this->headers,
            'error'   => curl_error($this->handler),
            'content' => $content,
        ];

        curl_reset($this->handler);

        return $rsp;
    }

    /**
     * Parse headers
     * 
     * @param  resource $r          CurlHandle resourse
     * @param  string   $header     Headers
     * 
     * @return integer              Lengh
     */
    protected function parseHeaders($r, string $header): int
    {
        $parts = explode(':', $header, 2);
        if (count($parts) == 2) {
            [$name, $value]               = $parts;
            $this->headers[trim($name)][] = trim($value);
        }

        return mb_strlen($header, '8bit');
    }

    protected function linearizeHeaders(array $headers): array
    {
        $res = [];
        foreach ($headers as $key => $values) {
            if (!is_array($values)) {
                $res[] = sprintf('%s: %s', $key, $values);
            } else {
                foreach ($values as $value) {
                    $res[] = sprintf('%s: %s', $key, $value);
                }
            }
        }

        return $res;
    }
}
