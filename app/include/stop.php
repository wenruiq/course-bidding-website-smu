<?php
require_once "common.php";
require_once "round2clearing.php";

function stop(){
    $errors = array();
    $dao = new roundDAO;
    $round1 = $dao->retrieveByRound("round 1");
    $round2 = $dao->retrieveByRound("round 2");
    $round = '';

    //If round 1 status is 'started', execute round1 clearing logic
    if($round1 == "started"){
        $dao->update("round 1", "ended");
        $bidDAO = new bidDAO;
        $sectionDAO = new sectionDAO;
        $bidsRejectedDAO = new bidsrejectedDAO;
        $successfulBidsDAO = new successfulbidsDAO;
        $studentDAO = new studentDAO;

        $bidArray = array();
        $unsuccessfulBids = array();
        $successfulBids = array();
        $allBids = $bidDAO->retrieveAll();


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
        
        
        //Access each section and course
        for($i=0; $i<count($bidArray); $i++){
            $name = array_keys($bidArray)[$i];
            $quota = $sectionDAO->retrieveByCourseSection(explode(" ", $name)[0], explode(" ", $name)[1])->getSize();
            $bidObjList = $bidArray[$name];
            
        
            //Define clearing price
            if (count($bidObjList) >= $quota){
                $clearingPrice = $bidObjList[$quota-1]->getAmount();
            }
        
            else{
                $clearingPrice = 9;
            }
        
            $studentsProcessed = 0;
        
            //Access each bid object in the same section and course
            for($j=0; $j<count($bidObjList); $j++){
                $currentBidderAmount = $bidObjList[$j]->getAmount();
        
                //Validate for minimum price
                if ($currentBidderAmount < 10){
                    $unsuccessfulBids[] = $bidObjList[$j];
                }
        
                elseif ($j < count($bidObjList) -1 && $currentBidderAmount == $bidObjList[$j+1]->getAmount() && $currentBidderAmount <= $clearingPrice){
                    if (!in_array($bidObjList[$j], $unsuccessfulBids)){
                        $unsuccessfulBids[] = $bidObjList[$j];
                    }
        
                    if (!in_array($bidObjList[$j+1], $unsuccessfulBids)){
                        $unsuccessfulBids[] = $bidObjList[$j+1];
                    }
        
        
                }
        
                //Validate for number of bids exceeding class size and bids which do not meet clearing price requirements
                elseif ($studentsProcessed >= $quota || $currentBidderAmount < $clearingPrice){
        
                    $unsuccessfulBids[] = $bidObjList[$j];
        
                }
        
                else{
                    if (!in_array($bidObjList[$j], $unsuccessfulBids)){
                        $successfulBids[] = $bidObjList[$j];
                    }
                }
        
                
        
                $studentsProcessed ++;
            }

        
        }

        //Remove all unsuccessful bids from the bid table and refund respective students eDollar
        if (!empty($unsuccessfulBids)){
            foreach ($unsuccessfulBids as $uBid){
                $bidsRejectedDAO->add($uBid);
                $bidDAO->remove($uBid->getUserID(), $uBid->getCode(), $uBid->getSection());
                $studentDAO->addEdollar($studentDAO->retrieveByID($uBid->getUserID()), $uBid->getAmount());
            }
        }
        
        if (!empty($successfulBids)){
            foreach($successfulBids as $sBid){
                $successfulBidsDAO->add($sBid);
                $bidDAO->remove($sBid->getUserID(), $sBid->getCode(), $sBid->getSection());
            }
        }
    }        

    //If round 2 status is 'started', execute round 2 clearing logic
    elseif($round2 == "started"){
        $dao->update("round 2", "ended");
        clear();

    }

    //Record error message when round is already ended
    elseif($round1 == "ended" || $round2 == "ended"){
    $errors[] = "round already ended";
    }

    //If there are no errors, return success message
    if(count($errors) == 0){
        $result = [
            "status" => "success",
        ];
    }

    //If errors are present, return error messages and error status
    else{
        $result = [
            "status" => "error",
            "message" => $errors
        ];
    }

    return $result;
}
?>