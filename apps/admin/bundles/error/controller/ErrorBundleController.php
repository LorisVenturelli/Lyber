<?php

namespace Lyber\Apps\Admin\Bundles\Error\Controller;

use Lyber\Common\Components\Core;
use Lyber\Common\Controllers\BundleController;

class ErrorBundleController extends BundleController
{

    public static function indexAction($params){

        if(empty($params['code']))
            Core::redirect(Core::absURL());

        $message = "";

        switch($params['code']){

            case '403':
                $message = "Accès interdit";
                break;

            case '404':
                $message = "Page non trouvée";
                break;

            case '500':
                $message = "Erreur survenue";
                break;

            default:
                $message = "Inconnu";
                break;

        }

        return array(
            'code' => $params['code'],
            'message' => $message
        );

    }

}