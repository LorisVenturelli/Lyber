<?php

namespace Lyber\Common\Components;

class Autoloader {

    static public function loader($className) {
        $base = realpath(dirname(__FILE__));

        $namespace = explode('\\', strtolower($className));
        if($namespace[0] != "lyber"){
            return false;
        }
        else {
            unset($namespace[0]);
        }


        $file = $base."/../../";
        if($namespace[1] == "common"){
            $file .= implode('/', $namespace).".php";
        }
        else if($namespace[1] == "apps"){
            $file .= "apps/".$namespace[2]."/".$namespace[3]."/controller/".$namespace[4].".php";
        }

        $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);

        if (file_exists($file)) {
            require_once($file);
            if (class_exists($className)) {
                return TRUE;
            }
        }
        return FALSE;
    }

}

spl_autoload_register('Lyber\Common\Components\Autoloader::loader');