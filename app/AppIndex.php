<?php

    require_once(__DIR__."/../vendor/autoload.php");

    Config::load(Core::getRoot()."config/global.ini");

    // Library Logger
    $_LOG = new \Monolog\Logger('UserName');
    $_LOG->pushHandler(new \Monolog\Handler\StreamHandler(Core::getRoot().'logs/'.date('Y-m-d').'.txt', \Monolog\Logger::WARNING));
    // $log->addWarning('Foo');
    // $log->addError('Bar');

    // Library Handle Errors
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->pushHandler(function ($exception, $inspector, $run) {
        if(Config::get("global", "dev_mode") == "0") {
            GLOBAL $_LOG;
            $_LOG->addError($exception->getMessage());
            Email::send(Config::get("mails", "alertes_dev"),'Error exception non encapsulé !','Exception : '.$exception->getMessage().'<br>Code : '.$exception->getCode().'<br>File : '.$exception->getFile().'<br>Line : '.$exception->getLine().'<br>Previous : '.$exception->getPrevious().'<br><br>$_REQUEST : <pre>'.print_r($_SERVER,true).'</pre>');
            Core::redirect('error/500');
        }
    });
    $whoops->register();

    // BDD
    $_DATABASE = new Database();

    // Routage avec librairie Flight
    Flight::route('(/@module(/@function(/@param)))', function($module, $function, $param) use ($_DATABASE) {

        Core::getRequest();

        // Module par défault
        $module = (empty($module)) ? Config::get('global','home_module') : $module;
        // Fonction par défault
        $function = (empty($function)) ? 'show' : $function;

        //Préfixe _ & Suffixe Action
        $functionAction = (is_numeric($function) ? "_".$function : $function)."Action";

        // Définition des noms des fichiers MVC
        $moduleController = ucfirst($module)."ViewController";
        $moduleModel = ucfirst($module)."Model";

        if(!is_dir('modules/'.$module))
            throw new Exception('Module '.$module.' introuvable !');

        // Test existance fichier config du view module
        if(file_exists('modules/'.$module.'/config/'.$function.'.ini'))
            $config = parse_ini_file('modules/'.$module.'/config/'.$function.'.ini', true);
        else
            throw new Exception('Fichier de config '.$function.'.ini du module '.$module.' non trouvée !', 1);

        $mod_config = parse_ini_file('modules/__modele/config/show.ini', true);

        // TODO - Faire un controller pour cet héritage de config
        // Héritage des configs modules
        $config = array_replace_recursive($mod_config, $config);

        // Test existance model
        if(file_exists('modules/'.$module.'/'.$moduleModel.'.php'))
            require_once('modules/'.$module.'/'.$moduleModel.'.php');

        // Test existance entity
        if(file_exists('modules/'.$module.'/'.ucfirst($module).'Entity.php'))
            require_once('modules/'.$module.'/'.ucfirst($module).'Entity.php');

        // Test existance controller
        if(file_exists('modules/'.$module.'/controller/'.$moduleController.'.php'))
            require_once('modules/'.$module.'/controller/'.$moduleController.'.php');
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

            foreach($config['assets']['mainCSS'] as $asset)
                Assets::addCssFile($asset);

            // Assets JS
            foreach($config['assets']['mainJS'] as $asset)
                Assets::addJsFile($asset);

            if(!empty($config['assets']['js'])){
                foreach($config['assets']['js'] as $asset)
                    Assets::addJsFile($asset);
            }

            if(Config::get('global','assets_minifer') == 1) {

                if(!file_exists(Core::getRoot().'modules/'.$module.'/cache/js'))
                    if(!mkdir(Core::getRoot().'modules/'.$module.'/cache/js', 0777, true))
                        throw new Exception('Echec lors de la création du dossier cache JS !');

                if(!file_exists(Core::getRoot().'modules/'.$module.'/cache/js/'.$function.'.min.js'))
                    if(!fopen(Core::getRoot().'modules/'.$module.'/cache/js/'.$function.'.min.js','w'))
                        throw new Exception('Echec lors de la création du fichier cache JS !');

                if(!file_exists(Core::getRoot().'modules/'.$module.'/cache/css'))
                    if(!mkdir(Core::getRoot().'modules/'.$module.'/cache/css', 0777, true))
                        throw new Exception('Echec lors de la création du dossier cache CSS !');

                if(!file_exists(Core::getRoot().'modules/'.$module.'/cache/js/'.$function.'.min.css'))
                    if(!fopen(Core::getRoot().'modules/'.$module.'/cache/js/'.$function.'.min.css','w'))
                        throw new Exception('Echec lors de la création du fichier cache CSS !');

                Assets::saveCss(Core::getRoot().'modules/'.$module.'/cache/css/'.$function.'.min.css');
                Assets::saveJs(Core::getRoot().'modules/'.$module.'/cache/js/'.$function.'.min.js');
            }


            // TODO - Templatisé le bordel

            // Inclusion du header
            ob_start();

            include("template/header.phtml");
            $content_header = ob_get_contents();

            ob_end_clean();

            // Inclusion du sidebar
            ob_start();

            include("template/sidebar.phtml");
            $content_sidebar = ob_get_contents();

            ob_end_clean();

            // Inclusion du footer
            ob_start();

            include("template/footer.phtml");
            $content_footer = ob_get_contents();

            ob_end_clean();
        }
        else {
            $content_header = $content_sidebar = $content_footer = NULL;
        }

        // HTML template du view module
        ob_start();

        include("modules/".$module."/view/".$function.".phtml");
        $content_html = ob_get_contents();

        ob_end_clean();


        echo $content_header . $content_sidebar . $content_html . $content_footer;

    });

    // Flight::route('POST (/@module(/@function(/@param)))', function($module, $function, $param) use ($_DATABASE) {

    // 	if(empty($module))
    // 		die(Core::json(array('message'=>'module manquant'), false));
    // 	else if(empty($function))
    // 		die(Core::json(array('message'=>'function manquant'), false));
    // 	else if(empty($param))
    // 		die(Core::json(array('message'=>'param manquant'), false));

    // 	require_once('modules/'.$module.'/model.php');

    // 	$module = ucfirst($module)."Model";

    // 	$data = $module::edit($param, $_POST);

    // 	Core::json($data);

    // });

    // Flight::route('PUT (/@module(/@function))', function($module, $fonction) use ($_DATABASE) {

    // 	Core::json(array("message"=>"PUT header method not supported, use POST method for insert or edit"), false);

    // });


    // Flight::route('DELETE (/@module(/@function(/@param)))', function($module, $function, $param) use ($_DATABASE) {

    // 	if(empty($module))
    // 		die(Core::json(array('message'=>'module manquant'), false));
    // 	else if(empty($function))
    // 		die(Core::json(array('message'=>'function manquant'), false));
    // 	else if(empty($param))
    // 		die(Core::json(array('message'=>'param manquant'), false));

    // 	require_once('modules/'.$module.'/model.php');

    // 	$module = ucfirst($module)."Model";

    // 	$data = $module::delete($param);

    // 	Core::json($data);

    // });

    Flight::set('flight.handle_errors', false);
    Flight::start();
