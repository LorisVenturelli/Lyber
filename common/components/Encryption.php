<?php

namespace Lyber\Common\Components;

use PHPEncryptData\Simple;

class Encryption {

    protected static $instance;

    private static function init(){

        if(is_null(self::$instance)){

            $encryptionKey = Config::get('encryption','key');
            $macKey = Config::get('encryption','mac');

            self::$instance = new Simple($encryptionKey, $macKey);

        }

    }

    public static function encrypt($string = ""){

        self::init();

        return self::$instance->encrypt($string);

    }
    public static function decrypt($string = ""){

        self::init();

        return self::$instance->decrypt($string);

    }

    public static function compare($string, $crypted){

        self::init();

        return (self::encrypt($string) == $crypted);

    }

}