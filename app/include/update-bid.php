<?php
require_once "common.php";
require_once "round2clearing.php";

 //checks if theres a clash in 2 sets of start and 2nd time, takes in 2 array
 function checkClash($startEnd1, $startEnd2){

    if ($startEnd1[0] > $startEnd2[0] && $startEnd1[0] < $startEnd2[1]){
        return TRUE;
    }

    if ($startEnd1[1] > $startEnd2[0] && $startEnd1[1] < $startEnd2[1]){
        return TRUE;
    }

    if ($startEnd1[1] == $startEnd2[1] && $startEnd1[0] == $startEnd2[0]){
        return TRUE;
    }

    return FALSE;
}


function update($userid, $amount, $code, $section){

   
    $bidDAO = new bidDAO;
    $successfulBidsDAO = new successfulbidsDAO;
    $successfulBidsDAO2 = new successfulbids2DAO;
    $courseDAO = new courseDAO;
    $courseCompleted = new course_completedDAO;
    $sectionDAO = new sectionDAO;
    $studentDAO = new studentDAO;
    $prerequisiteDAO = new prerequisiteDAO;
    $roundDAO = new roundDAO;
    $minimumBidValueDAO = new minimum_bid_valueDAO;

    $allCourses = $courseDAO->retrieveAll();
    $errors = array();
    $added = FALSE;
    $updated = FALSE;
    $roundEnded = FALSE;

    $inputErrors = 0;
    $logicErrors = 0;

    $round1 = $roundDAO->retrieveByRound('round 1');
    $round2 = $roundDAO->retrieveByRound('round 2');

    if ( ($round1 == 'ended' && $round2 == 'not started') || ($round1 == 'ended' && $round2 == 'ended')){
        $_SESSION['errors'][] = 'round ended.';
        return  [
                "status" => "error",
                "message" => ['round ended']
            ];
            
        }
    

    // Input validations
    // "invalid amount"	the amount must be a positive number >= e$10.00 and not more than 2 decimal places.
    // "invalid course"	the course code is not found in the system records
    // "invalid section" the section code is not found in the system records. Only check if the course code is valid.
    // "invalid userid"	the userid is not found in the system records

    //Checks for invalid userid
    if ( empty($studentDAO->retrieveByID($userid))){
        $errors[] = 'invalid userid';
        $inputErrors ++;
    }

    //Checks for invalid amount format
    if ($amount < 10 || strlen(substr(strrchr($amount, "."), 1)) > 2){
        $errors[] = 'invalid amount';
        $inputErrors ++;
    }

    //Checks for invalid course
    if ( empty($courseDAO->retrieveByCourseID($code)) ){
        $errors[] = 'invalid course';
        $inputErrors ++;
    }

    //Checks for invalid section
    if( !empty($courseDAO->retrieveByCourseID($code)) && !in_array($section, $sectionDAO->retrieveByCourse($code)) ){
        $errors[] = 'invalid section';
        $inputErrors ++;
    }


    if ($inputErrors == 0){

        $tempBid = array();


        //Delete bid if user already bidded for the course (For update bid)
        if ( !empty($bidDAO->retrieveCourse($userid,$code)) ){
            $tempBid = $bidDAO->retrieveCourse($userid, $code)[0];
            $origin = 'bid';
            $bidDAO->remove($userid, $code, $tempBid->getSection());
        }


        //execute logic validations
        $bidList = $bidDAO->retrieveSpecific($userid);
        $successfulBidList = $successfulBidsDAO->retrieveSpecific($userid);
        $allBids = array_merge($bidList, $successfulBidList);
        $edollar = $studentDAO->retrieveByID($userid)->getEdollar();
        $vacancy = $sectionDAO->retrieveByCourseSection($code, $section)->getSize() - $successfulBidsDAO->count($code, $section)
        - $successfulBidsDAO2->count($code, $section);

       // Reject if vacany = 0
       if ($vacancy <= 0){
            $errors[] = 'no vacancy';
            $logicErrors ++;
       }
        //Check for insufficient e$
        $tempEdollar = $edollar;
        if( !empty($tempBid) ){
            $tempEdollar += $tempBid->getAmount();
        }
        if ($amount > $tempEdollar){
            $errors[] = 'insufficient e$';
            $logicErrors ++;
        }

        //If user has pre-exisitng bids, check for class and exam timetable clashes
        if ( !empty($bidList) || !empty($successfulBidList) ){

            foreach ($allBids as $bid){
                $biddingSectionObj = $sectionDAO->retrieveByCourseSection($code, $section);
                $biddedSectionObj = $sectionDAO->retrieveByCourseSection($bid->getCode(), $bid->getSection());

                //Check if section exists in course first, then check if class timetable clashes
                if ($biddingSectionObj != NULL){
                    $startEndBidding = [$biddingSectionObj->getStart(), $biddingSectionObj->getEnd()];
                    $startEndBidded = [$biddedSectionObj->getStart(), $biddedSectionObj->getEnd()];

                    if (checkClash($startEndBidding, $startEndBidded) && $biddingSectionObj->getDay() == $biddedSectionObj->getDay()){
                        $errors[] = 'class timetable clash';
                        $logicErrors ++;
                    }
                }

                //Check if exam time clashes
                $biddingCourseObj = $courseDAO->retrieveByCourseID($code);
                $biddedCourseObj = $courseDAO->retrieveByCourseID($bid->getCode());

                if ($biddingCourseObj->getExamDate() == $biddedCourseObj->getExamDate()){
                    $biddingExamTime = [$biddingCourseObj->getExamStart(), $biddingCourseObj->getExamEnd()];
                    $biddedExamTime = [$biddedCourseObj->getExamStart(), $biddedCourseObj->getExamEnd()];

                    if (checkClash($biddingExamTime, $biddedExamTime)){
                        $errors[] = 'exam timetable clash';
                        $logicErrors ++;
                    }
                }
            }
        }

        //Check if student has done prerequisites
        $prerequisiteList = $prerequisiteDAO->retrievePrereq($code);
        $coursesDone = $courseCompleted->retrieveCoursesCompletedByID($userid);

        foreach ($prerequisiteList as $prereq){
            if (!in_array($prereq, $coursesDone)){
                $errors[] = 'Incomplete prerequisites';
                $logicErrors ++;
                break;
            }
        }



        //Check if student has already completed course
        if (in_array($code, $coursesDone)){
            $errors[] = 'course completed';
            $logicErrors ++;
        }

        //Check if student has already successfully won a bid for the course
        if (!empty($successfulBidsDAO->retrieveCourse($userid, $code))){
            $errors[] = 'course enrolled';
            $logicErrors ++;
        }

        //Check if student has already bidded for 5 sections
        if (count($allBids) >= 5){
            $errors[] = 'section limit reached';
            $logicErrors ++;
        }

        //Check if student is bidding for his own school course if its round 1
        if ($roundDAO->retrieveByround('round 1') == 'started'){
            if ($studentDAO->retrieveByID($userid)->getSchool() !== $courseDAO->retrieveSchoolByCourse($code)[0]){
                $errors[] = 'not own school course';
                $logicErrors ++;
            }
        }

        //Check if student is bidding below minimum bid price
        if ( $round1 == 'ended' && $round2 == 'started'){
            if (!empty($minimumBidValueDAO->retrieveSpecificValue($code, $section))){
                $minimumBidValue = $minimumBidValueDAO->retrieveSpecificValue($code, $section);
                $count = $bidDAO->retrieveSectionCount($code, $section);
                if ($amount < $minimumBidValue){
                    $errors[] = 'bid too low';
                    $inputErrors++;
                }
            }
        }


    }

    //If first time bidding for course, simply add the bid in
    if ( $logicErrors == 0 && $inputErrors == 0 && empty($tempBid) ){
        $bidDAO->add(new bid($userid, $amount, $code, $section));
        $studentDAO->deductEdollar($studentDAO->retrieveByID($userid), $amount);
        $added = TRUE;
    }

    //If not, update existing bid
    if ( $logicErrors == 0 && $inputErrors == 0 && !$added && !empty($tempBid) ){
        $bidDAO->add(new bid($userid, $amount, $code, $section));
        $studentDAO->addEdollar($studentDAO->retrieveByID($userid), $tempBid->getAmount());
        $studentDAO->deductEdollar($studentDAO->retrieveByID($userid), $amount);
        $updated = TRUE;

    }

    //If no errors and bid has been updated/aded, return success messages
    if($logicErrors == 0 && $inputErrors == 0 && ($updated || $added)){
        displayLive($code, $section);
        $result = [
            "status" => "success",
        ];
        if ($updated){
        $_SESSION['updated'] = $updated;
        }
    }

    //If there are errors present, add previously deleted bid and return error messages
    else{
        if (!empty($tempBid)){
            if ($origin == 'bid'){
                $bidDAO->add($tempBid);
            }
        }
        

        $result = [
            "status" => "error",
            "message" => $errors
        ];

        $sortingArray = array();

        foreach ($result['message'] as $string){
            $sortingArray[] = explode(" ", $string)[0];
        }

        //Sort error messages by field names
        array_multisort($sortingArray, SORT_ASC, $result['message']);

        foreach ($errors as $error){
            $_SESSION['errors'][] = $error;
        }
        

    }


    return $result;
}

?>