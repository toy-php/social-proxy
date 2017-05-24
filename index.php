<?php

ini_set("default_charset", "utf-8");

define('BASE_PATH', __DIR__);

include  'vendor/autoload.php';

Proxy::mode(Proxy::DEV);

$app = new Proxy();

$app->registerModule(new \Vkontakte\VkModule());

$app->run();

