<?php

namespace Lyber\Common\Controllers;

use Exception;
use Flight;
use Lyber\Common\Components\Core;

class WebServicesController extends AppController
{

    public function init(){

        try{
            parent::init();

            $this->datas = Core::jsonResponse(true, "success", $this->datas);
        }
        catch(Exception $e){
            $this->datas = Core::jsonResponse(false, $e->getMessage(), $this->datas);
        }

        return $this;
    }

    public function render(){
        Flight::json($this->datas);
    }

}