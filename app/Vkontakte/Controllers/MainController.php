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
                'client_secret' => $this->config->get('7LONJuDHTdVIbBz5JgwZ')
            ]));
        list($content) = $this->getContent($url->__toString());
        if (!$content) {
            $response->getBody()->write(json_encode([
                'error' => 'invalid_response',
                'error_description' => 'response error from the authorization server'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $resultObj = json_decode($content);
        $resultObj->redirect = $sessionRedirect->__toString();
        if(!isset($resultObj->error)){
            $this->session->set('token', $resultObj->access_token);
        }
        $response->getBody()->write(json_encode($resultObj));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Проверка токена на валидность
     * @param ServerRequestInterface $request
     * @param Response $response
     * @return Response
     */
    public function validateAction(ServerRequestInterface $request,
                                   Response $response)
    {
        $query = $request->getQueryParams();
        $token = isset($query['token']) ? $query['token'] : '';
        $result = $this->session->get('token') === $token;
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}