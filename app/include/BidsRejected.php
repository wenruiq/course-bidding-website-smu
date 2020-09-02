<?php
class bidsrejected{

public $userID;
public $amount;
public $code;
public $section;


public function __construct($userID, $amount, $code, $section){
    $this->userID = $userID;
    $this->amount = $amount;
    $this->code = $code;
    $this->section = $section;
}

public function getUserID() {
    return $this->userID;
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