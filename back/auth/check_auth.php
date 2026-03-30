<?php
require __DIR__ . '/../db/Connection.php';

if (!$auth->isLoggedIn()) {
    $loginUrl = '/front/backoffice/auth/login.php';
    header('Location: ' . $loginUrl);
    exit;
}
