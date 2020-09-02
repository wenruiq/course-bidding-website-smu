<?php
require_once 'common.php';

// Function to check if student has completed prerequisite course
function checkPrerequisiteCompleted ( $courseid, $studentCompletedCourses, $prereqDict ){
    // Checks if course has a prerequisite
    if (array_key_exists($courseid, $prereqDict)){
        foreach ($prereqDict[$courseid] as $prereq){
            if(!in_array($prereq, $studentCompletedCourses)){
                return False;
            }
        }
    return True;
    }
}

// Function to validate date
function validateDate($date, $format){
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function doBootstrap() {
    # Bootstrap should set current round to round 1
    $round_dao = new roundDAO;
    $round_dao->update("round 1", "started");
    $round_dao->update("round 2", "not started");

    $errors = array();
    # Need tmp_name - a temporary name create for the file and stored inside apache memory folder - for proper read access
    $zip_file = $_FILES["bootstrap-file"]["tmp_name"];

    # Get temp dir on system for uploading
    $temp_dir = sys_get_temp_dir();

    # Keep track of the number of line successfully processed for each file
    $course_processed = 0;
    $section_processed = 0;
    $student_processed = 0;
    $prerequisite_processed = 0;
    $course_completed_processed = 0;
    $bid_processed = 0;

    # Check file size
    if($_FILES["bootstrap-file"]["size"] <= 0)
        $errors[] = "Input files not found";

    else{

        $zip = new ZipArchive;
        $res = $zip->open($zip_file);

        if($res === TRUE){
            $zip->extractTo($temp_dir);
            $zip->close();

            $course_path = "$temp_dir/course.csv";
            $section_path = "$temp_dir/section.csv";
            $student_path = "$temp_dir/student.csv";
            $prerequisite_path = "$temp_dir/prerequisite.csv";
            $course_completed_path = "$temp_dir/course_completed.csv";
            $bid_path = "$temp_dir/bid.csv";

            $course = @fopen($course_path, "r");
            $section = @fopen($section_path, "r");
            $student = @fopen($student_path, "r");
            $prerequisite = @fopen($prerequisite_path, "r");
            $course_completed = @fopen($course_completed_path, "r");
            $bid = @fopen($bid_path, "r");

            if(empty($course) || empty($section) || empty($student) || empty($prerequisite) || empty($course_completed) || empty($bid)){

                $errors[] = "input files not found";
                if(!empty($course)){
                    fclose($course);
                    @unlink($course_path);
                }

                if(!empty($section)){
                    fclose($section);
                    @unlink($section_path);
                }

                if(!empty($student)){
                    fclose($student);
                    @unlink($student_path);
                }

                if(!empty($prerequisite)){
                    fclose($prerequisite);
                    @unlink($prerequisite_path);
                }

                if(!empty($course_completed)){
                    fclose($course_completed);
                    @unlink($course_completed_path);
                }

                if(!empty($bid)){
                    fclose($bid);
                    @unlink($bid_path);
                }


            }
            else{
                # Start validations because files are not empty
                $connMgr = new ConnectionManager();
                $conn = $connMgr->getConnection();

                # Start processing
                # Truncate current SQL tables

                $course_completedDAO = new course_completedDAO();
                $course_completedDAO->removeAll();
                $prerequisiteDAO = new prerequisiteDAO();
                $prerequisiteDAO->removeAll();
                $bidDAO = new bidDAO();
                $bidDAO->removeAll();
                $sectionDAO = new sectionDAO();
                $sectionDAO->removeAll();
                $courseDAO = new courseDAO();
                $courseDAO->removeAll();
                $studentDAO = new studentDAO();
                $studentDAO->removeall();
                $successfulbidsDAO = new successfulbidsDAO;
                $successfulbidsDAO->removeall();
                $bidsrejectedDAO = new bidsrejectedDAO;
                $bidsrejectedDAO->removeall();
                $successfulbids2DAO = new successfulbids2DAO;
                $successfulbids2DAO->removeall();
                $bidsrejected2DAO = new bidsrejected2DAO;
                $bidsrejected2DAO->removeall();
                $minimumbidvalueDAO = new minimum_bid_valueDAO;
                $minimumbidvalueDAO->removeall();

                # Read each csv file row by row (skip the header)
                    // $data = fgetcsv($file) get you the next line of the csv file which will be stored in $data array
                    // $data[0] is the first element in the csv row, $data[1] is the second...

                # For each row
                    // Common validations first
                        // For each field
                            // Remove white spaces from start and end 
                            // If blank, discard current row and go next row

                    // If passed common validations
                        // Run fields through file-specific validations - error message follows project document


                // Variables needed later
                $coursecode_list = array(); // Array(courseid1, courseid2...)
                $userid_list = array(); // Array(userid1, userid2...)
                $prereq_dict = []; // Array(course_a=>[prereq1,prereq2,prereq3], course_b=>[prereq1]...)
                $student_completed = []; // Array(userid_a=>[courseid1,courseid2], userid_b=>[courseid1]...)

                // Begin (student, course, section, prerequisite, course_completed, bid)

                /*
                student.csv
                */
                $data = fgetcsv($student);

                # Keep an array of field names needed for blank field errors later
                $field_names = array();
                for($i = 0; $i < count($data); $i++){
                    $field_names[] = $data[$i];
                }

                # Read row by row
                $current_row = 1;
                while(($data = fgetcsv($student)) != false){

                    $data = str_replace(chr(160), " ", $data);

                    $current_row++;

                    // Initiate required variables
                    $file_specific_errors = 0;
                    $current_error = array();
                    $current_error['file'] = 'student.csv';
                    $current_error['line'] = $current_row;
                    $current_error['message'] = array();

                    // Check for blank fields
                    for($i = 0; $i < count($data); $i++){
                        $field = trim($data[$i]);
                        if($field == ''){
                            $current_error['message'][] = 'blank ' . $field_names[$i];
                        }
                    }
                    
                    // If no blank fields, check for file-specific errors
                    if(count($current_error['message']) > 0){
                        // There's blank fields, end
                        $errors[] = $current_error;
                    }
                    else{
                        // No blank field, continue (userid,password,name,school,edollar)
                        for($i = 0; $i < count($data); $i++){
                            $field = trim($data[$i]); // Trim field

                            // userid
                            if($i == 0){
                                // Check for invalid userid
                                if(strlen($field) > 128){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid userid';
                                }
                                // Check for duplicate userid
                                if(in_array($field, $userid_list)){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'duplicate userid';
                                }
                            }

                            // edollar
                            elseif($i == 4){
                                // Check for invalid e-dollar
                                if(is_numeric($field)){
                                    if($field < 0 || strlen(substr(strrchr($field, "."), 1)) > 2){
                                        $file_specific_errors++;
                                        $current_error['message'][] = 'invalid e-dollar';
                                    }
                                }
                                else{
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid e-dollar';
                                }

                            }

                            // password
                            elseif($i == 1){
                                // Check for invalid password
                                if(strlen($field) > 128){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid password';
                                }
                            }

                            // name
                            elseif($i == 2){
                                // Check for invalid name
                                if(strlen($field) > 100){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid name';
                                }
                            }
                        }
                        // If no errors, add to database
                        if( ($file_specific_errors == 0) && (count($current_error['message']) == 0) ){
                            $userid_list[] = trim($data[0]); // Populate list of userids needed for future validations
                            $student_obj = new student($data[0], $data[1], $data[2], $data[3], $data[4]);
                            $studentDAO->add($student_obj);
                            $student_processed++;
                        }
                        else{
                            $errors[] = $current_error;
                        }
                    }
                }
                // Clean up
                fclose($student);
                @unlink($student_path);


                /*
                course.csv
                */
                $data = fgetcsv($course);

                # Keep an array of field names needed for blank field errors later
                $field_names = array();
                for($i = 0; $i < count($data); $i++){
                    $field_names[] = $data[$i];
                }

                # Read row by row
                $current_row = 1;
                while(($data = fgetcsv($course)) != false){

                    $data = str_replace(chr(160), " ", $data);

                    $current_row++;

                    // Initiate required variables
                    $file_specific_errors = 0;
                    $current_error = array();
                    $current_error['file'] = 'course.csv';
                    $current_error['line'] = $current_row;
                    $current_error['message'] = array();

                    // Check for blank fields
                    for($i = 0; $i < count($data); $i++){
                        $field = trim($data[$i]);
                        if($field == ''){
                            $current_error['message'][] = 'blank ' . $field_names[$i];
                        }
                    }
                    if(count($current_error['message']) > 0){
                        // There's blank fields, end
                        $errors[] = $current_error;
                    }
                    else{
                        // No blank fields, continue (course,school,title,description,exam date,exam start,exam end)
                        for($i = 0; $i < count($data); $i++){
                            $field = trim($data[$i]); // Trim field

                            // exam date
                            if($i == 4){
                                // Check for invalid exam date
                                if(validateDate($field, $format = 'Ymd') == false){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid exam date';
                                }
                            }

                            // exam start
                            elseif($i == 5){
                                // Check for invalid exam start
                                if(strlen($field) < 5){
                                    $field = "0" . $field;
                                }
                                if(validateDate($field, $format = 'H:i') == false){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid exam start';
                                }
                            }

                            // exam end
                            elseif($i == 6){
                                // Check for invalid exam end
                                if(strlen($field) < 5){
                                    $field = "0" . $field;
                                }
                                $exam_start = trim($data[5]);
                                if(strlen($exam_start) < 5){
                                    $exam_start = "0" . $exam_start;
                                }
                                if(validateDate($field, $format = 'H:i') == false || $field <= $exam_start ){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid exam end';
                                }
                            }

                            // title
                            elseif($i == 2){
                                // Check for invalid title
                                if(strlen($field) > 100){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid title';
                                }
                            }

                            // description
                            elseif($i == 3){
                                // Check for invalid description
                                if(strlen($field) > 1000){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid description';
                                }
                            }
                        }
                        // If no errors, add to database
                        if( ($file_specific_errors == 0) && (count($current_error['message']) == 0) ){
                            $coursecode_list[] = trim($data[0]); // Populate list of course codes needed for future validations
                            $courseDAO->add(new course($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]));
                            $course_processed++;
                        }
                        else{
                            $errors[] = $current_error;
                        }
                    }

                }
                // Clean up
                fclose($course);
                @unlink($course_path);


                /*
                section.csv
                */
                $data = fgetcsv($section);

                //Create an associative array to store section objects bootstrapped
                $sectionArray = array();

                # Keep an array of field names needed for blank field errors later
                $field_names = array();
                for($i = 0; $i < count($data); $i++){
                    $field_names[] = $data[$i];
                }

                # Read row by row
                $current_row = 1;
                while(($data = fgetcsv($section)) != false){

                    $data = str_replace(chr(160), " ", $data);

                    $current_row++;

                    // Initiate required variables
                    $file_specific_errors = 0;
                    $current_error = array();
                    $current_error['file'] = 'section.csv';
                    $current_error['line'] = $current_row;
                    $current_error['message'] = array();

                    // Check for blank fields
                    for($i = 0; $i < count($data); $i++){
                        $field = trim($data[$i]);
                        if($field == ''){
                            $current_error['message'][] = 'blank ' . $field_names[$i];
                        }
                    }
                    if(count($current_error['message']) > 0){
                        // There's blank fields, end
                        $errors[] = $current_error;
                    }
                    else{
                        // No blank fields, continue (course,section,day,start,end,instructor,venue,size)
                        for($i = 0; $i < count($data); $i++){
                            $field = trim($data[$i]); // Trim field

                            // course
                            if($i == 0){
                                // Check for invalid course
                                if(!in_array($field, $coursecode_list)){
                                    $file_specific_errors++;
                                    $current_error['message'][] = "invalid course";
                                }

                                // section
                                else{
                                    // Check for invalid section if course is valid
                                    $sect = trim($data[1]);
                                    $sectAlpha = $sect[0];
                                    $sectNum = substr($sect, 1);
                                    if ($sectAlpha != 'S' || !is_numeric($sectNum) || intval($sectNum) < 1 || intval($sectNum) > 99 || $sect[1] == 0){
                                        $file_specific_errors++;
                                        $current_error['message'][] = "invalid section";
                                    }
                                }
                            }

                            // day
                            elseif($i == 2){
                                // Check for invalid day
                                $filter_options = array(
                                    'options' => array('min_range' => 1, 'max_range' => 7)
                                );
                                if (filter_var($field, FILTER_VALIDATE_INT, $filter_options) == false){
                                    $file_specific_errors++;
                                    $current_error['message'][] = "invalid day";
                                }
                            }

                            // start
                            elseif($i == 3){
                                // Check for invalid start
                                if(strlen($field) < 5){
                                    $field = "0" . $field;
                                }
                                if(validateDate($field, $format = 'H:i') == false){
                                    $file_specific_errors++;
                                    $current_error['message'][] = "invalid start";
                                }
                            }

                            // end
                            elseif($i == 4){
                                // Check for invalid end
                                if(strlen($field) < 5){
                                    $field = "0" . $field;
                                }
                                $class_start = trim($data[3]);
                                if(strlen($class_start) < 5){
                                    $class_start = "0" . $class_start;
                                }
                                if(validateDate($field, $format = 'H:i') == false || (validateDate(trim($data[3]),$format = 'H:i' ) && 
                                $field <= $class_start)){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid end';
                                }
                            }

                            // instructor
                            elseif($i == 5){
                                // Check for invalid instructor
                                if(strlen($field) > 100){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid instructor';
                                }
                            }

                            // venue
                            elseif($i == 6){
                                // Check for invalid venue
                                if(strlen($field) > 100){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid venue';
                                }
                            }

                            // size
                            elseif($i == 7){
                                // Check for invalid size
                                $filter_options = array(
                                    'options' => array('min_range' => 1)
                                );
                                if (filter_var($field, FILTER_VALIDATE_INT, $filter_options) == false){
                                    $file_specific_errors++;
                                    $current_error['message'][] = "invalid size";
                                }
                            }
                        }
                        // If no errors, add to database
                        if( ($file_specific_errors == 0) && (count($current_error['message']) == 0) ){
                            $sectionObj = new section(trim($data[0]), trim($data[1]), trim($data[2]), trim($data[3]), trim($data[4]), trim($data[5]), trim($data[6]), trim($data[7]));
                            $sectionDAO->add($sectionObj);
                            $section_processed++;
                            $sectionArray[] = $sectionObj;
                        }
                        else{
                            $errors[] = $current_error;
                        }
                    }

                }
                // Clean up
                fclose($section);
                @unlink($section_path);

                /*
                prerequisite.csv
                */
                $data = fgetcsv($prerequisite);

                # Keep an array of field names needed for blank field errors later
                $field_names = array();
                for($i = 0; $i < count($data); $i++){
                    $field_names[] = $data[$i];
                }

                # Read row by row
                $current_row = 1;
                while(($data = fgetcsv($prerequisite)) != false){

                    $data = str_replace(chr(160), " ", $data);

                    $current_row++;

                    // Initiate required variables
                    $file_specific_errors = 0;
                    $current_error = array();
                    $current_error['file'] = 'prerequisite.csv';
                    $current_error['line'] = $current_row;
                    $current_error['message'] = array();

                    // Check for blank fields
                    for($i = 0; $i < count($data); $i++){
                        $field = trim($data[$i]);
                        if($field == ''){
                            $current_error['message'][] = 'blank ' . $field_names[$i];
                        }
                    }
                    if(count($current_error['message']) > 0){
                        // There's blank fields, end
                        $errors[] = $current_error;
                    }
                    else{
                        // No blank fields, continue (course,prerequisite)
                        for($i = 0; $i < count($data); $i++){
                            $field = trim($data[$i]); // Trim field

                            // course
                            if($i == 0){
                                // Check for invalid course
                                if(!in_array($field, $coursecode_list)){
                                    $file_specific_errors++;
                                    $current_error['message'][] = "invalid course";
                                }
                            }

                            // prerequisite
                            elseif($i == 1){
                                // Check for invalid prerequisite course
                                if(!in_array($field, $coursecode_list)){
                                    $file_specific_errors++;
                                    $current_error['message'][] = "invalid prerequisite";
                                }
                            }
                        }
                        // If no errors, add to database
                        if( ($file_specific_errors == 0) && (count($current_error['message']) == 0) ){
                            $prerequisiteDAO->add(new prerequisite(trim($data[0]), trim($data[1])));
                            $prerequisite_processed++;

                            // Populate dict to record the prereqs of a course
                            if (!array_key_exists(trim($data[0]), $prereq_dict)){
                                $prereq_dict[trim($data[0])] = [trim($data[1])];
                            }
                            else{
                                $prereq_dict[trim($data[0])][] = trim($data[1]);
                            }
                        }
                        else{
                            $errors[] = $current_error;
                        }
                    }
                }
                // Clean up
                fclose($prerequisite);
                @unlink($prerequisite_path);


                /*
                course_completed.csv
                */
                $data = fgetcsv($course_completed);

                # Keep an array of field names needed for blank field errors later
                $field_names = array();
                for($i = 0; $i < count($data); $i++){
                    $field_names[] = $data[$i];
                }

                # Read row by row
                $current_row = 1;
                while(($data = fgetcsv($course_completed)) != false){

                    $data = str_replace(chr(160), " ", $data);

                    $current_row++;

                    // Initiate required variables
                    $file_specific_errors = 0;
                    $current_error = array();
                    $current_error['file'] = 'course_completed.csv';
                    $current_error['line'] = $current_row;
                    $current_error['message'] = array();

                    // Check for blank fields
                    for($i = 0; $i < count($data); $i++){
                        $field = trim($data[$i]);
                        if($field == ''){
                            $current_error['message'][] = 'blank ' . $field_names[$i];
                        }
                    }
                    if(count($current_error['message']) > 0){
                        $errors[] = $current_error;
                    }
                    else{
                        // No blank fields, continue (userid,code)
                        for($i = 0; $i < count($data); $i++){
                            $field = trim($data[$i]); // Trim field

                            // userid
                            if($i == 0){
                                // Check for invalid userid
                                if(!in_array($field, $userid_list)){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid userid';
                                }
                            }

                            // course
                            elseif($i == 1){
                                // Check for invalid course
                                if(!in_array($field, $coursecode_list)){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid course';
                                }
                            }
                        }
                        // If no errors so far, perform further logic validations
                        if(count($current_error['message']) > 0){
                            // Previous validations found errors, end
                            $errors[] = $current_error;
                        }
                        else{
                            // Previous validations passed, continue
                            // Check if student has completed prequisite course
                            if (array_key_exists(trim($data[1]), $prereq_dict)){
                                // Course has prereqs
                                if (array_key_exists(trim($data[0]), $student_completed)){
                                    if (!checkPrerequisiteCompleted(trim($data[1]), $student_completed[trim($data[0])], $prereq_dict)){
                                        $file_specific_errors++;
                                        $current_error['message'][] = 'invalid course completed';
                                    }
                                }
                                else{
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid course completed';
                                }
                            }

                            // If no errors, add to database
                            if( ($file_specific_errors == 0) && (count($current_error['message']) == 0) ){
                                $course_completedDAO->add(new course_completed(trim($data[0]), trim($data[1])));
                                $course_completed_processed++;

                                // Populate dict to record a list of courses completed by student
                                if (!array_key_exists(trim($data[0]), $student_completed)){
                                    $student_completed[trim($data[0])] = [trim($data[1])];
                                }
                                else{
                                    $student_completed[trim($data[0])][] = trim($data[1]);
                                }
                            }
                            else{
                                $errors[] = $current_error;
                            }
                        }                        
                    }
                }
                // Clean up
                fclose($course_completed);
                @unlink($course_completed_path);


                /*
                bid.csv
                */
                $data = fgetcsv($bid);

                // Create an associative array to record student's submitted bids
                $bid_submitted = array(); // Array(userid_a=>[bidobj1, bidobj2], userid_b=>[bidobj1]...)

                # Keep an array of field names needed for blank field errors later
                $field_names = array();
                for($i = 0; $i < count($data); $i++){
                    $field_names[] = $data[$i];
                }

                # Read row by row
                $current_row = 1;
                while(($data = fgetcsv($bid)) != false){

                    $data = str_replace(chr(160), " ", $data);

                    $current_row++;

                    // Initiate required variables
                    $file_specific_errors = 0;
                    $current_error = array();
                    $current_error['file'] = 'bid.csv';
                    $current_error['line'] = $current_row;
                    $current_error['message'] = array();

                    // Check for blank fields
                    for($i = 0; $i < count($data); $i++){
                        $field = trim($data[$i]);
                        if($field == ''){
                            $current_error['message'][] = 'blank ' . $field_names[$i];
                        }
                    }
                    if(count($current_error['message']) > 0){
                        // There's blank fields, end
                        $errors[] = $current_error;
                    }
                    else{
                        // No blank fields, continue (userid,amount,code,section)
                        for($i = 0; $i < count($data); $i++){
                            $field = trim($data[$i]); // Trim field

                            // userid
                            if($i == 0){
                                // Check for invalid userid
                                if(!in_array($field, $userid_list)){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid userid';
                                }
                            }

                            // amount
                            elseif($i == 1){
                                // Check for invalid amount
                                if($field < 10 || strlen(substr(strrchr($field, "."), 1)) > 2){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid amount';
                                }
                            }

                            // course
                            elseif($i == 2){
                                // Check for invalid course
                                if(!in_array($field, $coursecode_list)){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'invalid course';
                                }
                                else{
                                    // Check for invalid section if course is valid
                                    if( !in_array(trim($data[3]), $sectionDAO->retrieveByCourse($field)) ){
                                        $file_specific_errors++;
                                        $current_error['message'][] = 'invalid section';
                                    }
                                }
                            }
                        }
                        if(count($current_error['message']) > 0){
                            // Previous validations found errors, end
                            $errors[] = $current_error;
                        }
                        else{
                            // Previous validations passed, continue
    
                            # Check if student is bidding from his/her own school
                            $studentSchool = $studentDAO->retrieveByID(trim($data[0]))->getSchool();
                            $schoolCourseBelongsTo = $courseDAO->retrieveSchoolByCourse(trim($data[2]));

                            #check if student has already bidded for course. If so, remove bid and refund
                            
                            $studentObj = $studentDAO->retrieveByID(trim($data[0]));
                            if (array_key_exists(trim($data[0]),$bid_submitted)){
                                  $studentObj = $studentDAO->retrieveByID(trim($data[0]));
                                for($i=0; $i<count($bid_submitted[trim($data[0])]); $i++){
                                  
                                    if ($bid_submitted[trim($data[0])][$i] != '' && 
                                    $bid_submitted[trim($data[0])][$i]->getCode() == trim($data[2])){
                                        $tempBid = $bid_submitted[trim($data[0])][$i];
                                        $bidDAO->remove($tempBid->getUserID(), $tempBid->getCode(),
                                        $tempBid->getSection());
                                        $bid_submitted[trim($data[0])][$i] = '';
                                        $studentDAO->addEdollar($studentObj, $tempBid->getAmount());
                                    
                                    }
                                }
                                
                            }
                            
                            if(!in_array($studentSchool, $schoolCourseBelongsTo)){
                                $file_specific_errors++;
                                $current_error['message'][] = 'not own school course';
                            }
    
                            # Check if student has already placed a bid
                            if (array_key_exists(trim($data[0]), $bid_submitted)){
                                # Check if class clashes
                                # Retrieve day, start and end class timings for course student is bidding for
                                $sectionObj = $sectionDAO->retrievebyCourseSection(trim($data[2]), trim($data[3]));
                                $sectionDay = $sectionObj->getDay();
                                $startTime = $sectionObj->getStart();
                                $endTime = $sectionObj->getEnd();

                                foreach ($bid_submitted[trim($data[0])] as $bidded){
                                    if ($bidded !== '' && $bidded->getCode() !== trim($data[2])){
                                        $biddedObj = $sectionDAO->retrievebyCourseSection($bidded->getCode(), $bidded->getSection());
                                        if($sectionDay == $biddedObj->getDay()){
                                            # Retrieve start and end class time for that bidded course
                                            $biddedStart = $biddedObj->getStart();
                                            $biddedEnd = $biddedObj->getEnd();
                                            if (($startTime > $biddedStart && $startTime < $biddedEnd) || 
                                            ($endTime > $biddedStart && $endTime < $biddedEnd ) ||
                                            $startTime == $biddedStart && $endTime == $biddedEnd){
                                                $file_specific_errors++;
                                                $current_error['message'][] = 'class timetable clash';
                                            }
                                        }
                                    }
                                }
    
                                # Check if exam clashes
                                # Retrieve day, start and end exam timings for course student is bidding for
                                $courseObj = $courseDAO->retrieveByCourseID(trim($data[2]));
                                $examDate = $courseObj->getExamDate();
                                $examStart = $courseObj->getExamStart();
                                $examEnd = $courseObj->getExamEnd();
                                
                                foreach ($bid_submitted[trim($data[0])] as $bidded){
                                    if ($bidded != '' && $bidded->getCode() !== trim($data[2])){
                                        $biddedCourseObj = $courseDAO->retrievebyCourseID($bidded->getCode());
                                        
                                        if ($examDate == $biddedCourseObj->getExamDate()){
                                            #retrieve start and end exam timings for course student has bidded for
                                            $biddedExamStart = $biddedCourseObj->getExamStart();
                                            $biddedExamEnd = $biddedCourseObj->getExamEnd();

                                            if ( ($examStart > $biddedExamStart && $examStart < $biddedExamEnd) || 
                                            ($examEnd > $biddedExamStart && $examEnd < $biddedExamEnd ) || 
                                            $examStart == $biddedExamStart && $examEnd == $biddedExamEnd){
                                                $file_specific_errors++;
                                                $current_error['message'][] = 'exam timetable clash';
                                            }
                                        }
                                    }
                                }
                            }
    
                            # Check if prereq completed for this bid
                            if(array_key_exists(trim($data[2]), $prereq_dict)){
                                if(array_key_exists(trim($data[0]), $student_completed)){
                                    if(!checkPrerequisiteCompleted(trim($data[2]), $student_completed[trim($data[0])], $prereq_dict)){
                                        $file_specific_errors++;
                                        $current_error['message'][] = 'incomplete prerequisites';
                                    }
                                }
                                else{
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'incomplete prerequisites';   
                                }
                            }

                            # Check if bidding for course already completed
                            if (array_key_exists(trim($data[0]), $student_completed)){
                                if (in_array(trim($data[2]), $student_completed[trim($data[0])])){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'course completed';
                                }
                            }

                            # Check if student has already bidded for 5 sections
                            if (array_key_exists(trim($data[0]), $bid_submitted)){
                                $count = 0;
                                foreach ($bid_submitted[trim($data[0])] as $target){
                                    if ($target != ''){
                                        $count ++;
                                    }
                                }
                                if ($count == 5){
                                    $file_specific_errors++;
                                    $current_error['message'][] = 'section limit reached';
                                }
                            }

                            # Check if enough edollar for this bid
                            if (trim($data[1]) > $studentDAO->retrieveByID(trim($data[0]))->getEdollar()){
                                $file_specific_errors++;
                                $current_error['message'][] = 'not enough e-dollar';
                            }

                            // If no file_specific_errors, add to database
                            if( ($file_specific_errors == 0) && (count($current_error['message']) == 0) ){
                                $bid_obj = new bid(trim($data[0]), trim($data[1]), trim($data[2]), trim($data[3]));
                               

                                //Deduct bid amount from student's eDollar records
                                //TO DO: account for bidding rounds and refund from cancellation
                                $studentObj = $studentDAO->retrieveByID(trim($data[0]));
                                $bidDAO->add($bid_obj);
                                $bid_processed++;

                                $studentDAO->deductEdollar($studentObj,trim($data[1]));

                                $tempBid = '';
  

                                // If student does not exist in associative array, add name and bid into it.
                                // If not, add on to existing array.
                                if (array_key_exists(trim($data[0]), $bid_submitted)){
                                    $bid_submitted[trim($data[0])][] = $bid_obj;
                                }
                                else{
                                    $bid_submitted[trim($data[0])] = [$bid_obj];
                                }
                                
                            }
                            
                            else{
                                if (!empty($tempBid)){
                                    $studentObj = $studentDAO->retrieveByID(trim($data[0]));
                                    $bidDAO->add($tempBid);
                                    $studentDAO->deductEdollar($studentObj, $tempBid->getAmount());
                                    $tempBid = ''; 
                                }
                                $errors[] = $current_error;
                            }
                          

                        }
                    }                       
                }
                // Clean up
                fclose($bid);
                @unlink($bid_path);
            }
        }
    }

    # Populate $result for json checker

    if(count($errors) != 0){
        # Sort ordered by file (alphabetical), then by line number
        $file = array_column($errors, "file");
        $line = array_column($errors, "line");
        array_multisort($file, SORT_ASC, $line, SORT_ASC, $errors);

        $result = [
            "status" => "error",
            "num-record-loaded" => [
                ["bid.csv" => $bid_processed],
                ["course.csv" => $course_processed],
                ["course_completed.csv" => $course_completed_processed],
                ["prerequisite.csv" => $prerequisite_processed],
                ["section.csv" => $section_processed],
                ["student.csv" => $student_processed]
            ],
            "error" => $errors
        ];
    }
    else{
        $round_dao->update("round 1", "started");
        $round_dao->update("round 2", "not started");
        $result = [
            "status" => "success",
            "num-record-loaded" => array (
                array("bid.csv" => $bid_processed),
                ["course.csv" => $course_processed],
                ["course_completed.csv" => $course_completed_processed],
                ["prerequisite.csv" => $prerequisite_processed],
                ["section.csv" => $section_processed],
                ["student.csv" => $student_processed]
            ),
        ];
    }

    return $result;
}
?>