<?php

namespace Lyber\Common\Components;

class Autoloader {

    static public function loader($className) {
        $namespace = explode('\\', strtolower($className));
        if($namespace[0] != "lyber"){
            return false;
        }
        else {
            unset($namespace[0]);
        }

        $file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, realpath(dirname(__FILE__))."/../../" . implode('/', $namespace).".php");

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