<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../db/connection.php';

$error = "";

try {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $auth->loginWithUsername($username, $password);
    $message = "Logged in!";
} catch (\Delight\Auth\InvalidEmailException $e) {
    $error = "Wrong email";
} catch (\Delight\Auth\InvalidPasswordException $e) {
    $error = "Wrong password";
} catch (\Delight\Auth\EmailNotVerifiedException $e) {
    $error = "Email not verified";
}

if ($auth->isLoggedIn()) {
    $location = "/front/backoffice/index.php";
    header("location: $location");
} else {
    $location = "/front/backoffice/auth/login.php";
    header("location: $location");
}