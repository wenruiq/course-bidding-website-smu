<?php

class student{

    public $userID;
    public $password;
    public $name;
    public $school;
    public $edollar;


    public function __construct($userID, $password, $name, $school, $edollar){
        $this->userID = $userID;
        $this->password = $password;
        $this->name = $name;
        $this->school = $school;
        $this->edollar = $edollar;
    }

    public function getUserID() {
        return $this->userID;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getName() {
        return $this->name;
    }

    public function getSchool() {
        return $this->school;
    }

    public function getEdollar() {
        return $this->edollar;
    }

    public function verify($enteredPwd) {
        $hash = password_hash($this->password, PASSWORD_DEFAULT);
        return password_verify($enteredPwd, $hash);
    }
    
}

?>