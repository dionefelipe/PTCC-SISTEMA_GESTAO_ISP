<?php
require_once __DIR__ . '/../includes/paths.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de cliente — ConectaSocial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>assets/css/login.css">
    <script>window.APP_BASE = <?= json_encode($BASE, JSON_UNESCAPED_SLASHES) ?>;</script>
</head>
<body class="login-body">
    <div class="login-container" style="max-width:520px">
        <div class="login-header">
            <div class="logo">
                <i class="fa-solid fa-wifi"></i>
                <span>ConectaSocial</span>
            </div>
            <p>Cadastro de cliente para consultar plano e abrir chamados</p>
        </div>
        <form id="formCadastroPublico" class="login-form">
            <div class="form-group">
                <label for="nome">Nome completo</label>
                <input id="nome" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input id="email" type="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input id="senha" type="password" required>
            </div>
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input id="telefone">
            </div>
            <div class="form-group">
                <label for="cep">CEP de instalação</label>
                <input id="cep" maxlength="8" placeholder="00000000" required>
                <p id="mensagem-cep" class="hint"></p>
            </div>
            <input type="hidden" id="latitude">
            <input type="hidden" id="longitude">
            <button type="submit" class="btn-submit">Criar conta</button>
        </form>
        <div class="login-footer">
            <p>Já possui acesso? <a href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>login.html">Fazer login</a></p>
        </div>
    </div>
    <script src="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>assets/js/script.js"></script>
</body>
</html>
