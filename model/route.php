<?php

class route {

    private $params;


    public function __construct() {
        $this->params = $this->proccessRequestURI();
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
        $array = explode('/', $_SERVER['REQUEST_URI']);
        array_shift($array);
        array_shift($array);

        while (1 < count($array) && null == $array[count($array)-1]) {
            array_pop($array);
        }

        return $array;
    }
}
