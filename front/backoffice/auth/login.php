<?php
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
        <div class="auth-container">
            <div class="auth-illustration">
                <img src="/public/assets/img/undraw_login_weas.svg" alt="Login Illustration">
            </div>
            <main class="auth-card" role="main">
                <h1 class="title">Connexion</h1>
                <p class="lead">Connectez-vous au back office</p>
                
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="error">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/back/auth/login.php">
                    <div class="form-row">
                        <label for="username">Nom d'utilisateur</label>
                        <input id="username" name="username" type="text" placeholder="Entrez votre nom d'utilisateur" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" required>
                    </div>
                    <div class="form-row">
                        <label for="password">Mot de passe</label>
                        <input id="password" name="password" type="password" placeholder="••••••••" value="<?= $password ?>" required>
                    </div>
                    <div class="auth-actions">
                        <button class="btn" type="submit">Se connecter</button>
                    </div>
                    <!-- <div class="auth-help">
                        <span>Besoin d'aide ? Contactez l'administrateur</span>
                    </div> -->
                </form>
            </main>
        </div>
    </div>
</body>