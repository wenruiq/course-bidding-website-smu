<?php
require_once "common.php";

//Counts the number of occurrence of bids with amount same as amount specified
function countAmount ($amount, $array){
    $matchCount = 0;

    foreach ($array as $arrayObj){
        if ($arrayObj->getAmount() == $amount){
            $matchCount ++;
        }
    }

    return $matchCount;
}

//Returns a table of array with sorted bids and their respective statuses, alongside misc information - Course code, 
//Vacancies, number of bids, minimum bid price
function displayLive ($code, $biddedSection){

    $bidDAO = new bidDAO;
    $sectionDAO = new sectionDAO;
    $bidsRejectedDAO = new bidsrejectedDAO;
    $successfulBidsDAO = new successfulbidsDAO;
    $studentDAO = new studentDAO;
    $minimumBidValueDAO = new minimum_bid_valueDAO;
    $bidArray = array();
    $allBids = $bidDAO->retrieveAll();
    $liveResults = array();


    foreach ($allBids as $bid){
        $title = $bid->getCode()." ".$bid->getSection();

        //Sort all bids into associative arrays based on their section and course
        if(array_key_exists($title, $bidArray)){
            $bidArray[$title][] = $bid;
        }

        else{
            $bidArray[$title] = [$bid];
        }

    }

    //Sorts the sections in $bidArray according to the bid amounts in the array
    foreach($bidArray as &$section){
        usort($section, function($a, $b) {
            return $a->amount < $b->amount ? 1 : -1;
        });
    }


    $check = $code . " " . $biddedSection;

    //Creating variables needed for round clearing 2
    $sectionCourseArray = $bidArray[$check];
    $quota = $sectionDAO->retrieveByCourseSection($code, $biddedSection)->getSize();
    $takenSlots = $successfulBidsDAO->count($code, $biddedSection);
    $vacancies = $quota - $takenSlots;
    $processCount = 0;
    $liveArray = array();

    //Define clearing price
    if (count($sectionCourseArray) >= $vacancies && $vacancies != 0){
        $tempMin = floatval($sectionCourseArray[$vacancies-1]->getAmount()) + 1;
        
        if (empty($minimumBidValueDAO->retrieveSpecificValue($code, $biddedSection))){
            $minimumBidValueDAO->add(new minimum_bid_value($tempMin, $code, $biddedSection));
        }

        elseif ($minimumBidValueDAO->retrieveSpecificValue($code, $biddedSection) < $tempMin){
            $minimumBidValueDAO->update($tempMin, $code, $biddedSection);
        }
        $minimumBidPrice = $sectionCourseArray[$vacancies-1]->getAmount();
    }

    else{
        $tempMin = 10;
            if (empty($minimumBidValueDAO->retrieveSpecificValue($code, $biddedSection))){
                $minimumBidValueDAO->add(new minimum_bid_value(10, $code, $biddedSection));
        }
        $minimumBidPrice = 10;
    }


    //Round 2 clearing logic
    $offset = 0;

    //Sorts all bids in specified course and section
    foreach ($sectionCourseArray as $bidItem){
        $rank = $processCount + 1;
        $price = $bidItem->getAmount();
        $repeatedAmount = countAmount($price, $sectionCourseArray);

        //Adds bid as successful if number of bids at iterated price does not exceed vacancies
        if ($processCount + $repeatedAmount - $offset <= $vacancies){
            $liveArray["rank  $rank"] = [
                'Obj' => $bidItem,
                'Ranking' => $rank,
                'Bid Price' => $price,
                'State' => 'Successful'
            ];

            if ($processCount < count($sectionCourseArray)-1 && $sectionCourseArray[$processCount + 1]->getAmount() == $price){
                $offset ++;
            }
            else{
                $offset = 0 ;
            }
            
        }

        //Adds bid as unsuccessful if number of bids at iterated price exceeds vacancies but price is equals to minimum bid price
        elseif ($processCount + $repeatedAmount - $offset > $vacancies && $price == $minimumBidPrice){
            $liveArray["rank  $rank"] = [
                'Obj' => $bidItem,
                'Ranking' => $rank,
                'Bid Price' => $price,
                'State' => 'Unsuccessful'
            ];

            if ($processCount < count($sectionCourseArray)-1 && $sectionCourseArray[$processCount + 1]->getAmount() == $price){
                $offset ++;
            }
            else{
                $offset = 0 ;
            }
        }

        //Adds bid as unsuccessful bid too low for all other cases
        else{
            $liveArray["rank  $rank"] = [
                'Obj' => $bidItem,
                'Ranking' => $rank,
                'Bid Price' => $price,
                'State' => 'Unsuccessful. Bid too low.'
            ];

            if ($processCount < count($sectionCourseArray)-1 && $sectionCourseArray[$processCount + 1]->getAmount() == $price){
                $offset ++;
            }
            else{
                $offset = 0 ;
            }
        }


        $processCount ++;
        
    }

    #Sorts by ascending name then descending amount for bid status function

    //Create an array of bid objects in order based on $liveArray
    $objList = array_column($liveArray, 'Obj');

    //Creates array of userids based on order in $objList
    $userID = array_map(function($e) {
        return $e->getUserID();
    }, $objList);

    //Sorts liveArray in alphabetical order of userid
    array_multisort($userID, SORT_ASC, $liveArray);

    //Creates array of amount based on order in $liveArray
    $amount = array_column($liveArray, 'Bid Price');

    //Sorts livearray in descending order of bid amount
    array_multisort($amount, SORT_DESC, $liveArray);


    $result = [
        'liveArray' => $liveArray,
        'misc' => [
            'CourseInfo' => $check,
            'Vacancies' => $vacancies,
            'BidsNumber' => $processCount,
            'MinBid' => $minimumBidValueDAO->retrieveSpecificValue($code, $biddedSection)
        ]
    ];

    return $result;
}

//Clears bid table at the end of round 2
function clear(){
    $bidDAO = new bidDAO;
    $sectionDAO = new sectionDAO;
    $bidsrejected2DAO = new bidsrejected2DAO;
    $successfulbids2DAO = new successfulbids2DAO;
    $studentDAO = new studentDAO;
    $minimumbidvalueDAO = new minimum_bid_valueDAO;

    //Selects distinct course code and sections from bid table to prevent double counting
    $distinct = $bidDAO->selectDistinct();

    //For each distinct course code and section, gather live array
    foreach($distinct as $pair){
        $miniCode = $pair[0];
        $miniSection = $pair[1];
        $info = displayLive($miniCode, $miniSection)['liveArray'];

        foreach($info as $rank => $arrayInfo){

            //If state of bid is successful, add it to successful bids 2 table
            if ($arrayInfo['State'] == 'Successful'){
                $miniObj = $arrayInfo['Obj'];
                $successfulbids2DAO->add($miniObj);
                $bidDAO->remove($miniObj->getUserID(), $miniObj->getCode(), $miniObj->getSection());
            }

            //If state of bid is not 'successful', add it bids rejected 2 table
            else{
                $bidsrejected2DAO->add($arrayInfo['Obj']);
                $miniObj = $arrayInfo['Obj'];
                $studentDAO->addEdollar($studentDAO->retrieveByID($miniObj->getUserID()), $miniObj->getAmount());
                $bidDAO->remove($miniObj->getUserID(), $miniObj->getCode(), $miniObj->getSection());
            }
        }
    }
}

?>
