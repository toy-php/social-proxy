<?php

class Proxy extends \Core\Toy
{

    const PROD = 1;

    const DEV = 2;

    /**
     * Режим работы приложения
     * @var int
     */
    static public $mode = self::PROD;

    public static function mode($mode)
    {
        static::$mode = $mode;
        if(static::$mode == static::DEV){
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
        }else{
            ini_set("display_errors", 0);
        }
    }

    public function run()
    {
        try{
            parent::run();
        }catch (Throwable $exception){
            if(static::$mode == static::DEV){
                throw $exception;
            }
        }

    }

}