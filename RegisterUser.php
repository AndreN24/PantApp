<?php
include "UserDB.php";

echo $_GET["name"] . "<br>";
echo $_GET["id"] . "<br>";
echo $_GET["email"];

$db = new SQLite3("DB/users.db");
//$db->exec("CREATE TABLE users(id INTEGER PRIMARY KEY, name TEXT, email TEXT, userid INT,privileges TEXT,rating FLOAT)");
$db->exec("INSERT INTO users(name, email, userid, privileges, rating) VALUES('Test Person', 'Test@test.se', '123', 'admin', 3.5)");


$dbdata = new DatabaseCommunicator();

$dbdata-> AddUserInDatabase($_GET['id'], $_GET['email'], $_GET['name'], "user", 0){



session_start();
//Set the current session email, name and ID of user 
$_SESSION['name'] = $_GET['name'];
$_SESSION['email'] = $_GET['email'];
$_SESSION['id'] = $_GET['id'];



?>