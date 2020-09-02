<?php
require_once "common.php";

function dumpBid($course, $section){

    $errors = array();

    $round_dao = new roundDAO;
    $course_dao = new courseDAO;
    $section_dao = new sectionDAO;

    $data = $round_dao->retrieveAll();
    $json = json_encode($data);
    $roundarray = json_decode($json, true);


    # format round in [ roundnum => status ]  format
    $round = array();
    foreach($roundarray as $r){
        $round[$r['roundnum']] = $r['status'];
    }


    # Logic validation
    $courseinfo = $course_dao->retrieveByCourseID($course);
    if(empty($courseinfo)){
        $errors[] = 'invalid course';
    }
    $sectioninfo = $section_dao->retrieveByCourseSection($course,$section);
    if(empty($sectioninfo) && !empty($courseinfo)){
        $errors[] = 'invalid section';
    }


    # Round started condition
    if (($round['round 1'] == 'started' && $round['round 2'] == 'not started') ||
    ($round['round 1'] == 'ended' && $round['round 2'] == 'started') ){
        $bid_dao = new bidDAO;

        $data = $bid_dao->retrieveByCourseSection($course, $section);
        $subbids = array();
        $n = 0;
        foreach($data as $d){
            $subbids[$n]['row'] = $n+1;
            $subbids[$n]['userid'] = $d->getUserID();
            $subbids[$n]['amount'] = $d->getAmount();
            $subbids[$n]['result'] = '-';
            $n++;}
        $json = json_encode($subbids);
        $bids = json_decode($json, true);


        $bid_amount = array();
        foreach($bids as $bid){
            $bid_amount[] = $bid['amount'];
        }
        array_multisort($bid_amount, SORT_DESC, $bids);

    }
    # Round ended condition
    elseif ($round['round 1'] == 'ended' && $round['round 2'] == 'not started'){
        $successful_bids_dao = new successfulbidsDAO;
        $bids_rejected_dao = new bidsrejectedDAO;

        $subbids = array();

        $data1 = $successful_bids_dao->retrieveByCourseSection($course, $section);
        $n = 0;
        foreach($data1 as $d){
            $subbids[$n]['row'] = $n+1;
            $subbids[$n]['userid'] = $d->getUserID();
            $subbids[$n]['amount'] = $d->getAmount();
            $subbids[$n]['result'] = 'in';
            $n++;}



        $data2 = $bids_rejected_dao->retrieveByCourseSection($course, $section);
        foreach($data2 as $d){
            $subbids[$n]['row'] = $n+1;
            $subbids[$n]['userid'] = $d->getUserID();
            $subbids[$n]['amount'] = $d->getAmount();
            $subbids[$n]['result'] = 'out';
            $n++;}

        $json = json_encode($subbids);
        $bids = json_decode($json, true);



        # sort based on amount from highest to lowest
        $bid_amount = array();
        foreach($bids as $bid){
            $bid_amount[] = $bid['amount'];
        }
        array_multisort($bid_amount, SORT_DESC, $bids);


    }

    elseif ($round['round 1'] == 'ended' && $round['round 2'] == 'ended'){
        $successful_bids_dao = new successfulbids2DAO;
        $bids_rejected_dao = new bidsrejected2DAO;

        $subbids = array();

        $data1 = $successful_bids_dao->retrieveByCourseSection($course, $section);
        $n = 0;
        foreach($data1 as $d){
            $subbids[$n]['row'] = $n+1;
            $subbids[$n]['userid'] = $d->getUserID();
            $subbids[$n]['amount'] = $d->getAmount();
            $subbids[$n]['result'] = 'in';
            $n++;}



        $data2 = $bids_rejected_dao->retrieveByCourseSection($course, $section);
        foreach($data2 as $d){
            $subbids[$n]['row'] = $n+1;
            $subbids[$n]['userid'] = $d->getUserID();
            $subbids[$n]['amount'] = $d->getAmount();
            $subbids[$n]['result'] = 'out';
            $n++;}

        $json = json_encode($subbids);
        $bids = json_decode($json, true);



        # sort based on amount from highest to lowest
        $bid_amount = array();
        foreach($bids as $bid){
            $bid_amount[] = $bid['amount'];
        }
        array_multisort($bid_amount, SORT_DESC, $bids);


    }

    //If no errors, return bids
    if(count($errors) == 0){
        $result = [
            "status" => "success",
            "bids" => $bids,
        ];
    }

    //If errors present, print error messages
    else{
        $result = [
            "status" => "error",
            "message" => $errors,
        ];
    }

    return $result;

}


?>