<?php

namespace Vkontakte\Controllers;

use Base\Controller;
use Base\SessionStorage;
use Http\Response;
use Http\Uri;
use Psr\Http\Message\ServerRequestInterface;

class MainController extends Controller
{

    protected $clientId ='6044494';

    public function authAction(ServerRequestInterface $request,
                               Response $response,
                               \Proxy $proxy)
    {
        $display ='page';
        $redirectUri = $request->getUri()->withPath('/vk/callback');
        $scope = 'email';
        $responseType = 'code';
        $oauthVersion = '5.64';

        /** @var SessionStorage $session */
        $session = $proxy['session'];
        $query = $request->getQueryParams();
        $session->set('sessionRedirect', isset($query['redirect']) ?: '');
        $url = (new Uri('https://oauth.vk.com/authorize'))
            ->withQuery(http_build_query([
                'client_id' => $this->clientId,
                'display' => $display,
                'redirect_uri' => $redirectUri->__toString(),
                'scope' => $scope,
                'response_type' => $responseType,
                'v' => $oauthVersion
            ]));
        return $response->withHeader('Location', $url);
    }

    public function callbackAction(ServerRequestInterface $request,
                               Response $response,
                               \Proxy $proxy)
    {
        $clientSecret = '7LONJuDHTdVIbBz5JgwZ';
        $redirectUri = $request->getUri()->withQuery('');
        $query = $request->getQueryParams();
        $code = isset($query['code']) ?: '';

        /** @var SessionStorage $session */
        $session = $proxy['session'];
        $token = $session->get('token');
        $sessionRedirect =  new Uri($session->get('sessionRedirect'));
        if(!empty($token)){
            $url = $sessionRedirect->withQuery(http_build_query([
                'token' => $token
            ]));
            $session->remove('token');
            return $response->withHeader('Location', $url);
        }
        $url = (new Uri('https://oauth.vk.com/access_token'))
            ->withQuery(http_build_query([
                'client_id' => $this->clientId,
                'code' => $code,
                'redirect_uri' => $redirectUri->__toString(),
                'client_secret' => $clientSecret
            ]));
        return $response;//->withHeader('Location', $url);
    }
}