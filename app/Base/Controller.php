<?php

namespace Base;

class Controller
{

    public function parseHeaders($headersString)
    {
        $allowedHeaders = "!^(server:|content-type:|last-modified|access-control-allow-origin|Content-Length:|Accept-Ranges:|Date:|Via:|Connection:|X-|age|cache-control|vary)!i";
        $headers = array();
        $header_text = substr($headersString, 0, strpos($headersString, "\r\n\r\n"));
        foreach (explode("\r\n", $header_text) as $i => $line) {
            if (preg_match($allowedHeaders, $line)) {
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }
        return $headers;
    }

    public function proxyUrl($proxyUrl, array $config = [])
    {
        $headers = '';
        $defaultConfig = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HEADERFUNCTION => function ($ch, $header_line) use (&$headers) {
                $headers .= $header_line;
                return strlen($header_line);
            }
        ];
        $curl = curl_init($proxyUrl);
        curl_setopt_array($curl, $config + $defaultConfig);
        $contents = curl_exec($curl);
        curl_close($curl);
        return [$contents, $this->parseHeaders($headers)];
    }


    public static function run($action)
    {
        return function ($request, $response, $app) use ($action) {
            $class = new static();
            $method = $action . 'Action';
            return $class->$method($request, $response, $app);

        };
    }
}