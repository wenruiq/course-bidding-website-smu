<?php

class studentDAO{

    # Retrieve All
    public function retrieveAll(){
        
        # SQL Statement
        $sql = 'SELECT * FROM student';

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
            $result[] = new student($row['userid'], $row['password'], $row['name'], $row['school'], $row['edollar']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    #Retrieve by userid
    public function retrieveByID($userid){
    
        #SQL Statement
        $sql = "SELECT userid, password, name, school, edollar FROM student WHERE userid = :userid";
        
        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        # Prepare & execute    
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();

        # Retrieve result as a student object
        $result = array();
        while($row = $stmt->fetch()) {
            $result = new student($row['userid'], $row['password'], $row['name'], $row['school'], $row['edollar']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;
    }

    //Deduct student edollar amount as inputted
    public function deductEdollar($studentObj, $amount){
    
        #SQL Statement
        $sql = "UPDATE student SET edollar = :endAmount WHERE userid = :userid";
        
        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        # Prepare & execute
        $userid = $studentObj->getUserID();
        $currentAmount = $studentObj->getEdollar();
        $endAmount = strval($currentAmount - $amount);
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->bindParam(":endAmount", $endAmount, PDO::PARAM_STR);

        $isRemoveAllOk = False;
        if($stmt->execute()){
            $isRemoveAllOk = True;
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $isRemoveAllOk;
        
    }

    //Add student edollar amount as inputted
    public function addEdollar($studentObj, $amount){
    
        #SQL Statement
        $sql = "UPDATE student SET edollar = :endAmount WHERE userid = :userid";
        
        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        # Prepare & execute
        $userid = $studentObj->getUserID();
        $currentAmount = $studentObj->getEdollar();
        $endAmount = strval($currentAmount + $amount);
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->bindParam(":endAmount", $endAmount, PDO::PARAM_STR);
      
        $isRemoveAllOk = False;
        if($stmt->execute()){
            $isRemoveAllOk = True;
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $isRemoveAllOk;
        
    }

    //Add edollar function specific for bootstrap
    public function addEdollarBoot($userid, $original, $amount){
    
        #SQL Statement
        $sql = "UPDATE student SET edollar = :endAmount WHERE userid = :userid";
        
        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        # Prepare & execute
        $endAmount = $original + $amount;
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->bindParam(":endAmount", $endAmount, PDO::PARAM_STR);
      
        $isRemoveAllOk = False;
        if($stmt->execute()){
            $isRemoveAllOk = True;
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $isRemoveAllOk;
        
    }

    # Add
    public function add($student){

        #SQL Statement
        $sql = 'INSERT INTO student (userid, password, name, school, edollar) VALUES (:userid, :password, :name, :school, :edollar)';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":userid", $student->userID, PDO::PARAM_STR);
        $stmt->bindParam(":password", $student->password, PDO::PARAM_STR);
        $stmt->bindParam(":name", $student->name, PDO::PARAM_STR);
        $stmt->bindParam(":school", $student->school, PDO::PARAM_STR);
        $stmt->bindParam(":edollar", $student->edollar, PDO::PARAM_STR);
        
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
        $sql = 'TRUNCATE TABLE student';

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