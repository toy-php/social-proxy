<?php

namespace Vkontakte;

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
                'GET/auth(.*?)' => MainController::run('auth'),
                'GET/callback(.*?)' => MainController::run('callback'),
            ]
        ]);
    }
}