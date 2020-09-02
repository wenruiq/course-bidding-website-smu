<?php

class admin{

    public $userID;
    public $password;


    public function __construct($userID, $password){
        $this->userID = $userID;
        $this->password = $password;
    }

    public function getUserID() {
        return $this->userID;
    }

    public function getPassword() {
        return $this->password;
    }

    public function verify($enteredPwd) {
        $hash = password_hash($this->password, PASSWORD_DEFAULT);
        return password_verify($enteredPwd, $hash);
    }

}

?>