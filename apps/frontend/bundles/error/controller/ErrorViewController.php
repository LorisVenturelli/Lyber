<?php

namespace Lyber\Apps\Frontend\Error;

use Lyber\Common\Components\Core;
use Lyber\Common\Controllers\ViewController;

class ErrorViewController extends ViewController
{

    public static function showAction($params){

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