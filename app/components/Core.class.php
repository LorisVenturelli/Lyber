<?php

class Core {

	private static $request = array();

    //===============================================================//

    public static function getRelativeRoot() {
        //Chemin entre CE fichier et la racine.
        $return_path = "/../..";

        $root_path = str_replace("\\", "/", realpath($_SERVER['DOCUMENT_ROOT']));
        $path_here = str_replace("\\", "/", dirname(__FILE__));
        $root_abs = str_replace("\\", "/", realpath($path_here . $return_path));
        $path = "/";
        if (strpos($root_abs, $root_path) !== false)
            $path = "/" . substr($root_abs, strlen($root_path)) . "/";

        $path = preg_replace("@/+@", "/", $path);

        return $path;
    }

    public static function absURL() {
        return self::getProto() . "://" . self::getPreferedDomain() . self::getRelativeRoot();
    }

    public static function parseRequest() {
        $request = substr($_SERVER['REQUEST_URI'], strlen(self::getRelativeRoot()));
        return $request;
    }

    public static function filesystemRoot() {
        return str_replace("\\", "/", realpath($_SERVER['DOCUMENT_ROOT']));
    }

    public static function getRoot(){
        return self::filesystemRoot().self::getRelativeRoot();
    }

    public static function getPreferedDomain() {
        return $_SERVER['HTTP_HOST'];
    }

    public static function getParams($context = "request") {
        if (!isset(self::$request[$context]))
            throw new Exception("Contexte $context inexistant ! (vérifiez variables_order)");

        return self::$request[$context];
    }

    public static function getParam($data, $default = false, $context = "request") {
        if (!isset(self::$request[$context]))
            throw new Exception("Contexte $context inexistant ! (vérifiez variables_order)");

        return isset(self::$request[$context][$data]) ? self::$request[$context][$data] : $default;
    }

    public static function getProto() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http";
    }

    public static function getRequest() {
        $default = 'app/AppIndex.php';

        $request = self::parseRequest();
        $url = parse_url($request);

        $path = !empty($url['path']) ? $url['path'] : "";
        //$path = str_replace(self::getRelativeRoot(), "", $path);
        $path = preg_replace("@/+@", "/", $path);

        if (!file_exists(self::getRoot(). "/" . $path)) {
            //$parts = explode("/", $path);
            $parts[] = $path;
        } else {
            if(is_dir(self::getRoot(). "/" . $path)){
                $path = $path.$default;
            }
            $parts[] = $path;
        }
        
        self::$request['cookie'] = array();
        self::$request['post'] = array();
        self::$request['get'] = array();
        self::$request['request'] = array();

        $matches = array();
        //Protection contre l'injection de paramètres indésirables.
        //gclid est utilisé pour "marquer" les pages par adWords
        /*if ((empty($url['path']) || $url['path'] != "min.php") && empty($_GET['gclid']) && empty($_GET['dev'])) {
            if (!empty($url['query']) || strpos($path, "?") !== false) {
                return self::redirect404(self::parseRequest());
            } else if (!empty($url['query']) || strpos($path, "&") !== false) {
                return self::redirect404(self::parseRequest());
            }
        }*/

        // if (preg_match("@(/([\w-.]+))+.html@", "/" . $path, $matches)) {
        //     if (strpos($path, "/", strpos($path, ".html")) !== false) {
        //         return self::redirect404(self::parseRequest());
        //     }
        //     self::$page = ($matches[2] != "accueil" ? $path : $default);
        // } else if (!empty($parts[0])) {
        //     if ('accueil' != $parts[0]) {
        //         self::$page = $parts[0];
        //     } else {
        //         self::$page = $default;
        //     }
        // } else {
        //     self::$page = $default;
        // }

        self::$request['cookie'] = $_COOKIE;
        self::$request['post'] = $_POST;
        $gets = array();
        for ($i = 1; $i < count($parts); $i = $i + 2) {
            if ($parts[$i] != "")
                $gets[$parts[$i]] = isset($parts[$i + 1]) ? $parts[$i + 1] : "";
        }
        self::$request['get'] = array_merge($_GET, $gets);

        $vars_order = ini_get("variables_order"); // Env, Get, Post, Cookie, Server
        for ($i = 0; $i < strlen($vars_order); $i++) {
            switch ($vars_order[$i]) {
                case "G":
                    self::$request['request'] = array_merge(self::$request['request'], self::$request['get']);
                    break;
                case "P":
                    self::$request['request'] = array_merge(self::$request['request'], self::$request['post']);
                    break;
                case "C":
                    self::$request['request'] = array_merge(self::$request['request'], self::$request['cookie']);
                    break;
            }
        }

        $_REQUEST = $_POST = $_GET = $_COOKIE = null;
    }

    public static function require_data($data){
    
        if(!is_array($data))
            throw new Exception("Erreur du paramétrage require_data !", 1);

        foreach ($data as $var => $critere) {

            if(!is_array($critere))
                $critere = array($critere);

            if(in_array('notempty', $critere) || !empty($var))
            {
                if(in_array('numeric', $critere) && !is_numeric($var))
                    throw new Exception("La variable ".$var." n'est pas un numeric !", 1);
                if(in_array('int', $critere) && !is_int($var))
                    throw new Exception("La variable ".$var." n'est pas un integer !", 1);
                if(in_array('float', $critere) && !is_float($var))
                    throw new Exception("La variable ".$var." n'est pas un float !", 1);
                if(in_array('array', $critere) && !is_array($var))
                    throw new Exception("La variable ".$var." n'est pas un array !", 1);
                if(in_array('string', $critere) && !is_string($var))
                    throw new Exception("La variable ".$var." n'est pas un string !", 1);
                if(in_array('bool', $critere) && !is_bool($var))
                    throw new Exception("La variable ".$var." n'est pas un booleen !", 1);
            }
            else if(in_array('notempty', $critere) && empty($var))
            {
                throw new Exception("La variable ".$var." est vide !", 1);
            }
            else if(in_array('empty', $critere) && !empty($var))
            {
                throw new Exception("La variable ".$var." n'est pas vide !", 1);
            }

        }
            
    }

    /**
     * @param $data
     * @param bool $code
     * @param string $message
     * @return String
     */
    public static function jsonResponse($code = true, $message = "", $data = array()){

        if(!is_array($data)){
            $data = array($data);
        }

        $reponse = ($code) ? "success" : "error";

        return array('reponse'=>$reponse,'message'=>$message,'data'=>$data);

    }

    public static function isJson($string) {
        $ob = json_decode($json);
        return ($ob !== null);
    }

    public static function redirect($url){

        Flight::redirect($url);

    }

    public static function multi_implode($array, $glue) {
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

    public static function array_searchRecursive( $needle, $haystack, $strict=false, $path=array() )
    {
        if( !is_array($haystack) ) {
            return false;
        }
        foreach( $haystack as $key => $val ) {
            if( is_array($val) && $subPath = self::array_searchRecursive($needle, $val, $strict, $path) ) {
                $path = array_merge($path, array($key), $subPath);
                return $path;
            } elseif( (!$strict && $val == $needle) || ($strict && $val === $needle) ) {
                $path[] = $key;
                return $path;
            }
        }
        return false;
    }

}