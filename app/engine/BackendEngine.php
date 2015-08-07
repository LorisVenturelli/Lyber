<?php

    class BackendEngine
    {

        public static function start($module, $function, $param)
        {
            Core::getRequest();

            // Module par défault
            $module = (empty($module)) ? Config::get('global','backend_home_module') : $module;
            // Fonction par défault
            $function = (empty($function)) ? 'show' : $function;

            //Préfixe _ & Suffixe Action
            $functionAction = (is_numeric($function) ? "_".$function : $function)."Action";

            // Définition des noms des fichiers MVC
            $moduleController = ucfirst($module)."ViewController";

            if(!is_dir('modules/backend/'.$module))
                throw new Exception('Module '.$module.' introuvable !');

            // Test existance fichier config du view module
            if(file_exists('modules/backend/'.$module.'/config/'.$function.'.ini'))
                $config = parse_ini_file('modules/backend/'.$module.'/config/'.$function.'.ini', true);
            else
                throw new Exception('Fichier de config '.$function.'.ini du module '.$module.' non trouvée !', 1);

            $mod_config = parse_ini_file('modules/backend/BackendConfig.ini', true);

            // TODO - Faire un controller pour cet héritage de config
            // Héritage des configs modules
            $config = array_replace_recursive($mod_config, $config);

            // Test existance controller
            if(file_exists('modules/backend/'.$module.'/controller/'.$moduleController.'.php'))
                require_once('modules/backend/'.$module.'/controller/'.$moduleController.'.php');
            else
                throw new Exception('Le '.$moduleController.'.php du module '.$module.' non trouvée !', 1);

            // Test existance class controller
            if(!class_exists($moduleController))
                throw new Exception('Class '.$moduleController.' n\'existe pas !', 1);
            else if(!method_exists($moduleController, $functionAction))
                throw new Exception('Function '.$moduleController.'::'.$functionAction.' n\'existe pas !', 1);
            else
                $data = $moduleController::$functionAction($param);

            // Paramètre fullpage du view module
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

                    if(!file_exists(Core::getRoot().'modules/backend/'.$module.'/cache/js'))
                        if(!mkdir(Core::getRoot().'modules/backend/'.$module.'/cache/js', 0777, true))
                            throw new Exception('Echec lors de la création du dossier cache JS !');

                    if(!file_exists(Core::getRoot().'modules/backend/'.$module.'/cache/js/'.$function.'.min.js'))
                        if(!fopen(Core::getRoot().'modules/backend/'.$module.'/cache/js/'.$function.'.min.js','w'))
                            throw new Exception('Echec lors de la création du fichier cache JS !');

                    if(!file_exists(Core::getRoot().'modules/backend/'.$module.'/cache/css'))
                        if(!mkdir(Core::getRoot().'modules/backend/'.$module.'/cache/css', 0777, true))
                            throw new Exception('Echec lors de la création du dossier cache CSS !');

                    if(!file_exists(Core::getRoot().'modules/backend/'.$module.'/cache/js/'.$function.'.min.css'))
                        if(!fopen(Core::getRoot().'modules/backend/'.$module.'/cache/js/'.$function.'.min.css','w'))
                            throw new Exception('Echec lors de la création du fichier cache CSS !');

                    Assets::saveCss(Core::getRoot().'modules/backend/'.$module.'/cache/css/'.$function.'.min.css');
                    Assets::saveJs(Core::getRoot().'modules/backend/'.$module.'/cache/js/'.$function.'.min.js');
                }


                // TODO - Templatisé le bordel

                // Inclusion du header
                ob_start();

                include("template/backend/header.phtml");
                $content_header = ob_get_contents();

                ob_end_clean();

                // Inclusion du sidebar
                ob_start();

                include("template/backend/sidebar.phtml");
                $content_sidebar = ob_get_contents();

                ob_end_clean();

                // Inclusion du footer
                ob_start();

                include("template/backend/footer.phtml");
                $content_footer = ob_get_contents();

                ob_end_clean();
            }
            else {
                $content_header = $content_sidebar = $content_footer = NULL;
            }

            // HTML template du view module
            ob_start();

            include("modules/backend/".$module."/view/".$function.".phtml");
            $content_html = ob_get_contents();

            ob_end_clean();


            echo $content_header . $content_sidebar . $content_html . $content_footer;
        }

    }