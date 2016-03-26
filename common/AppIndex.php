<?php
use Lyber\Common\Components\Config;
use Lyber\Common\Components\Core;
use Lyber\Common\Components\ErrorsHandler;
use Lyber\Common\Engines\AdminEngine;
use Lyber\Common\Engines\AjaxEngine;
use Lyber\Common\Engines\APIEngine;
use Lyber\Common\Engines\BackendEngine;
use Lyber\Common\Engines\FrontendEngine;

header('Content-Type: text/html; charset=utf-8');

require_once(__DIR__."/components/Autoloader.php");
require_once(__DIR__."/../vendor/autoload.php");

Config::load(Core::getRoot()."common/AppConfig.ini");

ErrorsHandler::init();

// Routage Ajax
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

// Routage admin
Flight::route('/admin(/@module(/@function(/@param)))', function($module, $function, $param) {

    AdminEngine::start($module, $function, $param);

});

// Routes personnalisés
$mod_config = parse_ini_file(Core::getRoot().'apps/frontend/FrontendConfig.ini', true);

foreach($mod_config['routes'] as $direction => $links) {

    foreach($links as $key => $link) {

        Flight::route($link, function ($route) {

            $arguments = func_get_args();

            $link = $arguments[count($arguments)-1]->pattern;

            // Routes personnalis?s
            $mod_config = parse_ini_file(Core::getRoot().'apps/frontend/FrontendConfig.ini', true);

            $direction = Core::array_searchRecursive($link, $mod_config['routes']);

            $route_personnalise = explode('.', $direction[0]);

            $module = "";
            $function = "";

            if (!empty($route_personnalise[0])) {
                $module = $route_personnalise[0];
                $function = $route_personnalise[1];
            }


            FrontendEngine::start($module, $function, $arguments[count($arguments)-1]->params);

        }, true);

    }

}

// Routage frontend
Flight::route('(/@module(/@function(/@param)))', function($module, $function, $param) {

    FrontendEngine::start($module, $function, $param);

});

Flight::set('flight.handle_errors', false);
Flight::start();
