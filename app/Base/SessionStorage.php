<?php

namespace Base;

interface SessionStorage
{

    /**
     * Установка значения переменной сессии
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set($name, $value);

    /**
     * Получение значения переменной сессии
     * @param string $name
     * @return mixed
     */
    public function get($name);

    /**
     * Наличие переменной сессии
     * @param string $name
     * @return boolean
     */
    public function has($name);

    /**
     * Удаление переменной сессии
     * @param string $name
     * @return void
     */
    public function remove($name);
}