<?php

namespace Lyber\Apps\Admin;

use Lyber\Common\Controllers\AppController;

class AdminAppController extends AppController {

    protected function getDatasToMerge(){

        return array(
            'merged' => true
        );

    }
}