<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$boot = api_boot(true);
$pdo = $boot['pdo'];
$usuario = $boot['usuario'];

$stmt = $pdo->prepare(
    "SELECT u.id, u.nome, u.email, u.perfil, u.cep, u.telefone, u.latitude, u.longitude,
            u.id_plano, u.id_regiao, p.nome AS plano_nome, p.velocidade, p.valor, p.descricao AS plano_descricao,
            r.nome AS regiao_nome
     FROM usuarios u
     LEFT JOIN planos p ON p.id_plano = u.id_plano
     LEFT JOIN regioes r ON r.id_regiao = u.id_regiao
     WHERE u.id = :id"
);
$stmt->execute(['id' => (int) $usuario->uid]);
$dados = $stmt->fetch();

if (!$dados) {
    json_out(['erro' => 'Usuário não encontrado.'], 404);
}

json_out($dados);
