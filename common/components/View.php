<?php

namespace Lyber\Common\Components;

use Exception;
use Twig_Environment;
use Twig_Extensions_Extension_Text;
use Twig_Loader_Filesystem;

class View {

    protected $template = null;
    protected $datas = array();

    /**
     * @param $template
     * @throws Exception
     */
    public function __construct($template){
        if(!file_exists($template)){
            throw new Exception('Template file not founed !');
        }

        $this->template = $template;
    }

    /**
     * @param array $datas
     * @return $this
     * @throws Exception
     */
    public function setDatas($datas){
        if(!is_array($datas)) {
            throw new Exception('Datas variable is not an array !');
        }

        $this->datas = array_replace_recursive($datas, $this->datas);

        return $this;
    }

    /**
     * @return string
     */
    public function render(){
        $path = explode(DIRECTORY_SEPARATOR, $this->template);
        $file = array_pop($path);
        $directory = implode(DIRECTORY_SEPARATOR, $path);

        $loader = new Twig_Loader_Filesystem($directory);
        $twig = new Twig_Environment($loader);
        $twig->addExtension(new Twig_Extensions_Extension_Text());
        return $twig->render($file, $this->datas);
    }

}