<?php

class adminDAO{

    # Retrieve All
    public function retrieveAll(){
        
        # SQL Statement
        $sql = 'SELECT * FROM admin';

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
            $result[] = new admin($row['userid'], $row['password']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    #Retrieve by userid
    public function retrieveByID($userid){
    
        #SQL Statement
        $sql = "SELECT userid, password FROM admin WHERE userid = :userid";
        
        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        # Prepare & execute    
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();

        # Retrieve result as a admin object
        $result = array();
        while($row = $stmt->fetch()) {
            $result = new admin($row['userid'], $row['password']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;
    }

    # Adds an admin object
    public function add($admin){

        #SQL Statement
        $sql = 'INSERT INTO admin (userid, password) VALUES (:userid, :password)';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":userid", $admin->userID, PDO::PARAM_STR);
        $stmt->bindParam(":password", $admin->password, PDO::PARAM_STR);

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
        $sql = 'TRUNCATE TABLE admin';

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