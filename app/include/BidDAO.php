<?php

class bidDAO{

    # Retrieve All
    public function retrieveAll(){
        
        # SQL Statement
        $sql = 'SELECT * FROM bid';

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
            $result[] = new bid($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    //Retrieve all bid objects of specific course code
    public function retrieveAllCourse($code){

        # SQL Statement
        $sql = 'SELECT * FROM bid WHERE code = :code';

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
            $result[] = new bid($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    # Add
    public function add($bid){

        #SQL Statement
        $sql = 'INSERT INTO bid (userid, amount, code, section) VALUES (:userid, :amount, :code, :section)';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":userid", $bid->userID, PDO::PARAM_STR);
        $stmt->bindParam(":amount", $bid->amount, PDO::PARAM_STR);
        $stmt->bindParam(":code", $bid->code, PDO::PARAM_STR);
        $stmt->bindParam(":section", $bid->section, PDO::PARAM_STR);
        
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
        $sql = 'DELETE FROM bid WHERE userid = :userid and code = :code and section = :section';

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

    //Update bid
    public function update($userid, $amount, $code, $section){

        #SQL Statement
        $sql = 'UPDATE bid SET userid = :userid and amount = :amount and code = :code and section = :section WHERE
        userid = :userid and code = :code';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);


        $stmt->bindParam(":code", $courseCode, PDO::PARAM_STR);
        $stmt->bindParam(":amount", $amount, PDO::PARAM_STR);
        $stmt->bindParam(":section", $section, PDO::PARAM_STR);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        
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
        $sql = 'TRUNCATE TABLE bid';

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

    #Retrieve all bids by user
    public function retrieveSpecific($userid){

        # SQL Statement
        $sql = 'SELECT * FROM bid WHERE userid = :userid';

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
            $result[] = new bid($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    // Retrieve a specific bid object
    public function retrieveSpecificBid($userid, $courseCode, $section){

        # SQL Statement
        $sql = 'SELECT * FROM bid WHERE userid = :userid and code = :code and section = :section';

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
            $result = new bid($row['userid'], $row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    //Retrieve all bid objects user has bidded for the course
    public function retrieveCourse($userid, $code){

        # SQL Statement
        $sql = 'SELECT * FROM bid WHERE userid = :userid and code = :code';
    
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
            $result[] = new bid($row['userid'], $row['amount'], $row['code'], $row['section']);
        }
    
        # Clear
        $stmt = null;
        $conn = null;
    
        return $result;
    
    }

        #Retrieve all bids by section and course
        public function retrieveByCourseSection($course, $section){

            # SQL Statement
            $sql = 'SELECT * FROM bid WHERE code = :course and section = :section';
    
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

        //Retrieve all the distinct sections from bid table
        public function selectDistinct(){
                $sql = 'SELECT DISTINCT code, section FROM bid';
        
                $connMgr = new ConnectionManager();
                $conn = $connMgr->getConnection();
                
                $stmt = $conn->prepare($sql);
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $stmt->execute();
        
                $result = [];
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $result[] = [$row['code'], $row['section']];
                }
                return $result;
            }

        //Retrieve the number of bid objects of specified course code and section
        public function retrieveSectionCount($code, $section){

            # SQL Statement
            $sql = 'SELECT * FROM bid WHERE code = :code AND section = :section';
        
            # Connect
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();
        
            # Prepare & execute
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":code", $code, PDO::PARAM_STR);
            $stmt->bindParam(":section", $section, PDO::PARAM_STR);
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
}

?>