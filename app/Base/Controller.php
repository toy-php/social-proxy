<?php

namespace Base;

use Container\Container;
use Http\Response;
use Psr\Http\Message\ServerRequestInterface;

class Controller
{

    protected $session;
    protected $config;
    protected $tokenStorage;

    public function __construct(SessionStorage $session, Container $config, \Memcached $tokenStorage)
    {
        $this->session = $session;
        $this->config = $config;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Проверка токена на валидность, и получение информации о пользователе
     * @param ServerRequestInterface $request
     * @param Response $response
     * @return Response
     */
    public function getUserInfoAction(ServerRequestInterface $request,
                                      Response $response)
    {
        $query = $request->getQueryParams();
        $token = isset($query['token']) ? $query['token'] : '';
        $userInfo = $this->tokenStorage->get($token);
        if (!empty($userInfo)
            and isset($userInfo->access_token)
            and $userInfo->access_token === $token) {
            $response->getBody()->write(json_encode($userInfo));
        } else {
            $response->getBody()->write(json_encode([
                'error' => 'invalid_token',
                'error_description' => 'invalid token'
            ]));
        }
        return $response->withHeader('Content-Type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*');
    }

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

    public function getContent($url, array $config = [])
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
        $curl = curl_init($url);
        curl_setopt_array($curl, $config + $defaultConfig);
        $contents = curl_exec($curl);
        curl_close($curl);
        return [$contents, $this->parseHeaders($headers)];
    }

    public static function run($action)
    {
        return function ($request, $response, $app) use ($action) {
            $class = new static($app['session'], $app['config'], $app['tokenStorage']);
            $method = $action . 'Action';
            return $class->$method($request, $response);

        };
    }
}