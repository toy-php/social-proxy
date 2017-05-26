<?php

namespace Odnoklassniki\Controllers;

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
            ->withPath('/ok/callback')
            ->withQuery('');

        $query = $request->getQueryParams();

        // Адрес переадресации при успешной авторизации
        $this->session->set('sessionRedirect', isset($query['redirect']) ? $query['redirect'] : '');

        $url = (new Uri('https://connect.ok.ru/oauth/authorize'))
            ->withQuery(http_build_query([
                'client_id' => $this->config['ok']->get('client_id'),
                'layout' => $this->config['ok']->get('layout'),
                'redirect_uri' => $redirectUri->__toString(),
                'scope' => $this->config['ok']->get('scope'),
                'response_type' => $this->config['ok']->get('response_type')
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

        $url = (new Uri('https://api.ok.ru/oauth/token.do'));
        list($content) = $this->getContent($url->__toString(), [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id' => $this->config['ok']->get('client_id'),
                'code' => $code,
                'redirect_uri' => $redirectUri->__toString(),
                'client_secret' => $this->config['ok']->get('client_secret'),
                'grant_type' => 'authorization_code'
            ]),
        ]);
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
        $userInfo->access_token = 'OK-' . $userInfo->access_token;
        $this->tokenStorage->set(
            $userInfo->access_token,
            $userInfo,
            $this->getExpirationTime($userInfo->expires_in)
        );
        $sessionRedirectUrl = new Uri($this->session->get('sessionRedirect'));
        $sessionRedirectUrl = $sessionRedirectUrl->withQuery(http_build_query([
            'token' => $userInfo->access_token
        ]));

        return $response->withHeader('Location', $sessionRedirectUrl->__toString());
    }

}