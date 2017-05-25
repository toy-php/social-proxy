<?php

namespace Base;

class Session implements SessionStorage
{

    protected $session_started = false;
    protected static $session_vars = [];

    public function __construct($auto_start = true)
    {
        if($auto_start){
            $this->start();
            $this->stop();
        }
    }

    /**
     * Получение идентификатора сессии
     */
    public function id()
    {
        $this->start();
        $sid = session_id();
        $this->stop();
        return $sid;
    }

    /**
     * Установка идентификатора сессии
     */
    public function setId($id)
    {
        $this->start();
        session_id($id);
        $this->stop();
    }

    /**
     * @inheritdoc
     */
    public function set($name, $value)
    {
        $this->start();
        static::$session_vars[$name] = $value;
        $this->stop();
    }

    /**
     * @inheritdoc
     */
    public function get($name)
    {
        return isset(static::$session_vars[$name]) ? static::$session_vars[$name] : null;
    }

    /**
     * @inheritdoc
     */
    public function has($name)
    {
        return isset(static::$session_vars[$name]);
    }

    /**
     * @inheritdoc
     */
    public function remove($name)
    {
        $this->start();
        if (isset(static::$session_vars[$name])) unset(static::$session_vars[$name]);
        $this->stop();
    }

    /**
     * Старт сессии
     */
    public function start()
    {
        if ($this->session_started == false) {
            session_start();
            $this->session_started = true;
            static::$session_vars = $_SESSION;
        }

    }

    /**
     * Остановка сессии
     */
    public function stop()
    {
        $_SESSION = static::$session_vars;
        session_write_close();
        $this->session_started = false;
    }

    /**
     * Удаление сессии
     */
    public function destroy()
    {
        $this->start();
        session_destroy();
        $this->stop();
    }

}
