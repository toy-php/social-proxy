<?php

namespace Facebook;

use Base\Config;

use Core\Module;
use Core\Toy;
use Facebook\Controllers\MainController;

class FbModule implements Module
{

    /**
     * Регистрация модуля в ядре
     * @param Toy $core
     * @return void
     */
    public function register(Toy $core)
    {
        $core->addRouts([
            '/fb' => [
                'GET/auth' => MainController::run('auth'),
                'GET/callback' => MainController::run('callback')
            ]
        ]);
        $core['config']['fb'] = new Config(include __DIR__ . '/config/config.php');
    }
}