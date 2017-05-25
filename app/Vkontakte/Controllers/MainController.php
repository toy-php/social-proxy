<?php

namespace Vkontakte\Controllers;

use Base\Controller;
use Base\SessionStorage;
use Http\Response;
use Http\Uri;
use Psr\Http\Message\ServerRequestInterface;

class MainController extends Controller
{

    /**
     * Идентификатор Вашего приложения.
     * @var string
     */
    protected $clientId = '6044494';

    /**
     * Авторизация в соц. сети
     * @param ServerRequestInterface $request
     * @param Response $response
     * @param \Proxy $proxy
     * @return Response
     */
    public function authAction(ServerRequestInterface $request,
                               Response $response,
                               \Proxy $proxy)
    {
        /*
            Указывает тип отображения страницы авторизации. Поддерживаются следующие варианты:
                page — форма авторизации в отдельном окне;
                popup — всплывающее окно;
                mobile — авторизация для мобильных устройств (без использования Javascript)
            Если пользователь авторизуется с мобильного устройства, будет использован тип mobile.
         */
        $display = 'page';

        /*
            Адрес, на который будет передан code (домен указанного адреса должен соответствовать
            основному домену в настройках приложения и перечисленным значениям
            в списке доверенных redirect uri — адреса сравниваются вплоть до path-части).
         */
        $redirectUri = $request->getUri()
            ->withPath('/vk/callback')
            ->withQuery('');
        /*
            Битовая маска настроек доступа приложения,
            которые необходимо проверить при авторизации пользователя и запросить отсутствующие.
            смотреть: https://vk.com/dev/permissions
         */
        $scope = 'email';
        /*
             Тип ответа, который Вы хотите получить.
         */
        $responseType = 'code';

        /*
            Версия API, которую Вы используете.
            смотреть: https://vk.com/dev/versions
         */
        $oauthVersion = '5.64';

        $query = $request->getQueryParams();

        // Адрес переадресации при успешной авторизации
        $this->session->set('sessionRedirect', isset($query['redirect']) ? $query['redirect'] : '');

        // Адрес переадресации при возникновении ошибки
        $this->session->set('sessionError', isset($query['error']) ? $query['error'] : '');

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

    /**
     * Получение токена
     * @param ServerRequestInterface $request
     * @param Response $response
     * @param \Proxy $proxy
     * @return Response
     */
    public function callbackAction(ServerRequestInterface $request,
                                   Response $response,
                                   \Proxy $proxy)
    {
        /*
            Защищенный ключ Вашего приложения (указан в настройках приложения)
         */
        $clientSecret = '7LONJuDHTdVIbBz5JgwZ';

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
        $sessionErrorRedirect = new Uri($this->session->get('sessionError'));

        $url = (new Uri('https://oauth.vk.com/access_token'))
            ->withQuery(http_build_query([
                'client_id' => $this->clientId,
                'code' => $code,
                'redirect_uri' => $redirectUri->__toString(),
                'client_secret' => $clientSecret
            ]));
        list($content) = $this->getContent($url->__toString());
        if (!$content) {
            return $response->withHeader('Location', $sessionErrorRedirect->__toString());
        }
        $resultObj = json_decode($content);
        $resultObj->redirect = $sessionRedirect->__toString();
        $this->session->set('token', $resultObj->access_token);
        $response->getBody()->write(json_encode($resultObj));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function validateAction(ServerRequestInterface $request,
                                   Response $response,
                                   \Proxy $proxy)
    {
        $query = $request->getQueryParams();
        $token = isset($query['token']) ? $query['token'] : '';
        $result = $this->session->get('token') === $token;
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}