<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once "../include/user-dump.php";

# Common validations for json requests
// Check for missing/blank fields


$errors = [
    isMissingOrEmpty("userid"),
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
    $userid = json_decode($_REQUEST['r'],true)['userid'];
    $result = dumpUser($userid);
    
    if ($result['status'] == 'success'){
        $edollar =  $result['edollar'];
        $json = json_encode($result, JSON_PRETTY_PRINT);
        $explodeEdollar = explode(".", $edollar);

        // If edollar has no cents
        if ($explodeEdollar[1] == '00'){
            $json = str_replace('"edollar": "'.$edollar.'"', '"edollar": '.intval($edollar).'.0',$json);
        }

        // If edollar has only 1 decimal place
        elseif (substr($explodeEdollar[1],1) == '0'){
            $amount = round($result['edollar'],1);
            $json = str_replace('"edollar": "'.$result['edollar'].'"', '"edollar": '."$amount",$json);
        }

        // If edollar has 2 decimal places
        else{
            $amount = floatval($result['edollar']);
            $json = str_replace('"edollar": "'.$result['edollar'].'"', '"edollar": '."$amount",$json);
        }
        header('Content-Type: application/json');
        echo"$json";
    }

    // If output is fail
    else{
        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
}





?>