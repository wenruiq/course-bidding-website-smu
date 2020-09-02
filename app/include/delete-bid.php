<?php
require_once "common.php";

function delete($userid, $code, $section){
    $bidDAO = new bidDAO;
    $roundDAO = new roundDAO;
    $courseDAO = new courseDAO;
    $studentDAO = new studentDAO;
    $sectionDAO = new sectionDAO;
    $inputErrors = 0;
    $logicErrors = 0;
    $errors = array();

    //Logic validations

    //Checks for invalid course
    if ( empty($courseDAO->retrieveByCourseID($code)) ){
        $errors[] = 'invalid course';
        $inputErrors ++;
    }

    //Checks for invalid userid
    if ( empty($studentDAO->retrieveByID($userid))){
        $errors[] = 'invalid userid';
        $inputErrors ++;
    }

    //Checks for invalid section
    if( !empty($courseDAO->retrieveByCourseID($code)) && !in_array($section, $sectionDAO->retrieveByCourse($code)) ){
        $errors[] = 'invalid section';
        $inputErrors ++;
    }

    //Checks if round has already ended
    if ($roundDAO->retrieveByRound('round 1') == 'not started' || $roundDAO->retrieveByRound('round 2') == 'not started' && $roundDAO->retrieveByRound('round 1') == 'ended'
     || (($roundDAO->retrieveByRound('round 1') == 'ended') && ($roundDAO->retrieveByRound('round 2') == 'ended')) ){
        $errors[] = 'round ended';
        $logicErrors ++;
    }

    //Only checks for the following if there is an (1) active bidding round, and (2) course, userid and section are valid 
    //and (3)the round is currently active
    else{
        if ( $bidDAO->retrieveSpecificBid($userid, $code, $section) == NULL && $inputErrors == 0){
            $errors[] = 'no such bid';
            $logicErrors ++;
        }

        //Successful case - bid is deleted and student is refunded e dollars accordingly
        elseif( $bidDAO->retrieveSpecificBid($userid, $code, $section) != NULL && $inputErrors == 0 ){
            $studentDAO->addEdollar($studentDAO->retrieveByID($userid), $bidDAO->retrieveSpecificBid($userid, $code, $section)->getAmount());
            $bidDAO->remove($userid, $code, $section);
            
        }

    }

    //Return success message if no errors
    if($logicErrors == 0 && $inputErrors == 0){
        $result = [
            "status" => "success",
        ];
        return ["status" => "success"
            ];
    }

    //If fail, return error messages
    else{
        $result = [
            "status" => "error",
            "message" => $errors
        ];
        foreach ($errors as $error){
            $_SESSION['errors'][] = $error;
        }
    }
    return $result;
}

?>