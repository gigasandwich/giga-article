<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../db/connection.php';

use Delight\Auth\Auth;

try {
    $auth = new Auth($pdo);

    $email = "admin@gmail.com";
    $password = "qaws1209edrf34tgyhuj";
    $username = "admin";

    if (!doesUserExist($pdo, $email)) {
        $success = $auth->register($email, $password, $username);
        if ($success) {
            echo "Created user: $email with password: $password";
        } else {
            echo "Failed to create user: $email";
        }
    }
} catch (Exception $e) {
    echo ''. $e->getMessage() .'';
}

function doesUserExist($pdo, $email) {
    $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    return $stmt->fetchColumn() > 0;
}