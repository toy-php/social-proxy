<?php

namespace Vkontakte\Controllers;

use Base\Controller;
use Base\SessionStorage;
use Http\Response;
use Http\Uri;
use Psr\Http\Message\ServerRequestInterface;

class MainController extends Controller
{

    public function authAction(ServerRequestInterface $request,
                               Response $response,
                               \Proxy $proxy)
    {
        $clientId ='6044494';
        $display ='page';
        $redirectUri = $request->getUri()->withPath('/vk/callback');
        $scope = 'email';
        $responseType = 'code';
        $oauthVersion = '5.64';

        /** @var SessionStorage $session */
        $session = $proxy['session'];
        $code = $session->get('code');
        if(!empty($code)){
            $url = $redirectUri->withQuery(http_build_query([
                    'code' => $code
                ]));
            return $response->withHeader('Location', $url);
        }
        $url = (new Uri('https://oauth.vk.com/authorize'))
            ->withQuery(http_build_query([
                'client_id' => $clientId,
                'display' => $display,
                'redirect_uri' => $redirectUri,
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
        return $response;
    }
}