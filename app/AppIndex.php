<?php

    require_once(__DIR__."/../vendor/autoload.php");

    Config::load(Core::getRoot()."app/AppConfig.ini");

    ErrorsHandler::init();

    // Routage API
    Flight::route('/ajax(/@module(/@function(/@param)))', function($module, $function, $param) {

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            AjaxEngine::start($module, $function, $param);
        else
            echo "Cette requête n'est pas de l'ajax !";

    });

    // Routage API
    Flight::route('/api(/@module(/@function(/@param)))', function($module, $function, $param) {

        APIEngine::start($module, $function, $param);

    });

    // Routage backend
    Flight::route('/admin(/@module(/@function(/@param)))', function($module, $function, $param) {

        BackendEngine::start($module, $function, $param);

    });


    // Routes personnalis?s
    $mod_config = parse_ini_file('modules/frontend/FrontendConfig.ini', true);


    //die(print_r($mod_config['routes']));

    foreach($mod_config['routes'] as $direction => $links) {

        foreach($links as $key => $link) {

            Flight::route($link, function () {

                global $mod_config, $link;

                $direction = Core::array_searchRecursive($link, $mod_config['routes']);

                $route_personnalise = explode('.', $direction[0]);

                $module = "";
                $function = "";

                if (!empty($route_personnalise[0])) {
                    $module = $route_personnalise[0];
                    $function = $route_personnalise[1];
                }

                $arguments = func_get_args();

                FrontendEngine::start($module, $function, $arguments[count($arguments)-1]->params);

            }, true);

        }

    }

    // Routage frontend
    Flight::route('(/@module(/@function(/@param)))', function($module, $function, $param) {

        FrontendEngine::start($module, $function, $param);

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
