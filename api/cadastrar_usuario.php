<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$boot = api_boot(false);
$pdo = $boot['pdo'];
$dados = json_input();

if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha']) || empty($dados['cep'])) {
    json_out(['erro' => 'Preencha nome, e-mail, senha e CEP.'], 400);
}

$perfilSolicitado = strtoupper((string) ($dados['perfil'] ?? 'CLIENTE'));
$token = null;

$auth = obterCabecalhoAutorizacao();
if ($auth) {
    try {
        $token = validarToken();
    } catch (Throwable $e) {
        $token = null;
    }
}

if ($perfilSolicitado === 'TECNICO') {
    if (!$token) {
        json_out(['erro' => 'Somente o provedor pode cadastrar técnicos.'], 403);
    }
    exigirPerfil(['GESTOR'], $token);
} else {
    $perfilSolicitado = 'CLIENTE';
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO usuarios (nome, email, senha_hash, perfil, latitude, longitude, cep, telefone, id_regiao)
         VALUES (:nome, :email, :senha, :perfil, :lat, :lng, :cep, :telefone, :regiao)'
    );
    $stmt->execute([
        'nome' => trim((string) $dados['nome']),
        'email' => trim((string) $dados['email']),
        'senha' => password_hash((string) $dados['senha'], PASSWORD_DEFAULT),
        'perfil' => $perfilSolicitado,
        'lat' => $dados['latitude'] ?? null,
        'lng' => $dados['longitude'] ?? null,
        'cep' => $dados['cep'],
        'telefone' => $dados['telefone'] ?? null,
        'regiao' => $dados['id_regiao'] ?? null,
    ]);
    json_out(['mensagem' => $perfilSolicitado === 'TECNICO' ? 'Técnico cadastrado.' : 'Cliente cadastrado com sucesso.'], 201);
} catch (PDOException $e) {
    json_out(['erro' => $e->getCode() == 23000 ? 'Este e-mail já está registado.' : 'Falha ao gravar na base de dados.'], 500);
}
