<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../db/Connection.php';

if ($auth->isLoggedIn()) {
    header('Location: /backoffice');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login');
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

$error = '';

try {
    $rememberDuration = (int) (60 * 3); // 3 minutes
    $auth->loginWithUsername($username, $password, $rememberDuration);
    header('Location: /backoffice');
    exit;
} catch (\Delight\Auth\UnknownUsernameException $e) {
    $error = "Nom d'utilisateur inexistant";
} catch (\Delight\Auth\InvalidPasswordException $e) {
    $error = 'Mauvais mot de passe';
}  catch (Exception $e) {
    $error = $e->getMessage();
}

$params = http_build_query(array_filter(['error' => $error, 'username' => $username]));
header('Location: /login' . ($params ? "?" . $params : ''));
exit;