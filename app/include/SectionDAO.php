<?php

class sectionDAO{

    # Retrieve All
    public function retrieveAll(){

        # SQL Statement
        $sql = 'SELECT * FROM section';

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
            $result[] = new section ($row['course'], $row['section'], $row['day'], $row['start'], $row['end'], $row['instructor'], 
            $row['venue'], $row['size']);
        }
        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    # Retrieve Sections
    public function retrieveListOfSections(){
        $sql = 'SELECT DISTINCT section FROM section';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row['section'];
        }
        return $result;
    }

    #Retrieve by course
    public function retrieveByCourse($course){

        # SQL Statement
        $sql = "SELECT section FROM section WHERE course = :course";

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();

        # Retrieve results in an array of sections
        $result = array();
        while($row = $stmt->fetch()) {
            $result[] = $row['section'];
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;
    }

    //retreive all sections by course
    public function retrieveSectionsByCourse($course){

        # SQL Statement
        $sql = "SELECT * FROM section WHERE course = :course";

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();

        # Retrieve results in an array of sections
        $result = array();
        while($row = $stmt->fetch()) {
            $result[] = new section ($row['course'], $row['section'], $row['day'], $row['start'], $row['end'], $row['instructor'], 
            $row['venue'], $row['size']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;
    }

    //retrieve specific secction object by course and session
    public function retrieveByCourseSection($course,$section){

        # SQL Statement
        $sql = "SELECT * FROM section WHERE course = :course AND section = :section";

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->execute();

        # Retrieve results in an array of sections
        $result = array();
        while($row = $stmt->fetch()) {
            $result = new section ($row['course'], $row['section'], $row['day'], $row['start'], $row['end'], $row['instructor'], 
            $row['venue'], $row['size']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;
    }

    # Add
    public function add($section){

        #SQL Statement
        $sql = 'INSERT INTO section (course, section, day, start, end, instructor, venue, size) VALUES (:course, :section, :day, :start, :end, :instructor, :venue, :size)';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":course", $section->course, PDO::PARAM_STR);
        $stmt->bindParam(":section", $section->section, PDO::PARAM_STR);
        $stmt->bindParam(":day", $section->day, PDO::PARAM_STR);
        $stmt->bindParam(":start", $section->start, PDO::PARAM_STR);
        $stmt->bindParam(":end", $section->end, PDO::PARAM_STR);
        $stmt->bindParam(":instructor", $section->instructor, PDO::PARAM_STR);
        $stmt->bindParam(":venue", $section->venue, PDO::PARAM_STR);
        $stmt->bindParam(":size", $section->size, PDO::PARAM_STR);

        $isAddOk = False;
        if($stmt->execute()){
            $isAddOk = True;
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $isAddOk;

    }

    # Remove all (to truncate table)
    public function removeAll(){

        # SQL Statement
        $sql = 'TRUNCATE TABLE section';

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