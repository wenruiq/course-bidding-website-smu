<?php

class minimum_bid_value{

    public $amount;
    public $code;
    public $section;


    public function __construct($amount, $code, $section){
        $this->amount = $amount;
        $this->code = $code;
        $this->section = $section;
    }


    public function getAmount() {
        return $this->amount;
    }

    public function getCode() {
        return $this->code;
    }

    public function getSection() {
        return $this->section;
    }

}

?>