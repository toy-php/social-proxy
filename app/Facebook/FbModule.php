<?php

namespace Facebook;

use Base\Config;
use Base\Module;
use Core\Toy;
use Facebook\Controllers\MainController;

class FbModule extends Module
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
            '/fb' => [
                'GET/auth' => MainController::run('auth'),
                'GET/callback' => MainController::run('callback')
            ]
        ]);
        $core['config'] = new Config(include __DIR__ . '/config/config.php');
    }
}