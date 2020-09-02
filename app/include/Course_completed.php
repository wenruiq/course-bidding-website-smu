<?php

class course_completed{

    public $userID;
    public $code;


    public function __construct($userID, $code){
        $this->userID = $userID;
        $this->code = $code;
    }

    public function getUserID() {
        return $this->userID;
    }

    public function getCode() {
        return $this->code;
    }

}

?>