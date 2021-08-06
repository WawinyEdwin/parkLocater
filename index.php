<?php

session_start();

if (isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You Must Log In!";
    header('location: login.php');
}
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION ['username']);
    header("location: login.php")
}

//notification messages
if (isset($_GET['success']));

//
?>