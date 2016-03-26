<?php

namespace Lyber\Common\Components;

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ErrorsHandler {

    public static function init(){

        $whoops = new Run;
        $whoops->pushHandler(new PrettyPageHandler);
        $whoops->pushHandler(function ($exception, $inspector, $run) {

            $infos = array();
            $infos['type'] = get_class($exception);
            $infos['code'] = $exception->getCode();
            $infos['message'] = $exception->getMessage();
            $infos['file'] = $exception->getFile();
            $infos['line'] = $exception->getLine();
            $infos['previous'] = $exception->getPrevious();
            if (!empty($exception->errorInfo)) {
                $infos['infos_supp'] = json_encode($exception->errorInfo);
            }
            $infos['trace'] = $exception->getTraceAsString();

            if(Config::get("global", "dev_mode") == "0") {

                Log::addError($infos);
                Email::send(Config::get("mails", "alertes_dev"),'Error exception non encapsulée !', '<pre>'.print_r($infos,true).'</pre>');

                Core::redirect('error/500');
            }
        });

        $whoops->register();

    }

}