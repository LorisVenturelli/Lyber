<?php

namespace Lyber\Common\Controllers;

use Exception;
use Lyber\Common\Components\Assets;
use Lyber\Common\Components\Auth;
use Lyber\Common\Components\Core;

use Lyber\Common\Components\View;
use Lyber\Common\Entities\User;

class AppController {

    protected $app          = "";
    protected $module       = "";
    protected $function     = "";
    protected $params       = array();
    protected $datas        = array();

    /* Variables temporaires */
    protected $config_app   = array();
    protected $config_view  = array();
    protected $minified     = false;

    public function __construct($app, $module, $function, $params){
        $this->app = $app;
        $this->module = $module;
        $this->function = $function;
        $this->params = $params;
    }

    public function init(){

        Core::getRequest();

        $this->config_app = parse_ini_file('apps/'.$this->app.'/'.ucfirst($this->app).'Config.ini', true);

        // Module par défault
        $this->module = (empty($this->module)) ? $this->config_app["main"]["home_module"] : $this->module;

        // Fonction par défault
        $this->function = (empty($this->function)) ? 'index' : $this->function;

        // Test authentification
        if($this->config_app["main"]["access_auth"] == "1") {

            if(Auth::isLocked() === true && $this->module != "login" && $this->function != "lock"){
                Core::redirect(Core::absURL() . $this->config_app["main"]["prefix_link"] . '/login/lock');
                exit;
            }
            else if(Auth::isLogged() === false && $this->module != "login"){
                Core::redirect(Core::absURL() . $this->config_app["main"]["prefix_link"] . '/login');
                exit;
            }

        }

        //Préfixe _ & Suffixe Action
        $functionAction = (is_numeric($this->function) ? "_".$this->function : $this->function)."Action";

        // Définition des noms des fichiers MVC
        $moduleController = ucfirst($this->module)."BundleController";

        if(!is_dir('apps/'.$this->app.'/bundles/'.$this->module))
            throw new Exception('Module '.$this->module.' introuvable !');


        // Test existance fichier config du view module
        if(file_exists('apps/'.$this->app.'/bundles/'.$this->module.'/config/'.$this->function.'.ini')) {
            $this->config_view = parse_ini_file('apps/'.$this->app.'/bundles/'.$this->module.'/config/'.$this->function.'.ini', true);
        }
        else {
            throw new Exception('Fichier de config '.$this->function.'.ini du module '.$this->module.' non trouvée !', 1);
        }

        // TODO - Faire un controller pour cet héritage de config
        // Héritage des configs modules
        $this->config_view = array_replace_recursive($this->config_app, $this->config_view);

        $moduleController = "Lyber\\Apps\\".$this->app."\\Bundles\\".ucfirst($this->module)."\\Controller\\".$moduleController;

        // Test existance class controller
        if(!class_exists($moduleController))
            throw new Exception('Class '.$moduleController.' n\'existe pas !', 1);
        else if(!method_exists($moduleController, $functionAction))
            throw new Exception('Function '.$moduleController.'::'.$functionAction.' n\'existe pas !', 1);
        else
            $this->datas = $moduleController::$functionAction($this->params);


        // TODO - Gestionnaire de cache et minifer
        if (!empty($this->config_view['assets']['mainCSS'])) {
            foreach ($this->config_view['assets']['mainCSS'] as $asset)
                Assets::addCssFile("apps/".$this->app."/assets/" . $asset);
        }

        // Assets JS
        if (!empty($this->config_view['assets']['mainJS'])) {
            foreach ($this->config_view['assets']['mainJS'] as $asset)
                Assets::addJsFile("apps/".$this->app."/assets/" . $asset);
        }

        return $this;
    }

    /**
     * Get all common datas for all apps
     * @return array
     */
    protected function getCommonDatas(){
        return array(
            'app' => array(
                'abs_url' => Core::absURL() . $this->config_app["main"]["prefix_link"] . "/",
                'host' => Core::absURL(),
                'frontend_url' => Core::absURL(),
                'module' => $this->module
            ),
            'assets' => array(
                'directory' => Core::absURL()."apps/".$this->app."/assets/",
                'css' => ($this->minified || $this->config_view['assets']['load_cache'] == "1") ? array("apps/".$this->app."/cache/assets/".$this->app.".min.css") : Assets::getCssList(),
                'js' => ($this->minified || $this->config_view['assets']['load_cache'] == "1") ? array("apps/".$this->app."/cache/assets/".$this->app.".min.js") : Assets::getJsList()
            ),
            'user' => array(
                'id_user' => (User::getInstance() !== null) ? User::getInstance()->getId_user() : "",
                'firstname' => (User::getInstance() !== null) ? User::getInstance()->getFirstName() : "",
                'lastname' => (User::getInstance() !== null) ? User::getInstance()->getLastName() : "",
                'email' => (User::getInstance() !== null) ? User::getInstance()->getEmail() : "",
            ),
            'data' => $this->datas
        );
    }

    /**
     * Override this function for create common datas on an application
     * @return array
     */
    protected function getDatasToMerge(){
        return array();
    }

    /**
     * Get all final datas for send to views
     * @return array
     */
    protected function getDatas(){
        return array_replace_recursive($this->getDatasToMerge(), $this->getCommonDatas());
    }

    /**
     * Final render
     * @return string
     * @throws Exception
     */
    public function render(){

        // Gestion minifer + cache assets
        if ($this->config_view['assets']['minifier'] == "1" || ($this->config_view['assets']['load_cache'] == "1" && !file_exists(Core::getRoot() . 'apps/'.$this->app.'/cache/assets/'.$this->app.'.min.js'))) {
            if (!file_exists(Core::getRoot() . 'apps/'.$this->app.'/cache/assets'))
                if (!mkdir(Core::getRoot() . 'apps/'.$this->app.'/cache/assets', 0777, true))
                    throw new Exception('Echec lors de la création du dossier cache main JS !');

            if (!file_exists(Core::getRoot() . 'apps/'.$this->app.'/cache/assets/'.$this->app.'.min.js'))
                if (!fopen(Core::getRoot() . 'apps/'.$this->app.'/cache/assets/'.$this->app.'.min.js', 'w'))
                    throw new Exception('Echec lors de la création du fichier cache main JS !');

            if (!file_exists(Core::getRoot() . 'apps/'.$this->app.'/cache/assets/'.$this->app.'.min.css'))
                if (!fopen(Core::getRoot() . 'apps/'.$this->app.'/cache/assets/'.$this->app.'.min.css', 'w'))
                    throw new Exception('Echec lors de la cr?ation du fichier cache main CSS !');

            Assets::saveCss(Core::getRoot() . 'apps/'.$this->app.'/cache/assets/'.$this->app.'.min.css');
            Assets::saveJs(Core::getRoot() . 'apps/'.$this->app.'/cache/assets/'.$this->app.'.min.js');

            $this->minified = true;
        }

        $datas = $this->getDatas();

        $view = new View(implode(DIRECTORY_SEPARATOR, array('apps', $this->app, 'bundles', $this->module, 'view', $this->function.'.twig')));
        $bundle_content = $view->setDatas($datas)->render();

        if(!empty($this->config_view['param']['fullpage']) && $this->config_view['param']['fullpage'] == "1") {
            return $bundle_content;
        }

        $datas["bundle_content"] = $bundle_content;

        $view = new View(implode(DIRECTORY_SEPARATOR, array('apps', $this->app, 'view', 'main.twig')));
        $html = $view->setDatas($datas)->render();

        return $html;

    }

}