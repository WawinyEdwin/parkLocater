<?php

//start session to store users logged session
session_start();

//registration variables.
$username = "";
$phone_number = "";
$errors = array();

//connection to the database
$db = mysqli_connect('localhost', 'root', 'registration');

//register users
if (isset($_POST['reg_user'])) {

    //recieving input
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $phone_number = mysqli_real_escape_string($db, $_POST['phone_number']);
    $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
    $password_2 = mysqli_real_escape_string($db, $POST['password_2']);
    
    //form validation
    if (empty($username)) {
        array_push($errors, "username is required");
    }
    if (empty($phone_number)) {
        array_push($errors, "phone number required");
    }
    if (empty($password_1)) {
        array_push($errors, "password is required");
    }
    if (empty($password_2)) {
        array_push($errors,"password is required");
    }
    if (password_1 != password_2){
        array_push($errors, "the passwords do not match")
    }

    //we check the database to make sure the user doesn't exist
    $user_check_query = "SELECT *FROM users WHERE 
    username = '$username' OR phone_number ='$phone_number' LIMIT 1";
    $result = mysqli_query($db, $user_check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        //if user exists.
        if ($user['username'] === $username) {
            array_push($errors, "username already exists!")
        }
        if ($phone_number['phone_number'] === $phone_number) {
            array_push($errors, "phone number alreeady used!")
        }
    }

    //register users if there are no errors
    if (count($errors) == 0){
     //password encryption before saving   
        $password = md5($password_1);

        $query = "INSERT INTO users(username, phone_number, password) VALUES ('$username', '$phone_number', '$password')";
        mysqli_query($db, $query);

        $_SESSION['username'] = $username;
        $_SESSION['success'] = "success registered!";

        header('location: index.php')
    }
}
//log in users
?>