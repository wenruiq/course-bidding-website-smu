<?php

class minimum_bid_valueDAO{

    # Retrieve All
    public function retrieveAll(){

        # SQL Statement
        $sql = 'SELECT * FROM minimum_bid_value';

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
            $result[] = new minimum_bid_value($row['amount'], $row['code'], $row['section']);
        }

        # Clear
        $stmt = null;
        $conn = null;

        return $result;

    }

        # Retrieve minimum bid for specific course code and section
        public function retrieveSpecificValue($code, $section){

            # SQL Statement
            $sql = 'SELECT * FROM minimum_bid_value WHERE code = :code and section = :section';

            # Connect
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();

            # Prepare & execute
            $stmt = $conn->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->bindParam(":code", $code, PDO::PARAM_STR);
            $stmt->bindParam(":section", $section, PDO::PARAM_STR);
            $stmt->execute();

            # Retrieve results in array of objects
            $result = array();
            while($row = $stmt->fetch()){
                $result = $row['amount'];
            }

            # Clear
            $stmt = null;
            $conn = null;

            return $result;

        }

        //Update minimum bid price for specified course code and section
        public function update($amount, $code, $section){

            # SQL Statement
            $sql = 'UPDATE minimum_bid_value
            SET amount = :amount, code = :code, section = :section
            WHERE code = :code and section = :section';

            # Connect
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();

            # Prepare & execute
            $stmt = $conn->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->bindParam(":code", $code, PDO::PARAM_STR);
            $stmt->bindParam(":section", $section, PDO::PARAM_STR);
            $stmt->bindParam(":amount", $amount, PDO::PARAM_STR);
            $stmt->execute();

            # Retrieve results in array of objects
            $isAddOk = False;
            if($stmt->execute()){
                $isAddOk = True;
            }

            # Clear
            $stmt = null;
            $conn = null;

            return $isAddOk;

        }

        //Add minimum bid price object to table
        public function add($mbvObj){

            #SQL Statement
            $sql = 'INSERT INTO minimum_bid_value (amount, code, section) VALUES (:amount, :code, :section)';
    
            # Connect
            $connMgr = new ConnectionManager();
            $conn = $connMgr->getConnection();
    
            # Prepare, bindParam & execute
            $stmt = $conn->prepare($sql);
    
            $stmt->bindParam(":amount", $mbvObj->amount, PDO::PARAM_STR);
            $stmt->bindParam(":code", $mbvObj->code, PDO::PARAM_STR);
            $stmt->bindParam(":section", $mbvObj->section, PDO::PARAM_STR);
    
            $isAddOk = False;
            if($stmt->execute()){
                $isAddOk = True;
            }
    
            # Clear
            $stmt = null;
            $conn = null;
    
            return $isAddOk;
    
        }

        //Remove all minimum bid price table information (truncate all)
        public function removeAll(){

            # SQL Statement
            $sql = 'TRUNCATE TABLE minimum_bid_value';
    
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