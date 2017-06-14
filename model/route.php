<?php

class route {

    private $params;
    private $docRoot;

    public function __construct() {
        defined('APP_DOC_ROOT') or
            die ('Configuration Setting: APP_DOC_ROOT is not set.');
        
        $this->docRoot = APP_DOC_ROOT;
        $this->params  = $this->proccessRequestURI();
    }


    public function getParams() {
        return $this->params;
    }

    public function getController() {
        return ( isset($this->params[0]) ? $this->params[0] : null);
    }

    public function getAction() {
        return ( isset($this->params[1]) ? $this->params[1] : null);
    }

    private function proccessRequestURI() {
        $uri = str_replace($this->docRoot, '', $_SERVER['REQUEST_URI']);
        $array = explode('/', $uri);

        array_shift($array);

        while (1 < count($array) && null == $array[count($array)-1]) {
            array_pop($array);
        }

        return $array;
    }
}
