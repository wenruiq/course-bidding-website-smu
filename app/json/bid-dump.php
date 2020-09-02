<?php
require_once '../include/common.php';
require_once '../include/token.php';
require_once "../include/bid-dump.php";

# Common validations for json requests
// Check for missing/blank fields
$errors = [
    isMissingOrEmpty("course"),
    isMissingOrEmpty("section"),
    tokenCheck()
];

// Remove empty values from array
$errors = array_filter($errors); 

//Output error messages for field validation
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
    $result = dumpBid($course, $section);

    // If output is success
    if ($result['status'] == 'success'){
        $json = json_encode($result, JSON_PRETTY_PRINT);

        foreach ($result['bids'] as $item){
            $explodeamount = explode(".", $item['amount']);

            // Replace if amount has no cents
            if ($explodeamount[1] == '00'){
                $json = str_replace('"amount": "'.$item['amount'].'"', '"amount": '.intval($item['amount']).'.0',$json);
            }

            // Replace if amount only has 1 decimal place
            elseif (substr($explodeamount[1],1) == '0'){
                $amount = round($item['amount'],1);
                $json = str_replace('"amount": "'.$item['amount'].'"', '"amount": '."$amount",$json);
            }

            // Replace if amount only has 2 decimal places
            else{
                $amount = floatval($item['amount']);
                $json = str_replace('"amount": "'.$result['amount'].'"', '"amount": '."$amount",$json);
            }
           
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