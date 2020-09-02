<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once "../include/dump-section.php";

# Common validations for json requests
// Check for missing/blank fields
$errors = [
    isMissingOrEmpty("course"),
    isMissingOrEmpty("section"),
    tokenCheck()
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
    $course = json_decode($_REQUEST['r'],true)['course'];
    $section = json_decode($_REQUEST['r'],true)['section'];
    $result = dumpSection($course, $section);
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
}





?>