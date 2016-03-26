<?php

namespace Lyber\Common\Engines;

use Exception;
use Flight;
use Lyber\Common\Components\Core;

class APIEngine
{

    public static function start($module, $function, $param)
    {
        Core::getRequest();

        // Fonction par défault
        $function = (empty($function)) ? 'show' : $function;

        //Préfixe _ & Suffixe Action
        $functionAction = (is_numeric($function) ? "_".$function : $function)."Action";

        // Définition des noms des fichiers MVC
        $moduleController = ucfirst($module)."APIController";

        if(!is_dir('apps/webservices/'.$module))
            throw new Exception('Module '.$module.' introuvable !');

        // Test existance fichier config du view module
        if(file_exists('apps/webservices/'.$module.'/config/'.$function.'.ini'))
            $config = parse_ini_file('apps/webservices/'.$module.'/config/'.$function.'.ini', true);
        else
            throw new Exception('Fichier de config '.$function.'.ini du module '.$module.' non trouvée !', 1);

        $mod_config = parse_ini_file('apps/webservices/WebservicesConfig.ini', true);

        // TODO - Faire un controller pour cet héritage de config
        // Héritage des configs modules
        $config = array_replace_recursive($mod_config, $config);

        // Test existance controller
        if(file_exists('apps/webservices/'.$module.'/controller/'.$moduleController.'.php'))
            require_once('apps/webservices/'.$module.'/controller/'.$moduleController.'.php');
        else
            throw new Exception('Le '.$moduleController.'.php du module '.$module.' non trouvée !', 1);

        // Test existance class controller
        if(!class_exists($moduleController))
            throw new Exception('Class '.$moduleController.' n\'existe pas !', 1);
        else if(!method_exists($moduleController, $functionAction))
            throw new Exception('Function '.$moduleController.'::'.$functionAction.' n\'existe pas !', 1);
        else
            $data = $moduleController::$functionAction($param);

        if(!is_array($data))
            throw new Exception($moduleController . '::' . $functionAction . ' ne renvoit pas un array !');

        echo Flight::json($data);

    }

}