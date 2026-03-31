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
                        <div class="password-container">
                            <input id="password" name="password" type="password" placeholder="••••••••" value="<?= $password ?>" required>
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Afficher le mot de passe">
                                <svg id="eyeIcon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="auth-actions">
                        <button class="btn" type="submit">Se connecter</button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            if (type === 'text') {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.064-7 9.542-7 1.053 0 2.062.18 3 .512M12 9a3 3 0 00-3 3 3 3 0 003 3m0 0a3 3 0 002.466-1.293m0 0l-4.466-4.466m15.466 1.466a10.05 10.05 0 01-1.012 4.125M17.657 17.657L13.414 13.414m0 0L9.172 9.172M9.172 9.172L4.929 4.929"></path>
                `;
            } else {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        });
    });
    </script>
</body>