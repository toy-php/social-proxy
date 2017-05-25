<?php

ini_set("default_charset", "utf-8");

define('BASE_PATH', __DIR__);

include  'vendor/autoload.php';

Proxy::mode(Proxy::DEV);

$app = new Proxy();

$app['session'] = new \Base\Session();

$memcached = new \Memcached();
$memcached->addServer('localhost', 11211);
$app['tokenStorage'] = $memcached;

$app->addRouts([
    'GET/user_info' => \Base\Controller::run('getUserInfo')
]);

$app->registerModule(new \Vkontakte\VkModule());

$app->registerModule(new \Facebook\FbModule());

$app->run();

