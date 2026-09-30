<?php

declare(strict_types=1);

require_once __DIR__ . '/paths.php';

$pageTitle = $pageTitle ?? 'ConectaSocial';
$activeNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — ConectaSocial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ((array) $extraCss as $cssHref): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($cssHref, ENT_QUOTES, 'UTF-8') ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>assets/css/style.css">
    <script>
        window.APP_BASE = <?= json_encode($BASE, JSON_UNESCAPED_SLASHES) ?>;
        (() => {
            const token = localStorage.getItem('token_acesso');
            if (!token) {
                window.location.replace(window.APP_BASE + 'login.html');
                return;
            }
            try {
                const payload = JSON.parse(atob(token.split('.')[1]));
                if (payload.exp && payload.exp * 1000 <= Date.now()) {
                    localStorage.removeItem('token_acesso');
                    localStorage.removeItem('perfil_usuario');
                    localStorage.removeItem('nome_usuario');
                    window.location.replace(window.APP_BASE + 'login.html');
                }
            } catch {
                localStorage.removeItem('token_acesso');
                localStorage.removeItem('perfil_usuario');
                localStorage.removeItem('nome_usuario');
                window.location.replace(window.APP_BASE + 'login.html');
            }
        })();
    </script>
</head>
<body>
    <div class="overlay" id="overlay"></div>
