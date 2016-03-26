<?php

    use MatthiasMullie\Minify;

    class Assets {

        private static $js = array();
        private static $css = array();

        public static function addJsFile($pathFile)
        {
            if(!file_exists(Core::getRoot().$pathFile))
                throw new Exception('Fichier inexistant ! : '.$pathFile);

            if(!in_array(pathinfo(Core::getRoot().$pathFile, PATHINFO_EXTENSION), array('js')))
                throw new Exception('This file is not a JS file ! : '.$pathFile);

            self::$js[] = $pathFile;
        }

        public static function addCssFile($pathFile)
        {
            if(!file_exists(Core::getRoot().$pathFile))
                throw new Exception('Fichier inexistant ! : '.$pathFile);

            if(!in_array(pathinfo(Core::getRoot().$pathFile, PATHINFO_EXTENSION), array('css')))
                throw new Exception('This file is not a CSS file ! : '.$pathFile);

            self::$css[] = $pathFile;
        }

        public static function getJsList()
        {
            return self::$js;
        }

        public static function getCssList()
        {
            return self::$css;
        }

        public static function saveAll($path)
        {
            self::saveCss($path);
            self::saveJs($path);
        }

        public static function saveJs($path)
        {
            $min = new Minify\JS();

            foreach(self::$js as $asset)
                $min->add(Core::getRoot().$asset);

            $min->minify($path);
        }

        public static function saveCss($path)
        {
            $min = new Minify\CSS();

            foreach(self::$css as $asset)
                $min->add(Core::getRoot().$asset);

            $min->minify($path);
        }
    }