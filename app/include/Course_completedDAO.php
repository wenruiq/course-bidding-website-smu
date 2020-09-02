<?php

class course_completedDAO{

    # Retrieve All
    public function retrieveAll(){
        
        # SQL Statement
        $sql = 'SELECT * FROM course_completed';

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
            $result[] = new course_completed($row['userid'], $row['code']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    #Retrieve courses completed by userid
    public function retrieveCoursesCompletedByID($userID){
        
        # SQL Statement
        $sql = 'SELECT code FROM course_completed where userid = :userid';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare & execute
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":userid", $userID, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        # Retrieve results in array of objects
        $result = array();
        while($row = $stmt->fetch()){
            $result[] = $row['code'];
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    # Add
    public function add($Course_completed){

        #SQL Statement
        $sql = 'INSERT INTO course_completed (userid, code) VALUES (:userid, :code)';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":userid", $Course_completed->userID, PDO::PARAM_STR);
        $stmt->bindParam(":code", $Course_completed->code, PDO::PARAM_STR);
        
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
        $sql = 'TRUNCATE TABLE course_completed';

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