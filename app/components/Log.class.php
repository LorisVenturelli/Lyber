<?php

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

            self::$instance->$method("'".self::multi_implode($parameters, "-")."'");

        }

        private static function multi_implode($array, $glue) {
            $ret = '';

            foreach ($array as $item) {
                if (is_array($item)) {
                    $ret .= self::multi_implode($item, $glue) . $glue;
                } else {
                    $ret .= $item . $glue;
                }
            }

            $ret = substr($ret, 0, 0-strlen($glue));

            return $ret;
        }

	}