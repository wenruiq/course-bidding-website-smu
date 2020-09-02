<?php

class prerequisiteDAO{

    # Retrieve All
    public function retrieveAll(){
        
        # SQL Statement
        $sql = 'SELECT * FROM prerequisite';

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
            $result[] = new prerequisite($row['course'], $row['prerequisite']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    # Add
    public function add($prerequisite){

        #SQL Statement
        $sql = 'INSERT INTO prerequisite (course, prerequisite) VALUES (:course, :prerequisite)';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":course", $prerequisite->course, PDO::PARAM_STR);
        $stmt->bindParam(":prerequisite", $prerequisite->prerequisite, PDO::PARAM_STR);
        
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
        $sql = 'TRUNCATE TABLE prerequisite';

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

    # Retrieve prerequisities for specific course code
    public function retrievePrereq($courseid){
        
        # SQL Statement
        $sql = 'SELECT * FROM prerequisite WHERE course = :course';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare & execute
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":course", $courseid, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        # Retrieve results in array of objects
        $result = array();
        while($row = $stmt->fetch()){
            $result[] = $row['prerequisite'];
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

}

?>