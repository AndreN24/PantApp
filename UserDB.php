<?php
class DatabaseCommunicator {
    function AddUserInDatabase($userid, $email, $name, $privileges, $rating){
        // collect value of input field


        $db = new SQLite3("DB/users.db");
        $checkIfUserExists = false;
        $sql = ("INSERT INTO users(name, email, userid, privileges, rating) VALUES(:name , :email, :userid, :privileges, :rating)");//prepare stmt

        $stmt = $db-> prepare ($sql); //bind params
        $stmt ->bindParam(':name', $name , SQLITE3_TEXT);
        $stmt ->bindParam(':email', $email , SQLITE3_TEXT);
        $stmt ->bindParam(':userid', $userid , SQLITE3_INT);
        $stmt ->bindParam(":privileges", $privileges, SQLITE3_TEXT);
        $stmt ->bindParam(":rating", $privileges, SQLITE3_FLOAT);



        $statement = $db->prepare('SELECT * FROM users WHERE userid = :userid'); //check if user already exists
        $statement->bindValue(':userid', $userid);

        $result = $statement->execute();


        while ($row = $result->fetchArray()) {  //confirm user exists
            if ($row['userid'] == $userid)
            {
                $checkIfUserExists = true;
                echo"<h1>User already exists!</h1>";
                    break;
            }
        }

        if (!$checkIfUserExists){ // create user
            if ($stmt->execute()){
                $db->close();
                echo "<h1>Successfully added user!</h1><br>";
            }
        }
    }
}
?>