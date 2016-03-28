<?php
use Lyber\Common\Components\Config;
use Lyber\Common\Components\Core;
use Lyber\Common\Components\ErrorsHandler;

header('Content-Type: text/html; charset=utf-8');

require_once(__DIR__."/components/Autoloader.php");
require_once(__DIR__."/../vendor/autoload.php");

Config::load(Core::getRoot()."common/AppConfig.ini");

ErrorsHandler::init();

$list_prefix = array();
foreach (glob(__DIR__."/../apps/*/*Config.ini") as $filename) {
    $tmp_config = parse_ini_file($filename, true);
    if (isset($tmp_config['main']['prefix_link'])) {
        $list_prefix[$tmp_config["main"]["name_app"]] = $tmp_config['main']['prefix_link'];
    }
}

$empty = null;

foreach($list_prefix as $app => $prefix) {

    require_once(__DIR__."/../apps/".$app."/".ucfirst($app)."AppController.php");

    $mod_config = parse_ini_file(Core::getRoot().'apps/'.$app.'/'.ucfirst($app).'Config.ini', true);

    foreach($mod_config['routes'] as $direction => $links) {

        foreach($links as $key => $link) {

            Flight::route((!empty($prefix) ? "/" : "") . $prefix . $link, function ($route) use ($app, $direction, $link) {

                $arguments = func_get_args();

                $route_personnalise = explode('.', $direction);

                $module = "";
                $function = "";

                if (!empty($route_personnalise[0])) {
                    $module = $route_personnalise[0];
                    $function = $route_personnalise[1];
                }

                $appController = "\\Lyber\\Apps\\".ucfirst($app)."\\".ucfirst($app)."AppController";

                $render = new $appController($app, $module, $function, end($arguments)->params);
                echo $render->init()->render();

            }, true);

        }

    }

    if(empty($prefix)){
        $empty = $app;
        continue;
    }

    Flight::route("/".$prefix . '(/@module(/@function(/@param)))', function($module, $function, $param) use ($app) {


        $appController = "\\Lyber\\Apps\\".ucfirst($app)."\\".ucfirst($app)."AppController";

        $render = new $appController($app, $module, $function, $param);
        echo $render->init()->render();
    });

}

if(!empty($empty)){
    Flight::route('(/@module(/@function(/@param)))', function($module, $function, $param) use ($empty) {

        $appController = "\\Lyber\\Apps\\".ucfirst($empty)."\\".ucfirst($empty)."AppController";

        $render = new $appController($empty, $module, $function, $param);
        echo $render->init()->render();

    });
}

Flight::set('flight.handle_errors', false);
Flight::start();
