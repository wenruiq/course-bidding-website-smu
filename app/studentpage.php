<?php
// spl_autoload_register
require_once "include/common.php";
// no access to this page without login ($username = $_SESSION['username'] with login)
require_once "include/protect.php";
// round2 clearing function displayLive($code, $biddedSection)
require_once "include/round2clearing.php";
// required function files
require_once "include/update-bid.php"; //update(username, amount, code, section)
require_once "include/delete-bid.php"; //delete(username, code, section)
require_once "include/drop-section.php"; //drop(username, code, section)

// process update/delete/drop bid for studentpage
$modify_bids_msg= array();
if(isset($_SESSION['msg'])){
    $modify_bids_msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}
if(isset($_POST['modify_bids'])){
    if($_POST['modify_bids'] == "Confirm"){
        $update_count = $_POST['update_count'];
        $drop_indexes = [];
        $delete_indexes = [];
        if(isset($_POST["drop_section"])){
            $drop_indexes = $_POST['drop_section'];
        }
        if(isset($_POST["delete_bid"])){
            $delete_indexes = $_POST['delete_bid'];
        }
        for($i=1; $i<$update_count; $i++){
            $update_code = $_POST["update_code$i"];
            $update_section = $_POST["update_section$i"];
            $update_amount = $_POST["update_amount$i"];
            $no_update = true;
            if(isset($_POST["original_code$i"]) && isset($_POST["original_section$i"]) && isset($_POST["original_amount$i"])){
                $original_code = $_POST["original_code$i"];
                $original_section = $_POST["original_section$i"];
                $original_amount = $_POST["original_amount$i"];
                if($update_code != $original_code || $update_section != $original_section || $update_amount != $original_amount){
                    $no_update = false;
                }
            }
            if(!$no_update || in_array($i, $delete_indexes) || in_array($i, $drop_indexes)){
                $modify_bids_msg["$update_code"] = array();
                if(in_array($i, $delete_indexes)){
                    $delete_msg = delete($username, $update_code, $update_section);
                    if($delete_msg['status'] == "success"){
                        $modify_bids_msg["$update_code"][] = "Bid Deleted";
                    }
                    elseif($delete_msg['status'] == "error"){
                        foreach($delete_msg["message"] as $err){
                            $modify_bids_msg["$update_code"][] = $err;
                        }
                    }
                }
                elseif(in_array($i, $drop_indexes)){
                    $drop_msg = drop($username, $update_code, $update_section);
                    if($drop_msg['status'] == "success"){
                        $modify_bids_msg["$update_code"][] = "Section Dropped";
                    }
                    elseif($drop_msg['status'] == "error"){
                        foreach($drop_msg["message"] as $err){
                            $modify_bids_msg["$update_code"][] = $err;
                        }
                    }
                }
                else{
                    $update_msg = update($username, $update_amount, $update_code, $update_section);
                    if($update_msg['status'] == "success"){
                        $modify_bids_msg["$update_code"][] = "Bid Updated";
                    }
                    elseif($update_msg['status'] == "error"){
                        foreach($update_msg["message"] as $err){
                            $modify_bids_msg["$update_code"][] = $err;
                        }
                    }
                }
            }
        }
    }
}

// RETRIEVE DATA
// student info
$studentDAO = new studentDAO;
$user = $studentDAO->retrieveByID($username);
if(empty($user)){
    header("Location: login.php");
    exit;
}
$student_name = $user->getName();
$student_school = $user->getSchool();
$student_edollar = $user->getEdollar();

// course & section info
$courseDAO = new courseDAO;
$course_list = $courseDAO->retrieveAll();
$sectionDAO = new sectionDAO;
$section_list = $sectionDAO->retrieveAll();

// course completed info
$course_completedDAO = new course_completedDAO;
$student_course_completed = $course_completedDAO->retrieveCoursesCompletedByID($username); // course completed by this student

// prereq info
$prerequisiteDAO = new prerequisiteDAO;

// bid info
$bidDAO = new bidDAO;
$successfulbidsDAO = new successfulbidsDAO;
$successfulbids2DAO = new successfulbids2DAO;
$bidsrejectedDAO = new bidsrejectedDAO;
$bidsrejected2DAO = new bidsrejected2DAO;
$minimum_bid_valueDAO = new minimum_bid_valueDAO;
$all_current_bids = $bidDAO->retrieveAll(); // all bids made in current round
$all_successful_bids1 = $successfulbidsDAO->retrieveAll();
$all_successful_bids2 = $successfulbids2DAO->retrieveAll();
$all_successful_bids = array_merge($all_successful_bids1, $all_successful_bids2); // all successful bids in all rounds
$all_rejected_bids1 = $bidsrejectedDAO->retrieveAll();
$all_rejected_bids2 = $bidsrejected2DAO->retrieveAll();
$all_rejected_bids = array_merge($all_rejected_bids1, $all_rejected_bids2); // all rejected bids in all rounds
$student_current_bids = $bidDAO->retrieveSpecific($username); // bids made by this student in current round
$student_successful_bids1 = $successfulbidsDAO->retrieveSpecific($username);
$student_successful_bids2 = $successfulbids2DAO->retrieveSpecific($username);
$student_successful_bids = array_merge($student_successful_bids1, $student_successful_bids2); // successful bids made by this student in previous rounds
$student_rejected_bids1 = $bidsrejectedDAO->retrieveSpecific($username);
$student_rejected_bids2 = $bidsrejected2DAO->retrieveSpecific($username);
$student_rejected_bids = array_merge($student_rejected_bids1, $student_rejected_bids2); // rejected bids made by this student in previous round(s)
$student_all_bids = array_merge($student_current_bids, $student_successful_bids, $student_rejected_bids); // bids made by this student in all rounds
$student_all_bids_round2 = array_merge($student_current_bids, $student_successful_bids);

// min bid info
$minimum_bid_ValueDAO = new minimum_bid_valueDAO;


// round info
$roundDAO = new roundDAO;
$round1_status = $roundDAO->retrieveByRound("round 1");
$round2_status = $roundDAO->retrieveByRound("round 2");

// functions

// check status of any bid object
function bid_status($bidObj){
    // bidObj info
    $course = $bidObj->getCode();
    $section = $bidObj->getSection();
    $amount = $bidObj->getAmount();
    // round & database info
    $roundDAO = new roundDAO;
    $bidDAO = new bidDAO;
    $round1_status = $roundDAO->retrieveByRound("round 1");
    $round2_status = $roundDAO->retrieveByRound("round 2");
    $successfulbidsDAO = new successfulbidsDAO;
    $bidsrejectedDAO = new bidsrejectedDAO;
    $successfulbids2DAO = new successfulbids2DAO;
    $bidsrejected2DAO = new bidsrejected2DAO;
    $all_successful_bids = $successfulbidsDAO->retrieveAll();
    $all_rejected_bids = $bidsrejectedDAO->retrieveAll();
    $all_successful_bids2 = $successfulbids2DAO->retrieveAll();
    $all_rejected_bids2 = $bidsrejected2DAO->retrieveAll();
    // round 1 logic
    if($round1_status == "started"){
        return "Pending";
    }
    elseif($round1_status == "ended"){
        if(in_array($bidObj, $all_successful_bids)){
            return "Success";
        }
        elseif(in_array($bidObj, $all_rejected_bids)){
            return "Fail";
        }
        // round 2 logic
        elseif($round2_status == "started" || $round2_status == "ended"){

            if (!empty($bidDAO->retrieveByCourseSection($course, $section))){
                $round2_results = displayLive($course, $section);
                foreach($round2_results['liveArray'] as $key => $value){
                    if($value['State'] == "Unsuccessful" || $value['State'] == "Unsuccessful. Bid too low."){

                        if($amount <= $value['Bid Price']){
                            return "Fail";
                        }
                    }

                }
                return "Success";
            }
            else{
                if(in_array($bidObj, $all_successful_bids2)){
                    return "Success";
                }
                elseif(in_array($bidObj, $all_rejected_bids2)){
                    return "Fail";
                }
            }
        }
    }
}

// process course search
$section_list_filtered = array();
if(isset($_POST['confirm-search'])){
    $section_list_filtered = $sectionDAO->retrieveSectionsByCourse($_POST['search-code']);
}
if(isset($_POST['clear-search'])){
    $section_list_filtered = $section_list;
}
$day_list = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
$time_list = ['08:30 - 11:45','12:00 - 15:15','15:30 - 18:45'];

// process add bid
if(isset($_POST['shop_confirm'])){
    $msg = array();
    $count = $_POST['count'];
    for($i=1; $i<=$count; $i++){
        if($_POST["shop_amount$i"] != ""){
            $shop_code = $_POST["shop_code$i"];
            $shop_section = $_POST["shop_section$i"];
            $shop_amount = $_POST["shop_amount$i"];
            $msg["$shop_code"] = array();
            $shop_msg = update($username, $shop_amount, $shop_code, $shop_section);
            if($shop_msg['status'] == "success"){
                $msg["$shop_code"][] = "Bid Added Successfully";
            }
            elseif($shop_msg['status'] == "error"){
                foreach($shop_msg['message'] as $err){
                    $msg["$shop_code"][] = $err;
                }
            }
            $_SESSION['msg'] = $msg;
            header("Location: studentpage.php");
            return;
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/s_studentpage.css">
    <title>BIOS | Bidding Online System</title>
</head>

<body>
    <!-- Wrapper -->
    <div class="wrapper">
        <!-- Nav bar -->
        <div class="nav-bar">
            <!-- Website info -->
            <div class="website-info">
                <a href="studentpage.php"><img src="img/merlion.png"></a>
                <span class="school-name">Merlion University</span>
                <span class="website-name">Bidding Online System</span>
            </div>
            <!-- Round info -->
            <div class="round-info">
                <div class="round-1"><b>Round 1:</b> <span class="round-1-status"><?=$round1_status?></span></div>
                <div class="round-2"><b>Round 2:</b> <span class="round-2-status"><?=$round2_status?></span></div>
            </div>
            <!-- User info -->
            <div class="user-info">
                <div class="welcome-msg"><b>Welcome, <?=$student_name?>!</b></div>
                <div class="edollar-msg">
                    Credit Balance: <span class="edollar"><?=$student_edollar?></span>
                </div>
                <!-- Nav buttons -->
                <div class="nav-btns">
                    <a href="logout.php" class="nav-btn">Logout</a>
                </div>
            </div>

        </div>
        <!-- Main container -->
        <div class="main-container">
            <!-- Time info -->
            <div class="def-box time-info">
                <div class="time-title">Timetable</div>

                <div class="time-table">
                    <table class="time-table">
                        <thead>
                            <tr>
                                <th></th>
                                <?php
                                foreach($time_list as $timeslot){
                                    echo"<th>$timeslot</th>";
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                        if(!empty($student_all_bids)){
                            for ($i=1; $i<=count($day_list); $i++){
                                echo"<tr>
                                    <th>{$day_list[$i-1]}</th>
                                    ";
                        
                                //Iterate through each timeslot and match it against the start time of each bids
                                foreach ($time_list as $timeslot){
                                    
                                    $match = 0;
                        
                                    
                                    foreach ($student_all_bids as $bid){
                                        $sectionObj = $sectionDAO->retrieveByCourseSection($bid->getCode(), $bid->getSection());
                                        if ($sectionObj->getDay() == $i){
                                                $starttime = explode(" ",$timeslot)[0];
                        
                                                if ($starttime == substr($sectionObj->getStart(), 0, -3)){
                                                    $courseTitle = $courseDAO->retrieveByCourseID($sectionObj->getCourse())->getTitle();
                        
                                                    if(bid_status($bid) == "Pending"){
                                                        echo"<td bgcolor='#F7DC6F'>{$sectionObj->getCourse()} ({$sectionObj->getSection()})</td>";
                                                    }
                        
                                                    elseif(bid_status($bid) == "Success"){
                                                        echo"<td bgcolor='#58D68D'>{$sectionObj->getCourse()} ({$sectionObj->getSection()})</td>";
                                                    }
                                                    $match += 1;
                        
                                                }
                                            }
                                        }
                                        
                                    if ($match == 0){
                                        echo"<td></td>";
                                    }
                                    
                                    }
                        
                                    $match = 0;
                        
                                    
                                    echo"</tr>";
                            }
                        }
                        else{
                            for ($i=1; $i<=count($day_list); $i++){
                                echo"<tr>
                                        <th>{$day_list[$i-1]}</th>
                                        <td><td>
                                        <td><td>
                                        <td><td>
                                    <tr>";
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- End of exam info -->

            <!-- Bidding results -->
            <div class="def-box bidding-results">
                <div class="bidding-title">Bidding Results</div>
                <div class="bidding-results-area">
                    <form action="studentpage.php" method="post">
                        <table class="def-table">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Section</th>
                                    <?php
                                    if($round2_status != "ended"){
                                        echo"<th> Min Bid</th>";
                                    }
                                    ?>
                                    <th>Bid Amount</th>
                                    <th>Status</th>
                                    <?php
                                if(isset($_POST['modify_bids'])){
                                    if($_POST['modify_bids'] == "Modify Bids"){
                                        echo"<th>Drop Bid</th>";
                                    }
                                }

                                ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $min_bid = number_format(10.00, 2);
                            // @@ Normal table @@
                            if(!isset($_POST['modify_bids']) || $_POST['modify_bids'] == "Confirm"){
                                // r1n r2n (BEFORE BOOTSTRAP)
                                if($round1_status == "not started" || empty($student_all_bids)){
                                    echo"
                                    <tr>
                                        <td colspan='4'>You have not placed any bids</td>
                                    <tr></tbody></table>
                                    <input type='submit' name='modify_bids' value='Modify Bids' disabled>";
                                }
                                // r1s r2n (ROUND 1)
                                elseif($round1_status == "started"){
                                    foreach($student_current_bids as $student_current_bid){
                                        $course_code = $student_current_bid->getCode();
                                        $course_obj = $courseDAO->retrieveByCourseID($course_code);
                                        $bid_status = bid_status($student_current_bid);
                                        echo"
                                        <tr>
                                            <td>{$course_code} - {$course_obj->getTitle()}</td>
                                            <td>{$student_current_bid->getSection()}</td>
                                            <td>$min_bid</td>
                                            <td>{$student_current_bid->getAmount()}</td>
                                            <td class='$bid_status'>$bid_status"." (Round 1)</td>
                                        <tr>";
                                    }
                                    echo"
                                    </tbody></table>
                                    <div id='modify-btn'><input type='submit' name='modify_bids' value='Modify Bids'></div>";
                                }
                                // r1e r2n (ROUND 1 ENDED)
                                elseif($round1_status == "ended" && $round2_status == "not started"){
                                    foreach($student_all_bids as $student_all_bid){
                                        $course_code = $student_all_bid->getCode();
                                        $course_obj = $courseDAO->retrieveByCourseID($course_code);
                                        $bid_status = bid_status($student_all_bid);
                                        echo"
                                        <tr>
                                            <td>{$course_code} - {$course_obj->getTitle()}</td>
                                            <td>{$student_all_bid->getSection()}</td>
                                            <td>$min_bid</td>
                                            <td>{$student_all_bid->getAmount()}</td>
                                            <td class='$bid_status'>$bid_status"." (Round 1)</td>
                                        <tr>";
                                    }
                                    echo"
                                    </tbody></table>
                                    <input type='submit' name='modify_bids' value='Modify Bids' disabled>";
                                }
                                // r1e r2s (ROUND 2)
                                elseif($round2_status == "started"){
                                    foreach($student_all_bids_round2 as $student_all_bid_round2){
                                        $course_code = $student_all_bid_round2->getCode();
                                        $course_obj = $courseDAO->retrieveByCourseID($course_code);
                                        $bid_section = $student_all_bid_round2->getSection();
                                        if($minimum_bid_valueDAO->retrieveSpecificValue($course_code, $bid_section) == []){
                                            $min_bid = number_format(10.00, 2);
                                        }
                                        else{
                                            $min_bid = $minimum_bid_valueDAO->retrieveSpecificValue($course_code, $bid_section);
                                        }
                                        $bid_status = bid_status($student_all_bid_round2);
                                        $placed_in_round = " (Round 1)";
                                        if(in_array($student_all_bid_round2, $student_current_bids)){
                                            $placed_in_round = " (Round 2)";
                                        }
                                        echo"
                                        <tr>
                                            <td>{$course_code} - {$course_obj->getTitle()}</td>
                                            <td>$bid_section</td>
                                            <td>$min_bid</td>
                                            <td>{$student_all_bid_round2->getAmount()}</td>
                                            <td class='$bid_status'>$bid_status $placed_in_round</td>
                                        <tr>";
                                    }
                                    echo"
                                    </tbody></table>
                                    <input type='submit' name='modify_bids' value='Modify Bids'>";
                                }
                                // r1e r2e (ROUND 2 ENDED)
                                elseif($round2_status == "ended"){
                                    foreach($student_successful_bids as $student_all_bid_round2){
                                        $course_code = $student_all_bid_round2->getCode();
                                        $course_obj = $courseDAO->retrieveByCourseID($course_code);
                                        $bid_status = bid_status($student_all_bid_round2);
                                        $placed_in_round = " (Round 1)";
                                        if(in_array($student_all_bid_round2, $all_successful_bids2)){
                                            $placed_in_round = " (Round 2)";
                                        }
                                        echo"
                                        <tr>
                                            <td>{$course_code} - {$course_obj->getTitle()}</td>
                                            <td>{$student_all_bid_round2->getSection()}</td>
                                            <td>{$student_all_bid_round2->getAmount()}</td>
                                            <td class='$bid_status'>$bid_status"."$placed_in_round</td>
                                        <tr>
                                        ";
                                    }
                                    echo"
                                    </tbody></table>
                                    <input type='submit' name='modify_bids' value='Modify Bids' disabled>";
                                }
                            }
                            // @@ Modify Table @@
                            elseif(isset($_POST['modify_bids']) && $_POST['modify_bids'] == "Modify Bids"){
                                // r1s r2n (ROUND 1)
                                if($round1_status == "started"){
                                    $update_count = 1;
                                    foreach($student_current_bids as $student_current_bid){
                                        $student_current_bid_amount = $student_current_bid->getAmount();
                                        $student_current_bid_section = $student_current_bid->getSection();
                                        $course_code = $student_current_bid->getCode();
                                        $course_obj = $courseDAO->retrieveByCourseID($course_code);
                                        $section_arr = $sectionDAO->retrieveByCourse($course_code);
                                        if($minimum_bid_valueDAO->retrieveSpecificValue($course_code, $student_current_bid_section) == []){
                                            $min_bid = number_format(10.00, 2);
                                        }
                                        else{
                                            $min_bid = $minimum_bid_valueDAO->retrieveSpecificValue($course_code, $student_current_bid_section);
                                        }
                                        $bid_status = bid_status($student_current_bid);
                                        $delete_or_drop = "delete_bid[]";
                                        if(in_array($student_current_bid, $student_successful_bids)){
                                            $delete_or_drop = "drop_section[]";
                                        }
                                        echo"
                                        <tr>
                                            <td>{$course_code} - {$course_obj->getTitle()}</td>
                                            <input type='hidden' name='update_code$update_count' value='$course_code'>
                                            <td><select name='update_section$update_count'>";
                                            foreach($section_arr as $section){
                                                if($section == $student_current_bid_section){
                                                    echo"<option value='$section' selected>$section</option>";
                                                }
                                                else{
                                                    echo"<option value='$section'>$section</option>";
                                                }
                                            }
                                        echo"</select></td>
                                            <td>$min_bid</td>
                                            <td><input type='number' step='0.01' name='update_amount$update_count' value='$student_current_bid_amount'></td>
                                            <td class='$bid_status'>$bid_status (Round 1)</td>
                                            <td><input type='checkbox' name='$delete_or_drop' value='$update_count'></td><tr>";
                                        echo"<input type='hidden' name='original_code$update_count' value='$course_code'>";
                                        echo"<input type='hidden' name='original_section$update_count' value='$student_current_bid_section'>";
                                        echo"<input type='hidden' name='original_amount$update_count' value='$student_current_bid_amount'>";
                                        $update_count++;
                                    }
                                    echo"
                                    </tbody></table>
                                    <input type='hidden' name='update_count' value='$update_count'>
                                    <input type='submit' name='modify_bids' id='modify-btn' value='Confirm'></form>";
                                }
                                // r1e r2s (ROUND 2)
                                elseif($round2_status == "started"){
                                    $update_count = 1;
                                    foreach($student_all_bids_round2 as $student_current_bid){
                                        $student_current_bid_amount = $student_current_bid->getAmount();
                                        $student_current_bid_section = $student_current_bid->getSection();
                                        $course_code = $student_current_bid->getCode();
                                        $course_obj = $courseDAO->retrieveByCourseID($course_code);
                                        $section_arr = $sectionDAO->retrieveByCourse($course_code);
                                        if($minimum_bid_valueDAO->retrieveSpecificValue($course_code, $student_current_bid_section) == []){
                                            $min_bid = number_format(10.00, 2);
                                        }
                                        else{
                                            $min_bid = $minimum_bid_valueDAO->retrieveSpecificValue($course_code, $student_current_bid_section);
                                        }
                                        $placed_in_round = " (Round 1)";
                                        if(in_array($student_current_bid, $student_current_bids)){
                                            $placed_in_round = " (Round 2)";
                                        }
                                        $bid_status = bid_status($student_current_bid);
                                        $delete_or_drop = "delete_bid[]";
                                        if(in_array($student_current_bid, $student_successful_bids)){
                                            $delete_or_drop = "drop_section[]";
                                        }
                                        echo"
                                        <tr>
                                            <td>{$course_code} - {$course_obj->getTitle()}</td>
                                            <input type='hidden' name='update_code$update_count' value='$course_code'>
                                            <td><select name='update_section$update_count'>";
                                            foreach($section_arr as $section){
                                                if($section == $student_current_bid_section){
                                                    echo"<option value='$section' selected>$section</option>";
                                                }
                                                else{
                                                    echo"<option value='$section'>$section</option>";
                                                }
                                            }
                                        echo"</select></td>
                                            <td>$min_bid</td>
                                            <td><input type='number' step='0.01' name='update_amount$update_count' value='$student_current_bid_amount'></td>
                                            <td class='$bid_status'>$bid_status"."$placed_in_round</td>
                                            <td><input type='checkbox' name='$delete_or_drop' value='$update_count'></td><tr>";
                                            echo"<input type='hidden' name='original_code$update_count' value='$course_code'>";
                                            echo"<input type='hidden' name='original_section$update_count' value='$student_current_bid_section'>";
                                            echo"<input type='hidden' name='original_amount$update_count' value='$student_current_bid_amount'>";
                                        $update_count++;
                                    }
                                    echo"
                                    </tbody></table>
                                    <input type='hidden' name='update_count' value='$update_count'>
                                    <input type='submit' name='modify_bids' value='Confirm'>";
                                }
                            }
                            ?>
                    </form>
                </div>
                <!-- Messages after modifying bid -->
                <div class="modify-bids-msg">
                    <ul>
                        <?php
                    if(!empty($modify_bids_msg)){
                        foreach($modify_bids_msg as $key=>$value){
                            echo"<li>$key - ";
                            $last = count($value) - 1;
                            for($i=0; $i<count($value); $i++){
                                echo ucwords($value[$i]);
                                if($i != $last){
                                    echo ", ";
                                }
                            }
                            echo"</li>";
                        }
                    }
                    ?>
                        <ul>
                </div>
            </div>
            <!-- End of bidding-results -->

            <!-- course-info -->
            <div class="def-box course-info">
                <div class="course-info-title">Course Search</div>

                <!-- course-search -->
                <div class="course-search">
                    <form action="studentpage.php" method="post">
                        <input type="text" name="search-code" placeholder="Course Code">
                        <input type="submit" name="confirm-search" value="Search">
                        <input type="submit" name="clear-search" value="Clear Search">
                    </form>
                    <hr>
                </div>
                <!-- End of course-search -->

                <!-- course-list -->
                <div class="course-list">
                    <form action="studentpage.php" method="post">
                        <div class="table-wrapper">
                            <div class="table-scroll">
                                <table class="def-table">
                                    <thead>
                                        <tr>
                                            <th><span class="th">Course</span></th>
                                            <th><span class="th">Section</span></th>
                                            <th><span class="th">Day</span></th>
                                            <th><span class="th">Start</span></th>
                                            <th><span class="th">End</span></th>
                                            <th><span class="th">Vacancy</span></th>
                                            <th><span class="th">Min Bid</span></th>
                                            <th><span class="th">Amount</span></th>
                                            <th><span class="th"></span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $add_bid_disabled = 'disabled';
                                        if($round1_status == "started" || $round2_status == "started"){
                                            $add_bid_disabled = '';
                                        }
                                    if(!isset($_POST['confirm-search'])){
                                        $section_list_filtered = $section_list;
                                    }
                                    if($round1_status == 'started'){
                                        $biddable_courses = $courseDAO->retrieveCourseBySchool($student_school);
                                    }
                                    if(!empty($section_list_filtered)){
                                        $count = 1;
                                        foreach($section_list_filtered as $data){
                                            $day_index = $data->getDay() - 1;
                                            $shop_day = $day_list[$day_index];
                                            $shop_code = $data->getCourse();
                                            $shop_section = $data->getSection();
                                            $shop_start = $data->getStart();
                                            $shop_start = substr($shop_start, 0 , 5);
                                            $shop_end = $data->getEnd();
                                            $shop_end = substr($shop_end, 0 , 5);
                                            $shop_size = $data->getSize();
                                            $shop_minimum_bid_value = $minimum_bid_valueDAO->retrieveSpecificValue($shop_code, $shop_section);
                                            $shop_instructor = $data->getInstructor();
                                            // if no bids is placed for the section in round 2
                                            if($shop_minimum_bid_value == []){
                                                $shop_minimum_bid_value = number_format(10.00, 2);
                                            }
                                            // vacancy calculation
                                            $r1_success_count = $successfulbidsDAO->count($shop_code, $shop_section);
                                            $shop_vacancy = $shop_size - ($r1_success_count);
                                            // round 1 disabling of other school courses
                                            if($round1_status == 'started'){
                                                if(!in_array($shop_code, $biddable_courses)){
                                                    $add_bid_disabled = 'disabled';
                                                }
                                                else{
                                                    $add_bid_disabled = '';
                                                }
                                            }


                                            echo"
                                            <tr>
                                                <td>$shop_code</td>
                                                <td>$shop_section</td>
                                                <td>$shop_day</td>
                                                <td>$shop_start</td>
                                                <td>$shop_end</td>
                                                <td>$shop_vacancy</td>
                                                <td>$shop_minimum_bid_value</td>
                                                <input type='hidden' name='count' value='$count'>
                                                <input type='hidden' name='shop_code$count' value='$shop_code'>
                                                <input type='hidden' name='shop_section$count' value='$shop_section'>
                                                <td class='shop-input'><input type='number' step='0.01' name='shop_amount$count' $add_bid_disabled></td>
                                                <td><input type='submit' name='shop_confirm' value='Add Bid' $add_bid_disabled></td>
                                            </tr>";
                                            $count = $count + 1;
                                        }
                                    }
                                    else{
                                        echo"
                                            <tr>
                                                <td colspan='7'>No results found</td>
                                            </tr>";
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>


            </div>
            <!-- End of course-info -->


        </div>
        <!-- End of main-container -->

    </div>
    <!-- End of wrapper -->


</body>

</html>