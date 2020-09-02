<?php
require_once "../include/common.php";
require_once "../include/token.php";
require_once "../include/bootstrap.php";

# Common valiations for json requests
$errors = array();
// Check if file exists
if(!isset($_FILES["bootstrap-file"])){
    $errors[] = "missing bootstrap-file";
}
else{
    // Check if file is blank
    $zip_file = $_FILES["bootstrap-file"]["tmp_name"];
    $temp_dir = sys_get_temp_dir();
    if($_FILES["bootstrap-file"]["size"] <= 0){
        $errors[] = "blank bootstrap-file";
    }
}
// Check token
$errors[] = tokenCheck();
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
    $result = doBootstrap();
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
}

?>