<?php

namespace Facebook\Controllers;

use Base\Controller;
use Http\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MainController extends Controller
{
    /**
     * Авторизация в соц. сети
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function authAction(ServerRequestInterface $request,
                               ResponseInterface $response)
    {
        /*
            Адрес, на который будет передан code (домен указанного адреса должен соответствовать
            основному домену в настройках приложения и перечисленным значениям
            в списке доверенных redirect uri — адреса сравниваются вплоть до path-части).
         */
        $redirectUri = $request->getUri()
            ->withPath('/fb/callback')
            ->withQuery('');

        $query = $request->getQueryParams();

        // Адрес переадресации при успешной авторизации
        $this->session->set('sessionRedirect', isset($query['redirect']) ? $query['redirect'] : '');

        $version = $this->config['fb']->get('oauth_version');

        $url = (new Uri('https://www.facebook.com/' . $version . '/dialog/oauth'))
            ->withQuery(http_build_query([
                'client_id' => $this->config['fb']->get('client_id'),
                'display' => $this->config['fb']->get('display'),
                'redirect_uri' => $redirectUri->__toString(),
                'scope' => $this->config['fb']->get('scope'),
                'response_type' => $this->config['fb']->get('response_type')
            ]));
        return $response->withHeader('Location', $url);
    }

    /**
     * Получение токена
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function callbackAction(ServerRequestInterface $request,
                                   ResponseInterface $response)
    {
        /*
            URL, который использовался при получении code на первом этапе авторизации.
            Должен быть аналогичен переданному при авторизации.
         */
        $redirectUri = $request->getUri()->withQuery('');

        $query = $request->getQueryParams();

        /*
            Временный код, полученный после прохождения авторизации.
         */
        $code = isset($query['code']) ? $query['code'] : '';

        $version = $this->config['fb']->get('oauth_version');

        $url = (new Uri('https://graph.facebook.com/' . $version . '/oauth/access_token'))
            ->withQuery(http_build_query([
                'client_id' => $this->config['fb']->get('client_id'),
                'code' => $code,
                'redirect_uri' => $redirectUri->__toString(),
                'client_secret' => $this->config['fb']->get('client_secret')
            ]));
        list($content) = $this->getContent($url->__toString());
        if (!$content) {
            $response->getBody()->write(json_encode([
                'error' => 'invalid_response',
                'error_description' => 'response error from the authorization server'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $userInfo = json_decode($content);
        if (isset($userInfo->error)) {
            $response->getBody()->write($content);
            return $response->withHeader('Content-Type', 'application/json');
        }

        $userInfo->data = $this->getUserInfoData($userInfo->access_token);
        $userInfo->access_token = 'FB-' . $userInfo->access_token;
        $this->tokenStorage->set(
            $userInfo->access_token,
                $userInfo,
                $this->getExpirationTime($userInfo->expires_in)
        );

        $sessionRedirectUrl = new Uri($this->session->get('sessionRedirect'));
        $sessionRedirectUrl = $sessionRedirectUrl->withQuery(http_build_query([
            'token' => $userInfo->access_token
        ]));
var_dump($userInfo);
        return $response;//->withHeader('Location', $sessionRedirectUrl->__toString());
    }

    protected function getUserInfoData($inputToken)
    {
        $accessToken = $this->getAccessToken();
        $version = $this->config['fb']->get('oauth_version');
        $url = (new Uri('https://graph.facebook.com/' . $version . '/me'))
            ->withQuery(http_build_query([
                'scope' => $this->config['fb']->get('scope'),
                'access_token' => $inputToken
            ]));
        list($content) = $this->getContent($url->__toString());
        $userInfoData = json_decode($content);
        return $userInfoData;
    }

    protected function getAccessToken()
    {
        $version = $this->config['fb']->get('oauth_version');
        $url = (new Uri('https://graph.facebook.com/' . $version . '/oauth/access_token'))
            ->withQuery(http_build_query([
                'client_id' => $this->config['fb']->get('client_id'),
                'client_secret' => $this->config['fb']->get('client_secret'),
                'grant_type' => 'client_credentials'
            ]));
        list($content) = $this->getContent($url->__toString());
        $accessTokenData = json_decode($content);
        return $accessTokenData->access_token;
    }

}