<?php

namespace Base;

class Config implements ConfigInterface
{

    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Получить значение ключа
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->has($name) ? $this->config[$name] : $default;
    }

    /**
     * Проверить наличие ключа
     * @param $name
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->config[$name]);
    }
}