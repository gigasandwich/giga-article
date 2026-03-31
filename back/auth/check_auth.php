<?php
require __DIR__ . '/../db/Connection.php';

if (!$auth->isLoggedIn()) {
    $loginUrl = '/login';
    header('Location: ' . $loginUrl);
    exit;
}
