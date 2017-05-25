<?php

namespace Base;

interface ConfigInterface
{

    /**
     * Получить значение ключа
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function get($name, $default = null);

    /**
     * Проверить наличие ключа
     * @param $name
     * @return boolean
     */
    public function has($name);
}