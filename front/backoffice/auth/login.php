<?php
require_once __DIR__ . '/../../../back/util/minify.php';
    $username = "admin";
    $password = "qaws1209edrf34tgyhuj";
    $error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
        <link rel="stylesheet" href="/public/assets/styles/style.css">
        <link rel="stylesheet" href="/public/assets/styles/auth.css">
</head>
<body>
    <?php include "../../component/header.php"; ?>
    <div class="auth-wrapper">
        <main class="auth-card" role="main">
            <p class="lead">Connectez vous au back office</p>
        <?php if (isset($error) && !empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="/back/auth/login.php">
            <div class="form-row">
                <label for="username">Nom d'utilisateur</label>
                <input id="username" name="username" type="text" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username">
            </div>
            <div class="form-row">
                <label for="password">Mot de passe</label>
                <input id="password" name="password" value="qaws1209edrf34tgyhuj" type="password">
            </div>
            <div class="auth-actions">
                <div class="auth-help">&nbsp;</div>
                <button class="btn" type="submit">Se connecter</button>
            </div>
        </form>
    </main>
</div>
</html>
</html>