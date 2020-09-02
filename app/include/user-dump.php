<?php
require_once "common.php";

function dumpUser($user){
    $student_dao = new studentDAO;

    $data = $student_dao->retrieveAll(); 
    $json = json_encode($data);
    $students = json_decode($json, true);

    # create index for error
    $error = 0;

    $student = array();
    $userid = '';
    $password = '';
    $name = '';
    $school = '';
    $edollar = '';

    //Check if userid exists against all student objects
    foreach($students as $s){
        if($s['userID'] == $user){
            $error++;
            $userid = $s['userID'];
            $password = $s['password'];
            $name = $s['name'];
            $school = $s['school'];
            $edollar = $s['edollar'];
            $student[] = $s;
            

        }
    }

    //If match occurs, return success
    if ($error != 0 ){
        $result = [
            "status" => "success",
            "userid" => $userid,
            "password" => $password,
            "name" => $name,
            "school" => $school,
            "edollar" => $edollar,
        ];
    }

    //If there are no matches ,return error messages
    else {
        $result = [
        "status" => "error",
        "message" => ["invalid userid"],
        ];
    }
    return $result;
}

?>