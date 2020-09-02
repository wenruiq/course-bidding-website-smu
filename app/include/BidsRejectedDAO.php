<?php

class bidsrejectedDAO{

    # Retrieve All
    public function retrieveAll(){

        # SQL Statement
        $sql = 'SELECT * FROM bids_rejected';

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
            $result[] = new bidsrejected($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    //Retrieve all bids_rejected object corresponding to course code
    public function retrieveAllCourse($code){

        # SQL Statement
        $sql = 'SELECT * FROM bids_rejected WHERE code = :code';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare & execute
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(":code", $code, PDO::PARAM_STR);
        $stmt->execute();

        # Retrieve results in array of objects
        $result = array();
        while($row = $stmt->fetch()){
            $result[] = new bidsrejected($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    # Add
    public function add($bids_rejected){

        #SQL Statement
        $sql = 'INSERT INTO bids_rejected (userid, amount, code, section) VALUES (:userid, :amount, :code, :section)';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":userid", $bids_rejected->userID, PDO::PARAM_STR);
        $stmt->bindParam(":amount", $bids_rejected->amount, PDO::PARAM_STR);
        $stmt->bindParam(":code", $bids_rejected->code, PDO::PARAM_STR);
        $stmt->bindParam(":section", $bids_rejected->section, PDO::PARAM_STR);

        $isAddOk = False;
        if($stmt->execute()){
            $isAddOk = True;
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $isAddOk;

    }

    # Remove
    public function remove($username, $courseCode, $section){

        #SQL Statement
        $sql = 'DELETE FROM bids_rejected WHERE userid = :userid and code = :code and section = :section';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);


        $stmt->bindParam(":code", $courseCode, PDO::PARAM_STR);
        $stmt->bindParam(":section", $section, PDO::PARAM_STR);
        $stmt->bindParam(":userid", $username, PDO::PARAM_STR);

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
        $sql = 'TRUNCATE TABLE bids_rejected';

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

    #Retrieve all bids_rejecteds by user
    public function retrieveSpecific($userid){

        # SQL Statement
        $sql = 'SELECT * FROM bids_rejected WHERE userid = :userid';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare & execute
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        # Retrieve results in array of objects
        $result = array();
        while($row = $stmt->fetch()){
            $result[] = new bidsrejected($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    //Retrieve specfific bids_rejected object
    public function retrieveSpecificBidsRejected($userid, $courseCode, $section){

        # SQL Statement
        $sql = 'SELECT * FROM bids_rejected WHERE userid = :userid and code = :code and section = :section';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare & execute
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->bindParam(":code", $courseCode, PDO::PARAM_STR);
        $stmt->bindParam(":section", $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        # Retrieve results in array of objects
        $result = '';

        while($row = $stmt->fetch()){
            $result = new bidsrejected($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    //Retrieve bids_rejected object by userid and course code
    public function retrieveCourse($userid, $code){

        # SQL Statement
        $sql = 'SELECT * FROM bids_rejected WHERE userid = :userid and code = :code';
    
        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
    
        # Prepare & execute
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->bindParam(":code", $code, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
    
        # Retrieve results in array of objects
        $result = array();
        while($row = $stmt->fetch()){
            $result[] = new bidsrejected($row['userid'], $row['amount'], $row['code'], $row['section']);
        }
    
        # Clear
        $stmt = null;
        $conn = null;
    
        return $result;
    
    }

        #Retrieve all bids by section
        public function retrieveByCourseSection($course, $section){

            # SQL Statement
            $sql = 'SELECT * FROM bids_rejected WHERE code = :course and section = :section';
    
            # Connect
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();
    
            # Prepare & execute
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":course", $course, PDO::PARAM_STR);
            $stmt->bindParam(":section", $section, PDO::PARAM_STR);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute();
    
            # Retrieve results in array of objects
            $result = array();
            while($row = $stmt->fetch()){
                $result[] = new bidsrejected($row['userid'], $row['amount'], $row['code'], $row['section']);
            }
    
            # Clear
            $stmt = null;
            $conn = null;
    
            return $result;
    
        }

}

?>