<?php

class Config {
    private static $conf = array();
    
    public static function load($file){
        if(!is_file($file))
            throw new Exception("Fichier de config $file, introuvable");
        
        $conf = parse_ini_file($file, true);
        if(!$conf)
            throw new Exception("Erreur de syntaxe dans le fichier $file");
        
        self::$conf = array_replace_recursive(self::$conf, $conf);
    }
    
    public static function get($section, $var = null){
        if(isset($var))
            return isset(self::$conf[$section][$var])?self::$conf[$section][$var]:null;
        else
            return isset(self::$conf[$section])?self::$conf[$section]:null;
    }
    
    public static function set($var, $value){
        if(is_array($var)){
            $section = $var[0];
            $var_name = $var[1];
        } else {
            $var_name = $var;
        }
        
        if(isset($section))
            self::$conf[$section][$var_name] = $value;
        else self::$conf[$var_name] = $value;
        
        return true;
    }
}