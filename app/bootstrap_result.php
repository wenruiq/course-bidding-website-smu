<?php
require_once "include/common.php";
require_once "include/protect.php";
require_once "include/bootstrap.php";

$data = doBootstrap();

$status = $data['status'];
if($status == "error"){
    $status = "<font color='red'>unsuccessful</font>.";
}
else{
    $status = "<font color='green'>successful</font>! Round 1 started automatically.";
}

?>

<html>

<head>
</head>

<body>

    <h2>Bootstrap is <?=$status?></h2>

    <table border='1' class="default">
        <tr>
            <th align='left'>File name</th>
            <th>Number of records loaded</th>
        </tr>
        <?php
    foreach($data['num-record-loaded'] as $item){
        foreach($item as $key=>$value)
        echo"
        <tr>
            <td>$key</td>
            <td align=center>$value</td>
        </tr>";
    }
    ?>
    </table>
    <hr>
    <form action="./adminpage.php">
        <input type="submit" value="Return to admin home page">
    </form>
    <form action="./bootstrap_page.php">
        <input type="submit" value="Bootstrap another file">
    </form>
    <?php
if($status == "<font color='red'>unsuccessful</font>."){
    echo"
    <h2> Error(s) in bootstrap file: </h2>
    <table border='1' class='default'>
        <tr>
            <th align='left'>File name</th>
            <th>Line</th>
            <th>Error(s)</th>
        <tr>";
    foreach($data['error'] as $error){
        $messages = implode(", ", $error['message']);
        echo"
        <tr>
            <td>{$error['file']}</td>
            <td>{$error['line']}</td>
            <td>$messages</td>
        </tr>";
    }
    echo"
    </table>";
}
?>


</body>

</html>