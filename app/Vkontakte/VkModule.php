<?php

namespace Vkontakte;

use Base\Config;
use Base\Session;
use Core\Module;
use Core\Toy;
use Vkontakte\Controllers\MainController;

class VkModule implements Module
{
    /**
     * Регистрация модуля в ядре
     * @param Toy $core
     * @return void
     */
    public function register(Toy $core)
    {
        $core->addRouts([
            '/vk' => [
                'GET/auth' => MainController::run('auth'),
                'GET/callback' => MainController::run('callback'),
                'GET/user_info' => MainController::run('getUserInfo'),
            ]
        ]);
        $core['session'] = new Session();
        $core['config'] = new Config(include __DIR__ . '/config/config.php');
        $memcached = new \Memcached();
        $memcached->addServer('localhost', 11211);
        $core['tokenStorage'] = $memcached;
    }
}