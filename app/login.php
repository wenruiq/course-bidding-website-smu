<?php
require_once "include/common.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/login.css">
    <title>Merlion University | Login</title>
</head>

<body>
    <div class="bg">
        <form class="box" action="process_login.php" method="post">
            <h1>BIOS | Login</h1>
            <input type="text" name="username" placeholder="Username">
            <input type="password" name="password" placeholder="Password">
            <input type="submit" name="" value="Login">
            <?php
            if(isset($_SESSION['errors'])){
                echo"
                <ul>";
                foreach($_SESSION['errors'] as $value){
                    echo"
                    <li>$value</li>";
                }
                echo"
                </ul>";
                unset($_SESSION['errors']);
            }
            ?>
        </form>
    </div>

</body>

</html>