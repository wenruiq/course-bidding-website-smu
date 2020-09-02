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
require_once 'include/start.php'; //start()
require_once 'include/stop.php'; //stop()

// Process round changes (triggered by admin function buttons at top of page)
$round_msg = '';
if(isset($_GET['round'])){
    if($_GET['round'] == 'start'){
        $roundDAO = new roundDAO;
        $round1_status = $roundDAO->retrieveByRound("round 1");
        $round2_status = $roundDAO->retrieveByRound("round 2");
        if($round1_status == "not started"){
            $round_msg = "Round 1 started successfully.";
        }
        if($round1_status == "ended" && $round2_status == "not started"){
            $round_msg = "Round 2 started successfully.";
        }
        start();
    }
    elseif($_GET['round'] == 'stop'){
        $roundDAO = new roundDAO;
        $round1_status = $roundDAO->retrieveByRound("round 1");
        $round2_status = $roundDAO->retrieveByRound("round 2");
        if($round1_status == "started"){
            $round_msg = "Round 1 stopped and cleared successfully.";
        }
        if($round2_status == "started"){
            $round_msg = "Round 2 stopped and cleared successfully.";
        }
        stop();
    }
    elseif($_GET['round'] == 'reset'){
        $round_dao = new roundDAO;
        $round_dao->update("round 1", "not started");
        $round_dao->update("round 2", "not started");
        $success_dao = new successfulbidsDAO;
        $success_dao->removeAll();
        $success_dao2 = new successfulbids2DAO;
        $success_dao2->removeAll();
        $rejected_dao = new bidsrejectedDAO;
        $rejected_dao->removeAll();
        $rejected_dao2 = new bidsrejected2DAO;
        $rejected_dao2->removeAll();
        $bid_dao = new bidDAO();
        $bid_dao->removeAll();
        $round_msg = "Reset is successful.";
    }
}

// RETRIEVE DATA
// admin login
$admin_dao = new adminDAO;
$user = $admin_dao->retrieveByID($username);
$name = $user->getUserID();

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
$all_current_bids = $bidDAO->retrieveAll(); // all bids made in current round
$all_successful_bids1 = $successfulbidsDAO->retrieveAll();
$all_successful_bids2 = $successfulbids2DAO->retrieveAll();
$all_successful_bids = array_merge($all_successful_bids1, $all_successful_bids2); // all successful bids in all rounds
$all_rejected_bids1 = $bidsrejectedDAO->retrieveAll();
$all_rejected_bids2 = $bidsrejected2DAO->retrieveAll();
$all_rejected_bids = array_merge($all_rejected_bids1, $all_rejected_bids2); // all rejected bids in all rounds
$all_bids_cleared1 = array_merge($all_successful_bids1, $all_rejected_bids1); // all bids cleared in round 1
$all_bids_cleared2 = array_merge($all_successful_bids2, $all_rejected_bids2); // all bids cleared in round 1


// min bid info
$minimum_bid_valueDAO = new minimum_bid_valueDAO;


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


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/a_adminpage.css">
    <title>BIOS | Bidding Online System</title>
</head>

<body>
    <!-- Wrapper -->
    <div class="wrapper">
        <!-- Nav bar -->
        <div class="nav-bar">
            <!-- Website info -->
            <div class="website-info">
                <a href='adminpage.php'><img src="img/merlion.png"></a>
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
                <div class="welcome-msg"><b>Welcome, <?=$name?>!</b></div>
                <!-- Nav buttons -->
                <div class="nav-btns">
                    <a href="logout.php" class="nav-btn">Logout</a>
                </div>
            </div>
        </div>
        <!-- Main container -->
        <div class="main-container">

            <!-- admin-functions -->
            <div class="def-box admin-functions">
                <a href="bootstrap_page.php" class="funct-btn">Bootstrap</a>
                <a href="adminpage.php?round=start" class="funct-btn">Start Round</a>
                <a href="adminpage.php?round=stop" class="funct-btn">Stop Round</a>
                <a href="adminpage.php?round=reset" class="funct-btn">Reset All</a>
            </div>

            <!-- round-msg -->
            <div class="round-msg">
                <font color='green'><?=$round_msg?></font>
            </div>

            <!-- admin-box -->
            <div class="def-box admin-box">
                <div class="admin-box-title">Student Bids</div>

                <!-- bid-search -->
                <?php
                $search_disabled = '';
                if($round1_status == 'not started' && $round2_status == 'not started'){
                    $search_disabled = "disabled";
                }
                ?>
                <div class="bid-search">
                    <form action="adminpage.php" method="post">
                        <input type="text" name="search-code" placeholder="Course Code" <?=$search_disabled?>>
                        <input type="text" name="search-section" placeholder="Section Number" <?=$search_disabled?>>
                        <input type="submit" name="confirm-search" value="Search" <?=$search_disabled?>>
                        <input type="submit" name="clear-search" value="Clear Search" <?=$search_disabled?>>
                    </form>
                </div>

                <!-- bid-list -->
                <div class="bid-list">
                    <div class="table-wrapper">
                        <div class="table-scroll">
                            <table class="def-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Course</th>
                                        <th>Section</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        // Before round 1
                                        if($round1_status == 'not started' && $round2_status =='not started'){
                                            echo "<tr><td colspan=5><b>Bidding hasn't begun.</b></td></tr>";
                                        }
                                        // Round 1 active
                                        if($round1_status == 'started' && $round2_status =='not started'){
                                            if(!isset($_POST["confirm-search"])){
                                                if(!empty($all_current_bids)){
                                                    foreach($all_current_bids as $all_current_bid){
                                                        $bid_status = bid_status($all_current_bid);
                                                        echo"
                                                        <tr>
                                                        <td>{$all_current_bid->getUserID()}</td>
                                                        <td>{$all_current_bid->getCode()}</td>
                                                        <td>{$all_current_bid->getSection()}</td>
                                                        <td>{$all_current_bid->getAmount()}</td>
                                                        <td class=$bid_status>$bid_status</td>
                                                        </tr>";
                                                    }
                                                }
                                                else{
                                                    echo"<tr><td colspan=5><b>No bids placed in round 1 yet.</b></td></td>";
                                                }
                                            }
                                            else{
                                                $search_code = $_POST["search-code"];
                                                $search_section = $_POST["search-section"];
                                                if(substr($search_section, 0, 1) != "s" && substr($search_section, 0, 1) != "S"){
                                                    $search_section = "S".$search_section;
                                                }
                                                $all_current_bids = $bidDAO->retrieveByCourseSection($search_code, $search_section);
                                                if(!empty($all_current_bids)){
                                                    $count = 0;
                                                    foreach($all_current_bids as $all_current_bid){
                                                        $bid_status = bid_status($all_current_bid);
                                                        echo"
                                                        <tr>
                                                        <td>{$all_current_bid->getUserID()}</td>
                                                        <td>{$all_current_bid->getCode()}</td>
                                                        <td>{$all_current_bid->getSection()}</td>
                                                        <td>{$all_current_bid->getAmount()}</td>
                                                        <td class=$bid_status>$bid_status</td>
                                                        </tr>";
                                                    }
                                                }
                                                else{
                                                    echo"<tr><td colspan=5><b>No results found.</b></td></td>";
                                                }
                                            }
                                        }
                                        // After Round 1
                                        if($round1_status == 'ended' && $round2_status =='not started'){
                                            if(!isset($_POST["confirm-search"])){
                                                if(!empty($all_bids_cleared1)){
                                                    foreach($all_bids_cleared1 as $all_current_bid){
                                                        $bid_status = bid_status($all_current_bid);
                                                        echo"
                                                        <tr>
                                                        <td>{$all_current_bid->getUserID()}</td>
                                                        <td>{$all_current_bid->getCode()}</td>
                                                        <td>{$all_current_bid->getSection()}</td>
                                                        <td>{$all_current_bid->getAmount()}</td>
                                                        <td class=$bid_status>$bid_status</td>
                                                        </tr>";
                                                    }
                                                }
                                                else{
                                                    echo"<tr><td colspan=5><b>No bids cleared from round 1.</b></td></td>";
                                                }
                                            }
                                            else{
                                                $search_code = $_POST["search-code"];
                                                $search_section = $_POST["search-section"];
                                                if(substr($search_section, 0, 1) != "s" && substr($search_section, 0, 1) != "S"){
                                                    $search_section = "S".$search_section;
                                                }
                                                $all_succ_cleared = $successfulbidsDAO->retrieveByCourseSection($search_code, $search_section);
                                                $all_fail_cleared = $bidsrejectedDAO->retrieveByCourseSection($search_code, $search_section);
                                                $all_current_bids = array_merge($all_succ_cleared,$all_fail_cleared);
                                                if(!empty($all_current_bids)){
                                                    $count = 0;
                                                    foreach($all_current_bids as $all_current_bid){
                                                        $bid_status = bid_status($all_current_bid);
                                                        echo"
                                                        <tr>
                                                        <td>{$all_current_bid->getUserID()}</td>
                                                        <td>{$all_current_bid->getCode()}</td>
                                                        <td>{$all_current_bid->getSection()}</td>
                                                        <td>{$all_current_bid->getAmount()}</td>
                                                        <td class=$bid_status>$bid_status</td>
                                                        </tr>";
                                                    }
                                                }
                                                else{
                                                    echo"<tr><td colspan=5><b>No results found.</b></td></td>";
                                                }
                                            }
                                        }
                                        // Round 2 active
                                        if($round1_status == 'ended' && $round2_status =='started'){
                                            if(!isset($_POST["confirm-search"])){
                                                if(!empty($all_current_bids)){
                                                    foreach($all_current_bids as $all_current_bid){
                                                        $bid_status = bid_status($all_current_bid);
                                                        echo"
                                                        <tr>
                                                        <td>{$all_current_bid->getUserID()}</td>
                                                        <td>{$all_current_bid->getCode()}</td>
                                                        <td>{$all_current_bid->getSection()}</td>
                                                        <td>{$all_current_bid->getAmount()}</td>
                                                        <td class=$bid_status>$bid_status</td>
                                                        </tr>";
                                                    }
                                                }
                                                else{
                                                    echo"<tr><td colspan=5><b>No bids placed in round 2 yet.</b></td></td>";
                                                }
                                            }
                                            else{
                                                $search_code = $_POST["search-code"];
                                                $search_section = $_POST["search-section"];
                                                if(substr($search_section, 0, 1) != "s" && substr($search_section, 0, 1) != "S"){
                                                    $search_section = "S".$search_section;
                                                }
                                                $all_current_bids = $bidDAO->retrieveByCourseSection($search_code, $search_section);
                                                if(!empty($all_current_bids)){
                                                    $count = 0;
                                                    foreach($all_current_bids as $all_current_bid){
                                                        $bid_status = bid_status($all_current_bid);
                                                        echo"
                                                        <tr>
                                                        <td>{$all_current_bid->getUserID()}</td>
                                                        <td>{$all_current_bid->getCode()}</td>
                                                        <td>{$all_current_bid->getSection()}</td>
                                                        <td>{$all_current_bid->getAmount()}</td>
                                                        <td class=$bid_status>$bid_status</td>
                                                        </tr>";
                                                    }
                                                }
                                                else{
                                                    echo"<tr><td colspan=5><b>No results found.</b></td></td>";
                                                }
                                            }
                                        }
                                        // After Round 2
                                        if($round1_status == 'ended' && $round2_status =='ended'){
                                            if(!isset($_POST["confirm-search"])){
                                                if(!empty($all_bids_cleared2)){
                                                    foreach($all_bids_cleared2 as $all_current_bid){
                                                        $bid_status = bid_status($all_current_bid);
                                                        echo"
                                                        <tr>
                                                        <td>{$all_current_bid->getUserID()}</td>
                                                        <td>{$all_current_bid->getCode()}</td>
                                                        <td>{$all_current_bid->getSection()}</td>
                                                        <td>{$all_current_bid->getAmount()}</td>
                                                        <td class=$bid_status>$bid_status</td>
                                                        </tr>";
                                                    }
                                                }
                                                else{
                                                    echo"<tr><td colspan=5><b>No bids cleared from round 2.</b></td></td>";
                                                }
                                            }
                                            else{
                                                $search_code = $_POST["search-code"];
                                                $search_section = $_POST["search-section"];
                                                if(substr($search_section, 0, 1) != "s" && substr($search_section, 0, 1) != "S"){
                                                    $search_section = "S".$search_section;
                                                }
                                                $all_succ_cleared = $successfulbids2DAO->retrieveByCourseSection($search_code, $search_section);
                                                $all_fail_cleared = $bidsrejected2DAO->retrieveByCourseSection($search_code, $search_section);
                                                $all_current_bids = array_merge($all_succ_cleared,$all_fail_cleared);
                                                if(!empty($all_current_bids)){
                                                    $count = 0;
                                                    foreach($all_current_bids as $all_current_bid){
                                                        $bid_status = bid_status($all_current_bid);
                                                        echo"
                                                        <tr>
                                                        <td>{$all_current_bid->getUserID()}</td>
                                                        <td>{$all_current_bid->getCode()}</td>
                                                        <td>{$all_current_bid->getSection()}</td>
                                                        <td>{$all_current_bid->getAmount()}</td>
                                                        <td class=$bid_status>$bid_status</td>
                                                        </tr>";
                                                    }
                                                }
                                                else{
                                                    echo"<tr><td colspan=5><b>No results found.</b></td></td>";
                                                }
                                            }
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- End of bids-list -->

            </div>
            <!-- End of admin-box -->

        </div>
        <!-- End of main-container -->

    </div>
    <!-- End of wrapper -->


</body>

</html>