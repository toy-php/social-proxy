<?php

namespace Base;

use Core\Module as ModuleInterface;
use Core\Toy;

class Module implements ModuleInterface
{

    /**
     * Регистрация модуля в ядре
     * @param Toy $core
     * @return void
     */
    public function register(Toy $core)
    {
        $core['session'] = new Session();
        $memcached = new \Memcached();
        $memcached->addServer('localhost', 11211);
        $core['tokenStorage'] = $memcached;
        $core->addRouts([
                'GET/user_info' => Controller::run('getUserInfo')
        ]);
    }
}