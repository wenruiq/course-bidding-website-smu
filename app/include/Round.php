<?php

class round{

    public $roundnum;
    public $status;


    public function __construct($roundnum, $status){
        $this->roundnum = $roundnum;
        $this->status = $status;

    }

    public function getRoundNum() {
        return $this->roundnum;
    }

    public function getStatus() {
        return $this->status;
    }

}

?>