<?php

session_start();

//registration variables.
$username = "";
$phone_number = "";
$errors = array();


if (isset($_POST['login_user']))
    {
        $username = mysqli_real_escape_string($db, $_POST['username']);
        $password = mysqli_real_escape_string($db, $_POST['password']);

        if (empty($username)) {
            array_push($errors, "username is required");
        }
        if (empty($password)) {
            array_push($errors, "enter your password");
        }

        if (count($errors) == 0) {
            $password = md5($password);

            $query = "SELECT *FROM users WHERE username = '$username', password = '$password'";
            $results = mysqli_query($db, $query);
            if (mysqli_num_rows($results) == 1) {

                $_SESSION['username'] = $username;
                $_SESSION['success'] = "succesfully logged in!";
                header('location: index.php');
            } else {
                array_push($errors, "Please enter the correct username/password!");
            }
        } 
    }
?>