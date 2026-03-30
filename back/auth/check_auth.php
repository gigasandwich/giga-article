<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../db/connection.php';

if (!$auth->isLoggedIn()) {
    $loginUrl = '/front/backoffice/auth/login.php';
    header('Location: ' . $loginUrl);
    exit;
}
