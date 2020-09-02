<?php
require_once "common.php";
require_once "round2clearing.php";

function bidStatus($code, $section){
    $roundDAO = new roundDAO;
    $courseDAO = new courseDAO;
    $mbpDAO = new minimum_bid_valueDAO;
    $bidDAO = new bidDAO;
    $studentDAO = new studentDAO;
    $sectionDAO = new sectionDAO;
    $success1 = new successfulbidsDAO;
    $success2 = new successfulbids2DAO;
    $rejected1 = new bidsrejectedDAO;
    $rejected2 = new bidsrejected2DAO;

    $round1 = $roundDAO->retrieveByRound('round 1');
    $round2 = $roundDAO->retrieveByRound('round 2');
    $errors = array();
    $result = array();
    
    # prepare for error output
    $courseinfo = $courseDAO->retrieveByCourseID($code);
    if(empty($courseinfo)){
        $errors[] = 'invalid course';
    }
    $sectioninfo = $sectionDAO->retrieveByCourseSection($code,$section);
    if(empty($sectioninfo) && !empty($courseinfo)){
        $errors[] = 'invalid section';
    }

    #During round 1:
    // Vacancy: the total available seats as all the bids are still pending.
    // Minimum bid price: when #bid is less than the #vacancy, report the lowest bid amount. Otherwise, set the price as the clearing price. When there is no bid made, the minimum bid price will be 10.0 dollars.
    // Bids: report (userid, bid amount, e-dollar balance, status) for all the bids made so far during round 1. Status should be "pending".
    // Balance: follow the round 1 logic.
    if (empty($errors)){
        $students = array();
        $studentsSuccess = array();
        $studentsReject = array();

        if ($round1 == 'started'){
            $vacancy = $sectionDAO->retrieveByCourseSection($code,$section)->getSize();
            $bids = $bidDAO->retrieveByCourseSection($code,$section);
            $minPrice = 10;
            
            if (count($bids) < $vacancy){
                $minPrice = end($bids)->getAmount();
            }
            elseif (!empty($bids)){
                $minPrice = $bids[$vacancy-1]->getAmount();
            }

            foreach ($bids as $bid){
                $students[] = [
                    "userid" => $bid->getUserID(),
                    "amount" => $bid->getAmount(),
                    "balance" => $studentDAO->retrieveByID($bid->getUserID())->getEdollar(),
                    "status" => "pending"
                ];
            }

            $userID = array_column($students, 'userid');
            array_multisort($userID, SORT_ASC, $students);
            $amount = array_column($students, 'amount');
            array_multisort($amount, SORT_DESC, $students);

        }


        #After Round 1 ended (and before round 2 is started):
        // Vacancy: (the total available seats) - (number of successful bid during round 1).
        // Minimum bid price: report the lowest successful bid. If there was no bid made (or no successful bid) during round 1, the value will be 10.0 dollars.
        // Bids: report (userid, bid amount, e-dollor balance, status) for all the bids. Status should be either "success" or "fail" according to the round 1 clearing logic.
        // Balance: follow the clearing round 1 logic.
        if ($round1 == 'ended' && $round2 == 'not started'){
            $successBids = $success1->retrieveByCourseSection($code, $section);
            $rejectedBids = $rejected1->retrieveByCourseSection($code, $section);
            $takenSeats = count($successBids);
            $vacancy = $sectionDAO->retrieveByCourseSection($code, $section)->getSize() - $takenSeats;
            $minPrice = 10;

            usort($successBids, function($a, $b) {
                return $a->amount < $b->amount ? 1 : -1;
            });

            if (!empty($successBids)){
                $minPrice = end($successBids)->getAmount();
            }

            foreach ($successBids as $bid){
                $studentsSuccess[] = [
                    "userid" => $bid->getUserID(),
                    "amount" => $bid->getAmount(),
                    "balance" => $studentDAO->retrieveByID($bid->getUserID())->getEdollar(),
                    "status" => "success"
                ];
            }

            $userID = array_column($studentsSuccess, 'userid');
            array_multisort($userID, SORT_ASC, $studentsSuccess);
            $amount = array_column($studentsSuccess, 'amount');
            array_multisort($amount, SORT_DESC, $studentsSuccess);

            foreach ($rejectedBids as $bid){
                $studentsReject[] = [
                    "userid" => $bid->getUserID(),
                    "amount" => $bid->getAmount(),
                    "balance" => $studentDAO->retrieveByID($bid->getUserID())->getEdollar(),
                    "status" => "fail"
                ];
            }

            $userID = array_column($studentsReject, 'userid');
            array_multisort($userID, SORT_ASC, $studentsReject);
            $amount = array_column($studentsReject, 'amount');
            array_multisort($amount, SORT_DESC, $studentsReject);

            $students = array_merge($studentsSuccess, $studentsReject);
        }

        # During Round 2:
        // Vacancy: follow the round 2 logic. (put the total available vacancies as the round is not over)
        // Minimum bid price: follow the round 2 logic.
        // Bids: report (userid, bid amount, e-dollor balance, status) for all the bids made during round 2. Status should be either "success" or "fail" reflecting the real-time bidding status.
        // Balance: follow the round 2 logic.
        if ($round1 == 'ended' && $round2 == 'started'){
            $successBids = $success1->retrieveByCourseSection($code, $section);
            $takenSeats = count($successBids);
            $vacancy = $sectionDAO->retrieveByCourseSection($code, $section)->getSize() - $takenSeats;
            $live = displayLive($code, $section);
            $minPrice = $live['misc']['MinBid'];
            $bids = $live['liveArray'];

            foreach ($bids as $bid){
                $students[] = [
                    "userid" => $bid['Obj']->getUserID(),
                    "amount" => $bid['Bid Price'],
                    "balance" => $studentDAO->retrieveByID($bid['Obj']->getUserID())->getEdollar(),
                    "status" => $bid['State'] == 'Successful' ? 'success' : 'fail'
                ];
            }
            
        }

        # After round 2 is closed:
        // Vacancy: (the total available seats) - (number of successfully enrolled students in round 1 and 2).
        // Minimum bid price: the minimum successful bid amount during round 2. If there was no bid made (or no successful bid) during round 2, the value will be 10.0 dollars.
        // Bids: report (userid, bid amount, e-dollor balance, status) for all the successful bids made in round 1 and 2. Do not include failed bids.
        // Balance: the e-dollor left after deducting all successful bid amounts in round 1 and 2.
        if ($round1 == 'ended' && $round2 == 'ended'){
            $successBids1 = $success1->retrieveByCourseSection($code, $section);
            $successBids2 = $success2->retrieveByCourseSection($code, $section);
            $rejectedBids1 = $rejected1->retrieveByCourseSection($code, $section);
            $rejectedBids2 = $rejected2->retrieveByCourseSection($code, $section);

            $vacancy = $sectionDAO->retrieveByCourseSection($code, $section)->getSize() - count($successBids1) - count($successBids2);
            $minPrice = 10;

            if (!empty($successBids2)){
                $minPrice = end($successBids2)->getAmount();
            }

            $successBids = array_merge($successBids1, $successBids2);

            foreach ($successBids as $bid){
                $students[] = [
                    "userid" => $bid->getUserID(),
                    "amount" => $bid->getAmount(),
                    "balance" => $studentDAO->retrieveByID($bid->getUserID())->getEdollar(),
                    "status" => "success"
                ];
            }
        }
    }

    //if no errors, return bid status
    if (empty($errors)){
        $result['status'] = "success";
        $result['vacancy'] = intval($vacancy);
        $result['min-bid-amount'] = $minPrice;
        $result['students'] = $students;
    }

    //if errors present, return error merssages
    else{
        $result['status'] = "error";
        $result['message'] = $errors;
    }

    return $result;
}
?>