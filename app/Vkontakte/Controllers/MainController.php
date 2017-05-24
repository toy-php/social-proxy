<?php

namespace Vkontakte\Controllers;

use Base\Controller;
use Http\Response;
use Psr\Http\Message\ServerRequestInterface;

class MainController extends Controller
{

    public function authAction(ServerRequestInterface $request,
                               Response $response,
                               \Proxy $proxy)
    {
        $base = 'https://oauth.vk.com';
        $path = str_replace('/vk', '', $request->getUri()->getPath());
        $query = $request->getUri()->getQuery();
        $proxyUrl = $base . $path . '?' . $query;
        list($content, $headers) = $this->proxyUrl($proxyUrl);
        if ($content === false) {
            $response->getBody()->write('Proxy failed to get contents at' . $proxyUrl);
            return $response->withStatus(503);
        }
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        $response->getBody()->write($content);
        return $response;
    }
}