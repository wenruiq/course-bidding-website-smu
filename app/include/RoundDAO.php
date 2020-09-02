<?php

class roundDAO{

    # Retrieve All
    public function retrieveAll(){
        
        # SQL Statement
        $sql = 'SELECT * FROM round';

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
            $result[] = new round($row['roundnum'], $row['status']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

    #Retrieve by roundnum
    public function retrieveByRound($roundnum){

        # SQL Statement
        $sql = "SELECT status FROM round WHERE roundnum = :roundnum";

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':roundnum', $roundnum, PDO::PARAM_STR);
        $stmt->execute();

        # Retrieve status as a string
        $result = '';
        while($row = $stmt->fetch()) {
            $result = $row['status'];
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;
    }
    

    # Update
    public function update($roundnum, $status){

        #SQL Statement
        $sql = 'UPDATE round SET status = :status WHERE roundnum = :roundnum';

        # Connect
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        # Prepare, bindParam & execute
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":status", $status, PDO::PARAM_STR);
        $stmt->bindParam(":roundnum", $roundnum, PDO::PARAM_STR);
        
        $isUpdateOk = False;
        if($stmt->execute()){
            $isUpdateOk = True;
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $isUpdateOk;
    }

}

?>