<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Delight\Auth\Auth;

function connection() {
    $host = 'db';
    $port = '5432';
    $dbName = 'giga_article';
    $username = 'root';
    $password = 'root';

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbName";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        return null;
    }
}

$pdo = Connection();

$auth = new Auth($pdo);
