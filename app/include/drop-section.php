<?php
require_once "common.php";

function drop($userid, $code, $section){
    $successfulBidsDAO = new successfulbidsDAO;
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
        $errors[] = "invalid userid";
        $inputErrors ++;
    }

    //Checks for invalid section
    if( !empty($courseDAO->retrieveByCourseID($code)) && !in_array($section, $sectionDAO->retrieveByCourse($code)) ){
        $errors[] = 'invalid section';
        $inputErrors ++;
    }

    //Checks for inactive round
    if ($roundDAO->retrieveByRound('round 1') != 'started' && $roundDAO->retrieveByRound('round 2') != 'started'){
        $errors[] = "round not active";
        $logicErrors ++;
    }

   //If no errors and bid exists, drop bid and refund user edollar
   else{
    if ( $successfulBidsDAO->retrieveSpecificSuccessfulBid($userid, $code, $section) == NULL && $inputErrors == 0){
        $errors[] = "no such enrollment record";
        $logicErrors ++;
    }
    elseif( $successfulBidsDAO->retrieveSpecificSuccessfulBid($userid, $code, $section) != NULL && $inputErrors == 0 ){
        $studentDAO->addEdollar($studentDAO->retrieveByID($userid), $successfulBidsDAO->retrieveSpecificSuccessfulBid($userid, $code, $section)->getAmount());
        $successfulBidsDAO->remove($userid, $code, $section);
        
    }
   }

    //If no errors, return success messages
    if($logicErrors == 0 && $inputErrors == 0){
        $result = [
            "status" => "success",
        ];
        return ["status" => "success"
            ];
    }

    //If errors present, print error messages
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