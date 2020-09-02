<?php

class bidsrejected2DAO{

    # Retrieve All
    public function retrieveAll(){

        # SQL Statement
        $sql = 'SELECT * FROM bids_rejected_2';

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
            $result[] = new bidsrejected2($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    //Retrieve bids_rejected_2 object of specific course code
    public function retrieveAllCourse($code){

        # SQL Statement
        $sql = 'SELECT * FROM bids_rejected_2 WHERE code = :code';

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
            $result[] = new bidsrejected2($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    # Add
    public function add($bids_rejected_2){

        #SQL Statement
        $sql = 'INSERT INTO bids_rejected_2 (userid, amount, code, section) VALUES (:userid, :amount, :code, :section)';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":userid", $bids_rejected_2->userID, PDO::PARAM_STR);
        $stmt->bindParam(":amount", $bids_rejected_2->amount, PDO::PARAM_STR);
        $stmt->bindParam(":code", $bids_rejected_2->code, PDO::PARAM_STR);
        $stmt->bindParam(":section", $bids_rejected_2->section, PDO::PARAM_STR);

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
        $sql = 'DELETE FROM bids_rejected_2 WHERE userid = :userid and code = :code and section = :section';

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
        $sql = 'TRUNCATE TABLE bids_rejected_2';

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

    #Retrieve all bids_rejected_2s by userid
    public function retrieveSpecific($userid){

        # SQL Statement
        $sql = 'SELECT * FROM bids_rejected_2 WHERE userid = :userid';

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
            $result[] = new bidsrejected2($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    //Retreive specific bids_rejected_2 object
    public function retrieveSpecificbidsrejected2($userid, $courseCode, $section){

        # SQL Statement
        $sql = 'SELECT * FROM bids_rejected_2 WHERE userid = :userid and code = :code and section = :section';

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
            $result = new bidsrejected2($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    //Retrieve bids_rejected_2 object by userid and course code
    public function retrieveCourse($userid, $code){

        # SQL Statement
        $sql = 'SELECT * FROM bids_rejected_2 WHERE userid = :userid and code = :code';
    
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
            $result[] = new bidsrejected2($row['userid'], $row['amount'], $row['code'], $row['section']);
        }
    
        # Clear
        $stmt = null;
        $conn = null;
    
        return $result;
    
    }

        #Retrieve all bids by section
        public function retrieveByCourseSection($course, $section){

            # SQL Statement
            $sql = 'SELECT * FROM bids_rejected_2 WHERE code = :course and section = :section';
    
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
                $result[] = new bidsrejected2($row['userid'], $row['amount'], $row['code'], $row['section']);
            }
    
            # Clear
            $stmt = null;
            $conn = null;
    
            return $result;
    
        }

}

?>