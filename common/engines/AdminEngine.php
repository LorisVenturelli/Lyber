<?php

namespace Lyber\Common\Engines;

use Exception;
use Lyber\Common\Components\Auth;
use Lyber\Common\Components\Config;
use Lyber\Common\Components\Core;

use Lyber\Common\Entities\User;
use Twig_Autoloader;
use Twig_Environment;
use Twig_Extensions_Extension_Text;
use Twig_Loader_Array;
use Twig_Loader_Chain;
use Twig_Loader_Filesystem;

class AdminEngine
{

    public static function start($module, $function, $param)
    {

        Core::getRequest();

        // Module par défault
        $module = (empty($module)) ? Config::get('global','backend_home_module') : $module;
        // Fonction par défault
        $function = (empty($function)) ? 'show' : $function;

        if(Auth::isLocked() === true && $module != "login" && $function != "lock"){
            Core::redirect(Core::absURL().'admin/login/lock');
            exit;
        }
        else if(Auth::isLogged() === false && $module != "login"){
            Core::redirect(Core::absURL().'admin/login');
            exit;
        }

        //Préfixe _ & Suffixe Action
        $functionAction = (is_numeric($function) ? "_".$function : $function)."Action";

        // Définition des noms des fichiers MVC
        $moduleController = ucfirst($module)."ViewController";

        if(!is_dir('apps/admin/'.$module))
            throw new Exception('Module '.$module.' introuvable !');

        // Test existance fichier config du view module
        if(file_exists('apps/admin/'.$module.'/config/'.$function.'.ini'))
            $config = parse_ini_file('apps/admin/'.$module.'/config/'.$function.'.ini', true);
        else
            throw new Exception('Fichier de config '.$function.'.ini du module '.$module.' non trouvée !', 1);

        $mod_config = parse_ini_file('apps/admin/BackendConfig.ini', true);

        // TODO - Faire un controller pour cet héritage de config
        // Héritage des configs modules
        $config = array_replace_recursive($mod_config, $config);

        $moduleController = "Lyber\\Apps\\Admin\\".ucfirst($module)."\\".$moduleController;

        // Test existance class controller
        if(!class_exists($moduleController))
            throw new Exception('Class '.$moduleController.' n\'existe pas !', 1);
        else if(!method_exists($moduleController, $functionAction))
            throw new Exception('Function '.$moduleController.'::'.$functionAction.' n\'existe pas !', 1);
        else
            $data = $moduleController::$functionAction($param);


        Twig_Autoloader::register();

        // Paramètre fullpage du view module
        if(!empty($config['param']['fullpage']) && $config['param']['fullpage'] == "1")
        {
            $loader = new Twig_Loader_Filesystem("apps/admin/".$module."/view");
            $twig = new Twig_Environment($loader);
            $twig->addExtension(new Twig_Extensions_Extension_Text());
            $twig_file = $function.".twig";
        }
        else {
            $loader1 = new Twig_Loader_Filesystem('template/admin');
            $loader2 = new Twig_Loader_Array(array(
                'module_content' => file_get_contents("apps/admin/".$module."/view/".$function.".twig"),
            ));

            $loader = new Twig_Loader_Chain(array($loader1, $loader2));
            $twig = new Twig_Environment($loader);
            $twig->addExtension(new Twig_Extensions_Extension_Text());
            $twig_file = "index.twig";
        }

        $user_instance = array(
            'id_user' => (User::getInstance() !== null) ? User::getInstance()->getId_user() : "",
            'firstname' => (User::getInstance() !== null) ? User::getInstance()->getFirstName() : "",
            'lastname' => (User::getInstance() !== null) ? User::getInstance()->getLastName() : "",
            'email' => (User::getInstance() !== null) ? User::getInstance()->getEmail() : "",
        );

        echo $twig->render($twig_file, array(
            'app' => array(
                'abs_url' => Core::absURL()."admin/",
                'frontend_url' => Core::absURL(),
                'module' => $module
            ),
            'assets' => array(
                'directory' => Core::absURL()."template/admin/assets/"
            ),
            'user' => $user_instance,
            'data' => $data
        ));

    }

}