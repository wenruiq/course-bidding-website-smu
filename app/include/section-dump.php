<?php
require_once "common.php";

function dumpSection($course, $section){
    $errors = array();
    $results = array();
    $successfulBidsDAO = new successfulbidsDAO;
    $successfulBids2DAO = new successfulbids2DAO;
    $courseDAO = new courseDAO;
    $sectionDAO = new sectionDAO;
    
    
    # Check course section valid
    if(empty($courseDAO->retrieveByCourseID($course))){
        $errors[] = "invalid course";
    }
    elseif(!in_array($section, $sectionDAO->retrieveByCourse($course))){
        $errors[] = "invalid section";
    }
    else{
        $data = array_merge($successfulBidsDAO->retrieveByCourseSection($course, $section), $successfulBids2DAO->retrieveByCourseSection($course,$section));
        foreach($data as $value){
            $id = $value->getUserID();
            $amt = $value->getAmount();
            $results[] = array("userid" => $id, "amount" => $amt);
        }
    }

    # Populate output for json checker

    //If errors are present, output error messages
    if(!empty($errors)){
        $result = [
            "status" => "error",
            "message" => $errors
        ];
        $sortingArray = array();

        foreach ($result['message'] as $string){
            $sortingArray[] = explode(" ", $string)[0];
        }

        //Sort error messages by field name
        array_multisort($sortingArray, SORT_ASC, $result['message']);
    }

    //If no errors present, return success message and information
    else{
        
        //Sort results by alphabetical order of userids
        $their_id = array_column($results, "userid");
        array_multisort($their_id, SORT_ASC, $results);
        $result = [
            "status" => "success",
            "students" => $results
        ];
    }
    return $result;
   }
    



?>