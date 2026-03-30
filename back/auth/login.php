<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../db/Connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /front/backoffice/auth/login.php');
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

$error = '';

try {
    $auth->loginWithUsername($username, $password);
    header('Location: /front/backoffice/index.php');
    exit;
} catch (\Delight\Auth\UnknownUsernameException $e) {
    $error = "Nom d'utilisateur inexistant";
} catch (\Delight\Auth\InvalidPasswordException $e) {
    $error = 'Mauvais mot de passe';
}  catch (Exception $e) {
    $error = $e->getMessage();
}

$params = http_build_query(array_filter(['error' => $error, 'username' => $username]));
header('Location: /front/backoffice/auth/login.php' . ($params ? "?" . $params : ''));
exit;