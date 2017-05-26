<?php

namespace Odnoklassniki;

use Base\Config;
use Core\Module;
use Core\Toy;
use Odnoklassniki\Controllers\MainController;

class OkModule implements Module
{

    /**
     * Регистрация модуля в ядре
     * @param Toy $core
     * @return void
     */
    public function register(Toy $core)
    {
        $core->addRouts([
            '/ok' => [
                'GET/auth' => MainController::run('auth'),
                'GET/callback' => MainController::run('callback')
            ]
        ]);
        $core['config']['ok'] = new Config(include __DIR__ . '/config/config.php');
    }
}