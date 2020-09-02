<?php
require_once '../include/common.php';
require_once '../include/token.php';

# Common validations for json requests
// Check for missing/blank fields
$errors = [
    isMissingOrEmptyAuthenticate("password"),
    isMissingOrEmptyAuthenticate("username")
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
}
else{
    // Request is valid
    $username = $_REQUEST['username'];
    $password = $_REQUEST['password'];
    $admin_dao = new adminDAO;
    $user = $admin_dao->retrieveByID($username);
    if($user != null){
        // username exists
        if($user->verify($password)){
            // password correct
            $token = generate_token($username);
            $result = [
                "status" => "success",
                "token" => $token
            ];
        }
        else{
            // password wrong
            $result = [
                "status" => "error",
                "message" => ["invalid password"]
            ];
        }
    }
    else{
        // username doesn't exist
        $result = [
            "status" => "error",
            "message" => ["invalid username"]
        ];
    }
}
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
 
?>