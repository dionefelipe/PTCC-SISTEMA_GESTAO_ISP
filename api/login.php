<?php

declare(strict_types=1);

use Firebase\JWT\JWT;

require_once __DIR__ . '/helpers.php';

$boot = api_boot(false);
$pdo = $boot['pdo'];

$dados = json_input();

if (!isset($dados['email'], $dados['senha'], $dados['tipoUsuario'])) {
    json_out(['erro' => 'Dados incompletos enviados ao servidor.'], 400);
}

$email = trim((string) $dados['email']);
$senha = (string) $dados['senha'];
$tipo = strtoupper(trim((string) $dados['tipoUsuario']));

try {
    $stmt = $pdo->prepare(
        'SELECT id, nome, email, senha_hash, perfil, ativo
         FROM usuarios
         WHERE email = :email'
    );
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        json_out(['erro' => 'E-mail não encontrado.'], 401);
    }

    if ((int) ($usuario['ativo'] ?? 1) !== 1) {
        json_out(['erro' => 'Esta conta está desativada.'], 403);
    }

    if (!password_verify($senha, $usuario['senha_hash'])) {
        json_out(['erro' => 'Senha incorreta.'], 401);
    }

    $perfilBanco = strtoupper(trim((string) $usuario['perfil']));
    if ($perfilBanco !== $tipo) {
        json_out([
            'erro' => 'O perfil selecionado não corresponde ao cadastro. Validação de permissões recusada.',
        ], 403);
    }

    $agora = time();
    $payload = [
        'iat' => $agora,
        'exp' => $agora + (60 * 60 * 8),
        'uid' => (int) $usuario['id'],
        'perfil' => $perfilBanco,
        'nome' => $usuario['nome'],
        'email' => $usuario['email'],
    ];

    $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');

    json_out([
        'mensagem' => 'Login efetuado com sucesso!',
        'token' => $jwt,
        'perfil' => $perfilBanco,
        'nome' => $usuario['nome'],
        'email' => $usuario['email'],
    ]);
} catch (Throwable $e) {
    json_out(['erro' => 'Erro no servidor de autenticação.'], 500);
}
