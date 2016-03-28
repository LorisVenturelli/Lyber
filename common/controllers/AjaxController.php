<?php

namespace Lyber\Common\Controllers;

use Exception;
use Lyber\Common\Components\Core;

class AjaxController extends WebServicesController {

    public function init(){

        if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            $this->datas = Core::jsonResponse(false, "Cette requête n\'est pas de l\'ajax !", $this->datas);
        }
        else {
            parent::init();
        }

        return $this;

    }

}