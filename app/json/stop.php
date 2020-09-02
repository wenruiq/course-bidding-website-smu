<?php
// Project requirements doesn't consider the case when both rounds haven't been started
require_once "../include/common.php";
require_once "../include/token.php";
require_once "../include/stop.php";

# Common valiations for json requests
// Check token
$errors = [tokenCheck()];
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
    $result = stop();
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
};

?>