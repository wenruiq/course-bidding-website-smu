<?php
require_once "include/common.php";
require_once "include/token.php";

$errors = array();
$student_dao = new studentDAO;
$admin_dao = new adminDAO;

if(isset($_POST['username']) && isset($_POST['password'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
}
else{
    // prevent access to this page without logging in
    header("Location: login.php");
    exit;
}

# Check if the user is admin, and if the credentials are correct
$user = $admin_dao->retrieveByID($username);
if($user != null){
    // admin username exists in database
    if($user->verify($password)){
        // admin username & password are correct
        $_SESSION['username'] = $username;
        header("Location: adminpage.php");
        exit;
    }
    else{
        // password is wrong
        $errors[] = "invalid password";
        $_SESSION['errors'] = $errors;
        header("Location: login.php");
        exit;
    }
}
else{
    # Process as student
    $user = $student_dao->retrieveByID($username);
    if($user != null){
        // username exists in database
        if($user->verify($password)){
            // username & password are correct
            $_SESSION['username'] = $username;
            header("Location: studentpage.php");
            exit;
        }
        else{
            // password is wrong
            $errors[] = "invalid password";
            $_SESSION['errors'] = $errors;
            header("Location: login.php");
            exit;
        }
    }
    else{
        // username doesn't exist in database
        $errors[] = "invalid username/password";
        $_SESSION['errors'] = $errors;
        header("Location: login.php");
        exit;

    }
}
?>