<?php

class successfulbidsDAO{

    # Retrieve All
    public function retrieveAll(){

        # SQL Statement
        $sql = 'SELECT * FROM successful_bids';

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
            $result[] = new successfulbids($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    //Rretieve all successful bid object correspondoing to course code
    public function retrieveAllCourse($code){

        # SQL Statement
        $sql = 'SELECT * FROM successful_bids WHERE code = :code';

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
            $result[] = new successfulbids($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    # Add
    public function add($successful_bids){

        #SQL Statement
        $sql = 'INSERT INTO successful_bids (userid, amount, code, section) VALUES (:userid, :amount, :code, :section)';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":userid", $successful_bids->userID, PDO::PARAM_STR);
        $stmt->bindParam(":amount", $successful_bids->amount, PDO::PARAM_STR);
        $stmt->bindParam(":code", $successful_bids->code, PDO::PARAM_STR);
        $stmt->bindParam(":section", $successful_bids->section, PDO::PARAM_STR);

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
        $sql = 'DELETE FROM successful_bids WHERE userid = :userid and code = :code and section = :section';

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
        $sql = 'TRUNCATE TABLE successful_bids';

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

    #Retrieve all successful_bidss by userid
    public function retrieveSpecific($userid){

        # SQL Statement
        $sql = 'SELECT * FROM successful_bids WHERE userid = :userid';

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
            $result[] = new successfulbids($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    //Retrieve specific successful bid object
    public function retrieveSpecificSuccessfulBid($userid, $courseCode, $section){

        # SQL Statement
        $sql = 'SELECT * FROM successful_bids WHERE userid = :userid and code = :code and section = :section';

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
            $result = new successfulbids($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    //Retrieve successful bid object corresponding to specific userid and course code
    public function retrieveCourse($userid, $code){

        # SQL Statement
        $sql = 'SELECT * FROM successful_bids WHERE userid = :userid and code = :code';

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
            $result[] = new successfulbids($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    //Counts number of bids in successful bids table corresponding to specifed course code and section
    public function count($courseCode, $section){
        # SQL Statement
        $sql = 'SELECT * FROM successful_bids WHERE section = :section and code = :code';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare & execute
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":section", $section, PDO::PARAM_STR);
        $stmt->bindParam(":code", $courseCode, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        # Retrieve results in array of objects
        $result = 0;
        while($row = $stmt->fetch()){
            $result ++;
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    #Retrieve all bids by section and course code
    public function retrieveByCourseSection($course, $section){

        # SQL Statement
        $sql = 'SELECT * FROM successful_bids WHERE code = :course and section = :section';

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
            $result[] = new successfulbids($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }


}



?>