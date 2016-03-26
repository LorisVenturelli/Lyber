<?php

namespace Lyber\Common\Components;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log {

    protected static $instance;

    private static function init(){

        if(is_null(self::$instance)) {

            $path = Core::getRoot()."logs/";
            $logfile = date('Y-m-d').".txt";

            if(!is_dir($path))
                mkdir($path, 0777);

            if(!file_exists($path.$logfile)){

                $fh  = fopen($path.$logfile, 'a+') or die("Fatal Error !");
                fwrite($fh, "");
                fclose($fh);

            }

            self::$instance = new Logger('Lyber');
            self::$instance->pushHandler(new StreamHandler($path.$logfile, Logger::WARNING));

        }

    }

    public static function __callStatic($method, $parameters) {

        self::init();

        self::$instance->$method("'".Core::multi_implode($parameters, "-")."'");

    }



}