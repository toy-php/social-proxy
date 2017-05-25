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
                'client_id' => $this->config->get('client_id'),
                'display' => $this->config->get('display'),
                'redirect_uri' => $redirectUri->__toString(),
                'scope' => $this->config->get('scope'),
                'response_type' => $this->config->get('response_type'),
                'v' => $this->config->get('oauth_version'),
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

        $sessionRedirect = new Uri($this->session->get('sessionRedirect'));

        $url = (new Uri('https://oauth.vk.com/access_token'))
            ->withQuery(http_build_query([
                'client_id' => $this->config->get('client_id'),
                'code' => $code,
                'redirect_uri' => $redirectUri->__toString(),
                'client_secret' => $this->config->get('client_secret')
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
        $userInfo->redirect = $sessionRedirect->__toString();
        $url = new Uri($sessionRedirect->__toString());
        if (!isset($userInfo->error)) {
            $this->session->set('userInfo', $userInfo);
            $url = $url->withQuery(http_build_query([
                'token' => $userInfo->access_token
            ]));
        }
        return $response->withHeader('Location', $url);
    }

    /**
     * Проверка токена на валидность
     * @param ServerRequestInterface $request
     * @param Response $response
     * @return Response
     */
    public function getUserInfoAction(ServerRequestInterface $request,
                                      Response $response)
    {
        $query = $request->getQueryParams();
        $token = isset($query['token']) ? $query['token'] : '';
        $userInfo = $this->session->get('userInfo');
        if (isset($userInfo->access_token) and $userInfo->access_token === $token) {
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
}