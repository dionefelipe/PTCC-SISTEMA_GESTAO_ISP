<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$boot = api_boot(true);
$pdo = $boot['pdo'];
$usuario = $boot['usuario'];
exigirPerfil(['GESTOR'], $usuario);

$stmt = $pdo->query(
    "SELECT u.id, u.nome, u.email, u.cep, u.telefone, u.latitude, u.longitude,
            p.nome AS plano, r.nome AS regiao
     FROM usuarios u
     LEFT JOIN planos p ON p.id_plano = u.id_plano
     LEFT JOIN regioes r ON r.id_regiao = u.id_regiao
     WHERE u.perfil = 'CLIENTE'
     ORDER BY u.nome"
);
json_out($stmt->fetchAll());
