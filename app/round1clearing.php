<?php
require_once "include/common.php";
require_once "include/protect.php";
require_once "include/stop.php";

$result = stop();


header("Location: adminpage.php?clear=round");

?>
