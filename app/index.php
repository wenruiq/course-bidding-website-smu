<?php
require_once "include/common.php";
require_once "include/protect.php";

$student_dao = new studentDAO;
$admin_dao = new adminDAO;
if($admin_dao->retrieveByID($username) != null){
    header("Location: adminpage.php");
    return;
}
else{
    header("Location: studentpage.php");
    return;
}


?>