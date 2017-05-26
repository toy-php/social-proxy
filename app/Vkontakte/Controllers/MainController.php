<?php

namespace Vkontakte\Controllers;

use Base\Controller;
use Http\Response;
use Http\Uri;
use Psr\Http\Message\ServerRequestInterface;

class MainController extends Controller
{

    /**
     * Авторизация в соц. сети
     * @param ServerRequestInterface $request
     * @param Response $response
     * @return Response
     */
    public function authAction(ServerRequestInterface $request,
                               Response $response)
    {
        /*
            Адрес, на который будет передан code (домен указанного адреса должен соответствовать
            основному домену в настройках приложения и перечисленным значениям
            в списке доверенных redirect uri — адреса сравниваются вплоть до path-части).
         */
        $redirectUri = $request->getUri()
            ->withPath('/vk/callback')
            ->withQuery('');

        $query = $request->getQueryParams();

        // Адрес переадресации при успешной авторизации
        $this->session->set('sessionRedirect', isset($query['redirect']) ? $query['redirect'] : '');

        $url = (new Uri('https://oauth.vk.com/authorize'))
            ->withQuery(http_build_query([
                'client_id' => $this->config['vk']->get('client_id'),
                'display' => $this->config['vk']->get('display'),
                'redirect_uri' => $redirectUri->__toString(),
                'scope' => $this->config['vk']->get('scope'),
                'response_type' => $this->config['vk']->get('response_type'),
                'v' => $this->config['vk']->get('oauth_version'),
            ]));
        return $response->withHeader('Location', $url);
    }

    /**
     * Получение токена
     * @param ServerRequestInterface $request
     * @param Response $response
     * @return Response
     */
    public function callbackAction(ServerRequestInterface $request,
                                   Response $response)
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

        $url = (new Uri('https://oauth.vk.com/access_token'))
            ->withQuery(http_build_query([
                'client_id' => $this->config['vk']->get('client_id'),
                'code' => $code,
                'redirect_uri' => $redirectUri->__toString(),
                'client_secret' => $this->config['vk']->get('client_secret')
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
        if(isset($userInfo->error)){
            $response->getBody()->write($content);
            return $response->withHeader('Content-Type', 'application/json');
        }
        $userInfo->access_token = 'VK-' . $userInfo->access_token;
        $sessionRedirectUrl = new Uri($this->session->get('sessionRedirect'));
        if (!isset($userInfo->error)) {
            $this->tokenStorage->set($userInfo->access_token, $userInfo, $userInfo->expires_in);
            $sessionRedirectUrl = $sessionRedirectUrl->withQuery(http_build_query([
                'token' => $userInfo->access_token
            ]));
        }
        return $response->withHeader('Location', $sessionRedirectUrl->__toString());
    }

}