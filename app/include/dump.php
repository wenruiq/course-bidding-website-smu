<?php
require_once "common.php";


function dump(){

    # create array for errors
    $errors = array();

    # Populate essential variables
    $course_dao = new courseDAO;
    $section_dao = new sectionDAO;
    $student_dao = new studentDAO;
    $prerequisite_dao = new prerequisiteDAO;
    $course_completed_dao = new course_completedDAO;
    $bid_dao = new bidDAO;
    $successful_bids_dao = new successfulbidsDAO;
    $rejected_bids_dao = new bidsrejectedDAO;
    $round_dao = new roundDAO;
    $courses = array();
    $sections = array(); 
    $students = array(); 
    $prerequisites = array(); 
    $course_completeds = array(); 
    $bids = array();
    $successful_bids = array();
    $rejected_bids = array();

    # Change arrays of objects to arrays of assoc arrays
    $data = $course_dao->retrieveAll(); 
    $json = json_encode($data);
    $courses = json_decode($json, true);
    $courses_results = array();
    for($i=0; $i<count($courses); $i++){
        $courses_results[$i]['course'] = $courses[$i]['course'];
        $courses_results[$i]['school'] = $courses[$i]['school'];
        $courses_results[$i]['title'] = $courses[$i]['title'];
        $courses_results[$i]['description'] = $courses[$i]['description'];
        $courses_results[$i]['exam date'] = str_replace("-", "", $courses[$i]['examDate']);
        $data = str_replace(":", "", $courses[$i]['examStart']);
        $data = substr($data, 0, -2);
        if(substr($data,0,1) == "0"){
            $data = substr($data, 1,3);
        }$courses_results[$i]['exam start'] = $data;
        $data = str_replace(":", "", $courses[$i]['examEnd']);
        $data = substr($data, 0, -2);
        if(substr($data,0,1) == "0"){
            $data = substr($data, 1,3);
        }$courses_results[$i]['exam end'] = $data;
    }

    $data = $section_dao->retrieveAll(); 
    $json = json_encode($data);
    $sections = json_decode($json, true);
    $sections_results = array();
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    for($i=0; $i<count($sections); $i++){
        $sections_results[$i]['course'] = $sections[$i]['course'];
        $sections_results[$i]['section'] = $sections[$i]['section'];
        $sections_results[$i]['day'] = $days[$sections[$i]['day']-1];
        $data = str_replace(":", "", $sections[$i]['start']);
        $data = substr($data, 0, -2);
        if(substr($data,0,1) == "0"){
            $data = substr($data, 1,3);
        }$sections_results[$i]['start'] = $data;
        $data = str_replace(":", "", $sections[$i]['end']);
        $data = substr($data, 0, -2);
        if(substr($data,0,1) == "0"){
            $data = substr($data, 1,3);
        }$sections_results[$i]['end'] = $data;
        $sections_results[$i]['instructor'] = $sections[$i]['instructor'];
        $sections_results[$i]['venue'] = $sections[$i]['venue'];
        $sections_results[$i]['size'] = intval($sections[$i]['size']);
    }

    $data = $student_dao->retrieveAll(); 
    $json = json_encode($data);
    $students = json_decode($json, true);
    $students_results = array();
    for($i=0; $i<count($students); $i++){
        $students_results[$i]['userid'] = $students[$i]['userID'];
        $students_results[$i]['password'] = $students[$i]['password'];
        $students_results[$i]['name'] = $students[$i]['name'];
        $students_results[$i]['school'] = $students[$i]['school'];
        $students_results[$i]['edollar'] = $students[$i]['edollar'];
    }

    $data = $prerequisite_dao->retrieveAll(); 
    $json = json_encode($data);
    $prerequisites = json_decode($json, true);

    $data = $course_completed_dao->retrieveAll(); 
    $json = json_encode($data);
    $course_completeds = json_decode($json, true);


    $data = $bid_dao->retrieveAll(); 
    $json = json_encode($data);
    $bids = json_decode($json, true);

    $data = $successful_bids_dao->retrieveAll(); 
    $json = json_encode($data);
    $successful_bids = json_decode($json, true);

    $data = $rejected_bids_dao->retrieveAll();
    $json = json_encode($data);
    $rejected_bids = json_decode($json, true);

    $combined_bids = $successful_bids + $rejected_bids;

    
    $bids_results = array();
    $section_student = array();


    $data = $round_dao->retrieveAll(); 
    $json = json_encode($data);
    $roundarray = json_decode($json, true);

    $round = array();
    foreach($roundarray as $r){
        $round[$r['roundnum']] = $r['status'];
    }


    if (($round['round 1'] == 'started' && $round['round 2'] == 'not started') ||
    ($round['round 1'] == 'ended' && $round['round 2'] == 'started') ){
        for($i=0; $i<count($bids); $i++){
            $bids_results[$i]['userid'] = $bids[$i]['userID'];
            $bids_results[$i]['amount'] = $bids[$i]['amount'];
            $bids_results[$i]['course'] = $bids[$i]['code'];
            $bids_results[$i]['section'] = $bids[$i]['section'];
        }
    }
    elseif (($round['round 1'] == 'ended' && $round['round 2'] == 'not started') ||
    ($round['round 1'] == 'ended' && $round['round 2'] == 'ended') ){
        for($i=0; $i<count($combined_bids); $i++){
            $bids_results[$i]['userid'] = $combined_bids[$i]['userID'];
            $bids_results[$i]['amount'] = $combined_bids[$i]['amount'];
            $bids_results[$i]['course'] = $combined_bids[$i]['code'];
            $bids_results[$i]['section'] = $combined_bids[$i]['section'];
        }
        for($i=0; $i<count($successful_bids); $i++){
            $section_student[$i]['userid'] = $successful_bids[$i]['userID'];
            $section_student[$i]['course'] = $successful_bids[$i]['code'];
            $section_student[$i]['section'] = $successful_bids[$i]['section'];
            $section_student[$i]['amount'] = $successful_bids[$i]['amount'];
        }


    }
    


    # create function to help sort in alphabetical order
    $alphabets = 'abcdefghijklmnopqrstuvwxyz';
    function a_order($array,$key){
        $nlist = array();
        $alphabets = 'abcdefghijklmnopqrstuvwxyz';
        foreach ($array as $a){
            $substr = substr($a[$key],0,1);
            $index = strpos($alphabets, strtolower($substr));
            if (in_array($index, $nlist)==false){
                array_push($nlist, $index);
            }
        }
        sort($nlist);
        return $nlist;
    }


    # arrange course
    # get a list of index that indicates alphabetical order of course 
    $courselist = a_order($courses_results,"course");
    # get an associative aray that arrange course based on different schools
    $subsorted_course = array();
    foreach($courselist as $n){
        foreach($courses_results as $c){
            $substr_course = substr($c["course"],0,1);
            $courseid = substr($c["course"],-3);
            $index = strpos($alphabets, strtolower($substr_course));
            if ($n == $index){
                $subsorted_course[$n][$courseid]=$c;
    }}}
    # sort records in numerical order
    $sorted_course = array();
    foreach($subsorted_course as $sub){
        sort($sub);
        foreach($sub as $s){
            $sorted_course[]=$s;
        }
    }
    $courses_results = $sorted_course;



    # arrange userid
    $ulist = a_order($students_results,"userid");
    $sorted_student = array();
    foreach($ulist as $n){
        foreach($students_results as $s){
            $substr_student = substr($s["userid"],0,1);
            $index = strpos($alphabets, strtolower($substr_student));
            if ($n == $index){
                array_push($sorted_student,$s);
    }}}
    $students_results = $sorted_student;


    # arrange section
    $courselist = a_order($sections_results,"course");
    $subsorted_section = array();
    foreach($courselist as $n){
        foreach($sections_results as $c){
            $course = $c["course"];
            $substr_section = substr($c["course"],0,1);
            $sectionid = substr($c["section"],-1);
            $index = strpos($alphabets, strtolower($substr_section));
            if ($n == $index){
                $subsorted_section[$n][$course][$sectionid]=$c;
    }}}
    $sorted_section = array();
    foreach($subsorted_section as $sub){
        foreach($sub as $s1){
            sort($sub);
            foreach($s1 as $s2){
                $sorted_section[]=$s2;
    }}}
    $sections_results = $sorted_section;


    # arrange prerequisite
    $courselist = a_order($prerequisites,"course");
    $subsorted_prereq = array();
    foreach($courselist as $n){
        foreach($prerequisites as $c){
            $substr = substr($c["course"],0,1);
            $id1 = $c["course"];
            $id2 = $c["prerequisite"];
            $index = strpos($alphabets, strtolower($substr));
            if ($n == $index){
                $subsorted_prereq[$n][$id1][$id2]=$c;
    }}}
    $sorted_prereq = array();
    foreach($subsorted_prereq as $sub){
        foreach($sub as $s1){
            sort($s1);
            foreach($s1 as $s2){
                $sorted_prereq[]=$s2;
    }}}
    $prerequisites = $sorted_prereq;


    # arrange course_completed
    $ulist = a_order($course_completeds,"code");
    $ulist2 = a_order($course_completeds,"userID");
    $subsorted_cc = array();
    foreach($ulist as $n){
        foreach($course_completeds as $s){
            $substr1 = substr($s["code"],0,1);
            $id2 = $s["code"];
            $index = strpos($alphabets, strtolower($substr1));
            if ($n == $index){
                foreach($ulist2 as $l){
                    $substr2 = substr($s["userID"],0,1);
                    $id1 = $s["userID"];
                    $index = strpos($alphabets, strtolower($substr2));
                    if ($l == $index){
                        $subsorted_cc[$n][$id2][$l][$id1]=$s;
    }}}}}
    $sorted_cc = array();
    foreach($subsorted_cc as $sub){
        foreach($sub as $s1){
            foreach($s1 as $s2){
                sort($s2);
                foreach($s2 as $s3){
                    $sorted_cc[]=$s3;
    }}}}
    $course_completeds = $sorted_cc;


    # arrange bid
    $courselist = a_order($bids_results, "course");
    $subsorted_bid = array();
    $codelist = array();
    foreach($courselist as &$n){
        foreach($bids_results as $c){
            $substr1 = substr($c["course"],0,1);
            $index = strpos($alphabets, strtolower($substr1));
            if ($n == $index){
                $code = substr($c["course"],-3);
                if(in_array($code, $codelist)==FALSE){
                    $codelist[]=$code;
                }
            }
        }
        sort($codelist);
    }
    foreach($courselist as &$n){
        foreach ($codelist as &$c){
            foreach ($bids_results as &$bid){
                $substr1 = substr($bid["course"],0,1);
                $code = substr($bid["course"],-3);
                $index = strpos($alphabets, strtolower($substr1));
                $sectionid = substr($bid["section"],-1);
                $amount = $bid["amount"];
                if ($n==$index && $c==$code){
                    if (empty($subsorted_bid[$n][$code][$sectionid])){
                        $subsorted_bid[$n][$code][$sectionid][$amount][]=$bid;
                    }
                    elseif(in_array($bid, $subsorted_bid[$n][$code][$sectionid])==FALSE ){
                        $subsorted_bid[$n][$code][$sectionid][$amount][]=$bid;
                    }
                }
            }
        }
    }
    $sorted_bid = array();
    foreach($subsorted_bid as &$sub){
        foreach($sub as &$s1){
            foreach($s1 as &$s2){
                krsort($s2);
                foreach($s2 as $s3){
                    $sorted_bid[]=$s3;
                }
            }
        }
    }
    $final_bids = array();
    foreach($sorted_bid as $s1){
        foreach($s1 as $s2){
            $final_bids[] = $s2;
        }
    }
    $bids_results = $final_bids;

 


    # arrange section-student
    $courselist = a_order($section_student, "course");
    $subsorted_sb = array();
    $codelist = array();
    foreach($courselist as &$n){
        foreach($section_student as $c){
            $substr1 = substr($c["course"],0,1);
            $index = strpos($alphabets, strtolower($substr1));
            if ($n == $index){
                $code = substr($c["course"],-3);
                if(in_array($code, $codelist)==FALSE){
                    $codelist[]=$code;
                }
            }
        }
        sort($codelist);
    }

    foreach($courselist as &$n){
        foreach($codelist as &$c){
            foreach($section_student as $ss){
                $substr1 = substr($ss["course"],0,1);
                $code = substr($ss["course"],-3);
                $index = strpos($alphabets, strtolower($substr1));
                if ($n==$index && $c==$code){
                    $substr2 = substr($ss["userid"],0,1);
                    $index2 = strpos($alphabets, strtolower($substr2));
                    $subsorted_sb[$n][$c][$index2][]=$ss;
                    if (empty($subsorted_sb[$n][$c][$index2])){
                        $subsorted_sb[$n][$c][$index2][]=$ss;
                    }
                    elseif(in_array($ss, $subsorted_sb[$n][$c][$index2])==FALSE ){
                        $subsorted_sb[$n][$c][$index2][]=$ss;
                    }
                }
            }
        }
    }
    $sorted_sb = array();
    foreach($subsorted_sb as &$sub){
        foreach($sub as &$s1){
            foreach($s1 as &$s2){
                ksort($s2);
                foreach($s2 as $s3){
                    $sorted_sb[]=$s3;
                }
            }
        }
    }
    $section_student = $sorted_sb;



    # show output only when there's no error
    if(count($errors) == 0){
        $results = [
            "status" => "success",
            "course" => $courses_results,
            "section" => $sections_results,
            "student" => $students_results,
            "prerequisite" => $prerequisites,
            "bid" => $bids_results,
            "completed-course" => $course_completeds,
            "section-student" => $section_student,
        ];
    }
    else{
        $results = [
            "status" => "error"
        ];
    }
    return $results;
}


?>