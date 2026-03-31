<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../db/Connection.php';

$auth->logOut();

header('Location: /front/frontoffice/');
exit;