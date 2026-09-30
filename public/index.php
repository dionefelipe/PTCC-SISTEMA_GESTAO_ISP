<?php
// /public/index.php
require_once __DIR__ . '/../src/Autenticacao/ServicoLogin.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (ServicoLogin::autenticar($email, $senha)) {
        $tipoPerfil = $_SESSION['id_perfil'] ?? 0;
        if ($tipoPerfil == 1) {
            header('Location: dashboard_provedor.php');
        } elseif ($tipoPerfil == 2) {
            header('Location: dashboard_tecnico.php');
        } elseif ($tipoPerfil == 3) {
            header('Location: dashboard_cliente.php');
        }
        exit;
    } else {
        $erro = "E-mail ou palavra-passe incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SocialConecta ISP</title>
    <link rel="stylesheet" href="css/estilo_base.css">
    <style>
        body { background: #f1f5f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; font-family: sans-serif; }
        .card-login { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
        .form-control { margin-bottom: 15px; }
        .form-control label { display: block; font-size: 13px; font-weight: bold; color: #334155; margin-bottom: 5px; }
        .form-control input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        .botao-submit { background: #2563eb; color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 10px; }
        .botao-submit:hover { background: #1d4ed8; }
        .botao-registo { display: block; text-align: center; background: #e2e8f0; color: #334155; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 14px; text-decoration: none; margin-top: 10px; box-sizing: border-box; transition: 0.2s; }
        .botao-registo:hover { background: #cbd5e1; }
    </style>
</head>
<body>

<div class="card-login">
    <h2 style="margin-top: 0; color: #1e293b; text-align: center;">SocialConecta</h2>
    <p style="text-align: center; color: #64748b; font-size: 14px; margin-bottom: 20px;">Plataforma de Gestão ISP & Inclusão Digital</p>

    <?php if ($erro): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 13px; font-weight: bold;">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-control">
            <label>E-mail de Acesso</label>
            <input type="email" name="email" required placeholder="exemplo@email.com">
        </div>
        <div class="form-control">
            <label>Palavra-passe</label>
            <input type="password" name="senha" required placeholder="••••••••">
        </div>
        <button type="submit" class="botao-submit">Entrar no Sistema</button>
    </form>

    <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 15px; text-align: center;">
        <p style="font-size: 13px; color: #64748b; margin-bottom: 8px;">Ainda não tem conta?</p>
        <a href="cadastro.php" class="botao-registo">Criar Nova Conta / Registar</a>
    </div>
</div>

</body>
</html>