<?php

class courseDAO{

    # Retrieve All
    public function retrieveAll(){

        # SQL Statement
        $sql = 'SELECT * FROM course';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare & execute
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        # Retrieve results in array of objects
        $result = array();
        while($row = $stmt->fetch()){
            $result[] = new course($row['course'], $row['school'], $row['title'], $row['description'], $row['examdate'], $row['examstart'], $row['examend']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    # Add
    public function add($course){

        #SQL Statement
        $sql = 'INSERT INTO course (course, school, title, description, examdate, examstart, examend) VALUES (:course, :school, :title, :description, :examDate, :examStart, :examEnd)';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":course", $course->course, PDO::PARAM_STR);
        $stmt->bindParam(":school", $course->school, PDO::PARAM_STR);
        $stmt->bindParam(":title", $course->title, PDO::PARAM_STR);
        $stmt->bindParam(":description", $course->description, PDO::PARAM_STR);
        $stmt->bindParam(":examDate", $course->examDate, PDO::PARAM_STR);
        $stmt->bindParam(":examStart", $course->examStart, PDO::PARAM_STR);
        $stmt->bindParam(":examEnd", $course->examEnd, PDO::PARAM_STR);

        $isAddOk = False;
        if($stmt->execute()){
            $isAddOk = True;
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $isAddOk;

    }

    # Retrieve list of course codes by school
    public function retrieveCourseBySchool($school){

        # SQL Statement
        $sql = 'SELECT * FROM course WHERE school = :school';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare & execute
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':school', $school, PDO::PARAM_STR);
        $stmt->execute();

        # Retrieve results in array of objects
        $result = array();
        while($row = $stmt->fetch()){
            $result[] = $row['course'];
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    #Retrieve School by course
    public function retrieveSchoolByCourse($course){

        # SQL Statement
        $sql = "SELECT school FROM course WHERE course = :course";

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();

        # Retrieve results in an array of courses
        $result = array();
        while($row = $stmt->fetch()) {
            $result[] = $row['school'];
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;
    }

    //Retreive course objects corresponding to course code
    public function retrieveByCourseID($course){

        # SQL Statement
        $sql = "SELECT * FROM course WHERE course = :course";

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();

        # Retrieve results in an array of courses
        $result = array();
        while($row = $stmt->fetch()) {
            $result = new course( $row['course'],  $row['school'],  $row['title'],  $row['description'],
            $row['examdate'], $row['examstart'],  $row['examend']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;
    }

    # Remove all (to truncate table)
    public function removeAll(){

        # SQL Statement
        $sql = 'TRUNCATE TABLE course';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare & execute
        $stmt = $conn->prepare($sql);

        $isRemoveAllOk = False;
        if($stmt->execute()){
            $isRemoveAllOk = True;
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $isRemoveAllOk;

    }

}

?>