<?php
// Auto load class 
spl_autoload_register(function($class){
    require_once "$class.php";    
});

// Start session
session_start();

// Print errors function
function print_errors() {
    if(isset($_SESSION['errors'])){
        echo "<ul id='errors' style='color:red;'>";
        
        foreach ($_SESSION['errors'] as $value) {
            echo "<li>" . $value . "</li>";
        }
        
        echo "</ul>";   
        unset($_SESSION['errors']);
    }    
}

// Json request common validations
    //Validates field without r= input
    function isMissingOrEmptyAuthenticate($field){
        if(!isset($_REQUEST[$field])){
            return "missing $field";
        }
        $value = $_REQUEST[$field];
        if(trim($value) == ''){
            return "blank $field";
        }
}
    //Validates field with r= input
    function isMissingOrEmpty($field){
        if(!isset(json_decode($_REQUEST['r'], true)[$field])){
            return "missing $field";
        }
        $value = json_decode($_REQUEST['r'], true)[$field];
        if(trim($value) == ''){
            return "blank $field";
        }
    }
    # Validate tokens
    function tokenCheck(){
        if(!isset($_REQUEST['token'])){
            return "missing token";
        }
        $token = $_REQUEST['token'];
        if (is_array($token)){
            $token = $token[0];
        }
        if(trim($token) == ''){
            return "blank token";
        }
        if(!verify_token($token)){
            return "invalid token";
        }
    }

    # this is better than empty when use with array, empty($var) returns FALSE even when
    # $var has only empty cells
    function isEmpty($var) {
        if (isset($var) && is_array($var))
            foreach ($var as $key => $value) {
                if (empty($value)) {
                unset($var[$key]);
                }
            }

        if (empty($var))
            return TRUE;
    }
?>