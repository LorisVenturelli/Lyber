<?php

namespace Lyber\Common\Engines;

use Exception;
use Lyber\Common\Components\Assets;
use Lyber\Common\Components\Config;
use Lyber\Common\Components\Cookie;
use Lyber\Common\Components\Core;

use Twig_Autoloader;
use Twig_Environment;
use Twig_Loader_Array;
use Twig_Loader_Chain;
use Twig_Loader_Filesystem;

class FrontendEngine {

    public static function start($module, $function, $params) {
        
        Core::getRequest();

        // Routes personnalis�s
        $mod_config = parse_ini_file('apps/frontend/FrontendConfig.ini', true);

        // Module par d�fault
        $module = (empty($module)) ? Config::get('global', 'frontend_home_module') : $module;

        // Fonction par d�fault
        $function = (empty($function)) ? 'show' : $function;

        //Pr�fixe _ & Suffixe Action
        $functionAction = (is_numeric($function) ? "_" . $function : $function) . "Action";

        // D�finition des noms des fichiers MVC
        $moduleController = ucfirst($module) . "ViewController";

        if (!is_dir('apps/frontend/' . $module))
            throw new Exception('Module ' . $module . ' introuvable !');

        // Test existance fichier config du view module
        if (file_exists('apps/frontend/' . $module . '/config/' . $function . '.ini'))
            $config = parse_ini_file('apps/frontend/' . $module . '/config/' . $function . '.ini', true);
        else
            throw new Exception('Fichier de config ' . $function . '.ini du module ' . $module . ' non trouv�e !', 1);

        // TODO - Faire un controller pour cet h�ritage de config
        // H�ritage des configs modules
        $config = array_replace_recursive($mod_config, $config);

        $moduleController = "Lyber\\Apps\\Frontend\\".ucfirst($module)."\\".$moduleController;

        // Test existance class controller
        if (!class_exists($moduleController))
            throw new Exception('Class ' . $moduleController . ' n\'existe pas !', 1);
        else if (!method_exists($moduleController, $functionAction))
            throw new Exception('Function ' . $moduleController . '::' . $functionAction . ' n\'existe pas !', 1);
        else
            $data = $moduleController::$functionAction($params);


        // TODO - Gestionnaire de cache et minifer
        if (!empty($config['assets']['mainCSS'])) {
            foreach ($config['assets']['mainCSS'] as $asset)
                Assets::addCssFile("template/frontend/assets/" . $asset);
        }

        // Assets JS
        if (!empty($config['assets']['mainJS'])) {
            foreach ($config['assets']['mainJS'] as $asset)
                Assets::addJsFile("template/frontend/assets/" . $asset);
        }

        $minified = false;
        // Gestion minifer + cache assets
        if ($config['assets']['minifier'] == "1" || ($config['assets']['load_cache'] == "1" && !file_exists(Core::getRoot() . 'cache/assets/frontend.min.js'))) {
            if (!file_exists(Core::getRoot() . 'cache/assets'))
                if (!mkdir(Core::getRoot() . 'cache/assets', 0777, true))
                    throw new Exception('Echec lors de la cr�ation du dossier cache main JS !');

            if (!file_exists(Core::getRoot() . 'cache/assets/frontend.min.js'))
                if (!fopen(Core::getRoot() . 'cache/assets/frontend.min.js', 'w'))
                    throw new Exception('Echec lors de la cr�ation du fichier cache main JS !');

            if (!file_exists(Core::getRoot() . 'cache/assets/frontend.min.css'))
                if (!fopen(Core::getRoot() . 'cache/assets/frontend.min.css', 'w'))
                    throw new Exception('Echec lors de la cr�ation du fichier cache main CSS !');

            Assets::saveCss(Core::getRoot() . 'cache/assets/frontend.min.css');
            Assets::saveJs(Core::getRoot() . 'cache/assets/frontend.min.js');

            $minified = true;
        }

        Twig_Autoloader::register();

        $loader1 = new Twig_Loader_Filesystem('template/frontend');
        $loader2 = new Twig_Loader_Array(array(
            'module_content' => file_get_contents("apps/frontend/" . $module . "/view/" . $function . ".twig"),
        ));
        
        $loader = new Twig_Loader_Chain(array($loader1, $loader2));

        $twig = new Twig_Environment($loader);

        echo $twig->render('index.twig', array(
            'app' => array(
                'abs_url' => Core::absURL(),
                'module' => $module,
                'accept_cookie' => Cookie::Exists("accept_cookie")
            ),
            'routes' => $mod_config['routes'],
            'assets' => array(
                'load_cache' => ($config['assets']['load_cache'] == "1"),
                'minifer' => ($config['assets']['minifier'] == "1"),
                'directory' => Core::absURL() . "template/frontend/assets/",
                'css' => ($minified || $config['assets']['load_cache'] == "1") ? array("cache/assets/frontend.min.css") : Assets::getCssList(),
                'js' => ($minified || $config['assets']['load_cache'] == "1") ? array("cache/assets/frontend.min.js") : Assets::getJsList()
            ),
            'data' => $data
        ));
    }

}
