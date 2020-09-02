<?php
require_once 'include/common.php';
require_once 'include/protect.php';
?>

<html>

<head>
</head>

<body>
    <form id="bootstrap-form" class="bootstrap_form" action="bootstrap_result.php" method="post"
        enctype="multipart/form-data">
        <h2>Bootstrap file:</h2>
        <input id="bootstrap-file" class="bootstrap_form" type="file" name="bootstrap-file" required>
        <input type="submit" class="bootstrap_form" name="import" value="Import">
    </form>
    <br>
    <form action="adminpage.php" method="post">
        <input type="submit" class="home_button" name="home" value="Return to admin home page">
    </form>
</body>

</html>