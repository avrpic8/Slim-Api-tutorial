<?php


class DbOperations
{

    private $connection;

    function __construct()
    {
        include_once dirname(__FILE__) . "/DbConnect.php";

        $db = new DbConnect();
        $this->connection = $db->connect();
    }

    public function createUser($email, $pass, $name, $school, $apiKey)
    {

        if(!$this->isUserExist($email)) {

            $query = "INSERT INTO users (email, password, name, school, api_key) VALUES (?,?,?,?,?)";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("sssss",$email,$pass,$name,$school,$apiKey);

            $result = $stmt->execute();
            $stmt->close();

            if ($result) {
                return USER_CREATE;
            } else {
                return USER_NOT_CREATE;
            }
        }else
            return USER_EXIST;
    }

    public function userLogin($email, $pass)
    {
        if($this->isUserExist($email)){

            $hashed_pass = $this->getUserPasswordByEmail($email);
            if(password_verify($pass, $hashed_pass)){
                return USER_AUTHENTICATED;
            }else{
                return PASSWORD_NOT_MATCH;
            }
        }else{
            return USER_NOT_FOUND;
        }
    }

    public function getUserPasswordByEmail($email)
    {
        $query = "SELECT password FROM users WHERE email = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $stmt->bind_result( $password);
        $stmt->fetch();

        return $password;
    }
    
    public function getUserByEmail($email)
    {
        $query = "SELECT id, email, name, school FROM users WHERE email = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id, $email, $name, $school);
        $stmt->fetch();

        $user = array();
        $user['id'] = $id;
        $user['email'] = $email;
        $user['name'] = $name;
        $user['school'] = $school;

        return $user;
    }

    public function getAllUser()
    {
        $query = "SELECT id, email, name, school FROM users";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $stmt->bind_result($id, $email, $name, $school);

        $users = array();
        while ($stmt->fetch()) {
            $user = array();
            $user['id'] = $id;
            $user['email'] = $email;
            $user['name'] = $name;
            $user['school'] = $school;

            array_push($users,$user);
        }
        return $users;
    }

    public function updateUser($id, $email, $name, $school)
    {
        $query = "UPDATE users SET email = ?, name = ?, school = ? WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssss",$email, $name, $school, $id);

        if($stmt->execute())
            return true;
        else
            return false;
    }

    public function updatePassword($currentPass, $newPass, $email)
    {
        $hashed_pass = $this->getUserPasswordByEmail($email);
        if(password_verify($currentPass, $hashed_pass)){

            $hash_pass = password_hash($newPass, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = ? WHERE email = ?";
            $stmt  = $this->connection->prepare($query);
            $stmt->bind_param("ss", $hash_pass, $email);

            if($stmt->execute()){
                return PASSWORD_CHANGED;
            }else{
                return PASSWORD_NOT_CHANGED;
            }

        }else{
            return PASSWORD_NOT_MATCH;
        }
    }


    public function deleteUser($id)
    {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id);

        if($stmt->execute())
            return true;
        else
            return false;
    }
    private function isUserExist($email){

        $query = "SELECT id FROM users WHERE email = ?";

        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows >0){
            return true;
        }else
            return false;
    }
}