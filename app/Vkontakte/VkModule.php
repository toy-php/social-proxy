<?php

namespace Vkontakte;

use Base\Config;
use Base\Module;
use Base\Session;
use Core\Toy;
use Vkontakte\Controllers\MainController;

class VkModule extends Module
{
    /**
     * Регистрация модуля в ядре
     * @param Toy $core
     * @return void
     */
    public function register(Toy $core)
    {
        parent::register($core);
        $core->addRouts([
            '/vk' => [
                'GET/auth' => MainController::run('auth'),
                'GET/callback' => MainController::run('callback')
            ]
        ]);
        $core['config'] = new Config(include __DIR__ . '/config/config.php');
    }
}