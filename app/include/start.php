<?php
require_once "common.php";

function start(){
    $errors = array();
    $dao = new roundDAO;
    $round1 = $dao->retrieveByRound("round 1");
    $round2 = $dao->retrieveByRound("round 2");
    $round = '';

    //Update round status and round number
    if($round1 == "not started" || $round1 == "started"){
        $dao->update("round 1", "started");
        $round = '1';
    }
    elseif($round1 == "ended"){
        if($round2 == "not started" || $round2 == "started"){
            $dao->update("round 2", "started");
            $round = '2';
        }
        elseif($round2 == "ended"){
            $errors[] = "round 2 ended";
        }
    }

    //If there are no errors, return success messages and new round number
    if(count($errors) == 0){
        $results = [
            "status" => "success",
            "round" => intval($round)
        ];
    }

    //If errors are present, return error messages
    else{
        $results = [
            "status" => "error",
            "message" => $errors
        ];
    }
    return $results;
}
?>