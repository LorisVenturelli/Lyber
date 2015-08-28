<?php

    class FrontendEngine
    {

        public static function start($module, $function, $params)
        {
            Core::getRequest();

            // Routes personnalis�s
            $mod_config = parse_ini_file('modules/frontend/FrontendConfig.ini', true);

            // Module par d�fault
            $module = (empty($module)) ? Config::get('global','frontend_home_module') : $module;

            // Fonction par d�fault
            $function = (empty($function)) ? 'show' : $function;


            //Pr�fixe _ & Suffixe Action
            $functionAction = (is_numeric($function) ? "_".$function : $function)."Action";

            // D�finition des noms des fichiers MVC
            $moduleController = ucfirst($module)."ViewController";

            if(!is_dir('modules/frontend/'.$module))
                throw new Exception('Module '.$module.' introuvable !');

            // Test existance fichier config du view module
            if(file_exists('modules/frontend/'.$module.'/config/'.$function.'.ini'))
                $config = parse_ini_file('modules/frontend/'.$module.'/config/'.$function.'.ini', true);
            else
                throw new Exception('Fichier de config '.$function.'.ini du module '.$module.' non trouv�e !', 1);

            // TODO - Faire un controller pour cet h�ritage de config
            // H�ritage des configs modules
            $config = array_replace_recursive($mod_config, $config);

            // Test existance controller
            if(file_exists('modules/frontend/'.$module.'/controller/'.$moduleController.'.php'))
                require_once('modules/frontend/'.$module.'/controller/'.$moduleController.'.php');
            else
                throw new Exception('Le '.$moduleController.'.php du module '.$module.' non trouv�e !', 1);

            // Test existance class controller
            if(!class_exists($moduleController))
                throw new Exception('Class '.$moduleController.' n\'existe pas !', 1);
            else if(!method_exists($moduleController, $functionAction))
                throw new Exception('Function '.$moduleController.'::'.$functionAction.' n\'existe pas !', 1);
            else
                $data = $moduleController::$functionAction($params);
/*
            // Param�tre fullpage du view module
            if($config['param']['fullpage'] == "0")
            {
                // TODO - Gestionnaire de cache et minifer
                // Assets CSS
                if(!empty($config['assets']['css'])){
                    foreach($config['assets']['css'] as $asset)
                        Assets::addCssFile($asset);
                }

                if(!empty($config['assets']['mainCSS'])) {
                    foreach ($config['assets']['mainCSS'] as $asset)
                        Assets::addCssFile($asset);
                }

                // Assets JS
                if(!empty($config['assets']['mainJS'])) {
                    foreach ($config['assets']['mainJS'] as $asset)
                        Assets::addJsFile($asset);
                }

                if(!empty($config['assets']['js'])){
                    foreach($config['assets']['js'] as $asset)
                        Assets::addJsFile($asset);
                }

                if(Config::get('global','assets_minifer') == 1) {

                    if(!file_exists(Core::getRoot().'modules/frontend/'.$module.'/cache/js'))
                        if(!mkdir(Core::getRoot().'modules/frontend/'.$module.'/cache/js', 0777, true))
                            throw new Exception('Echec lors de la cr�ation du dossier cache JS !');

                    if(!file_exists(Core::getRoot().'modules/frontend/'.$module.'/cache/js/'.$function.'.min.js'))
                        if(!fopen(Core::getRoot().'modules/frontend/'.$module.'/cache/js/'.$function.'.min.js','w'))
                            throw new Exception('Echec lors de la cr�ation du fichier cache JS !');

                    if(!file_exists(Core::getRoot().'modules/frontend/'.$module.'/cache/css'))
                        if(!mkdir(Core::getRoot().'modules/frontend/'.$module.'/cache/css', 0777, true))
                            throw new Exception('Echec lors de la cr�ation du dossier cache CSS !');

                    if(!file_exists(Core::getRoot().'modules/frontend/'.$module.'/cache/js/'.$function.'.min.css'))
                        if(!fopen(Core::getRoot().'modules/frontend/'.$module.'/cache/js/'.$function.'.min.css','w'))
                            throw new Exception('Echec lors de la création du fichier cache CSS !');

                    Assets::saveCss(Core::getRoot().'modules/frontend/'.$module.'/cache/css/'.$function.'.min.css');
                    Assets::saveJs(Core::getRoot().'modules/frontend/'.$module.'/cache/js/'.$function.'.min.js');
                }// TODO - Templatis� le bordel


            }*/

            Twig_Autoloader::register();

            $loader1 = new Twig_Loader_Filesystem('template/frontend');
            $loader2 = new Twig_Loader_Array(array(
                'module_content' => file_get_contents("modules/frontend/".$module."/view/".$function.".twig"),
            ));

            $loader = new Twig_Loader_Chain(array($loader1, $loader2));

            $twig = new Twig_Environment($loader);
            echo $twig->render('index.twig', array(
                'assets' => array('directory' => Core::absURL()."template/frontend/assets"),
                'data' => $data
            ));

        }

    }