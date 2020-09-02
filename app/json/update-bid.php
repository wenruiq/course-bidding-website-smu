<?php
require_once "../include/common.php";
require_once '../include/token.php';
require_once "../include/update-bid.php";

# Common valiations for json requests
// Check token
$errors = [
    isMissingOrEmpty("userid"),
    isMissingOrEmpty("amount"),
    isMissingOrEmpty("course"),
    isMissingOrEmpty("section"),
    // tokenCheck()
];

$errors = array_filter($errors); // Remove empty values from array


if(!isEmpty($errors)){
    // Request did not pass common validations
    $result = [
        "status" => "error",
        "message" => array_values($errors)
    ];

    // Sort error messages by field alphabetical order
    $sortingArray = array();
    foreach ($result['message'] as $indError){
        $sortingArray[] = explode(" ", $indError)[1];
    }

    array_multisort($sortingArray, SORT_ASC, $result['message']);

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
}
else{
    $userid = json_decode($_REQUEST['r'],true)['userid'];
    $amount = json_decode($_REQUEST['r'],true)['amount'];
    $course = json_decode($_REQUEST['r'],true)['course'];
    $section = json_decode($_REQUEST['r'],true)['section'];
    
    $result =  update($userid, $amount, $course, $section);
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
};

?>