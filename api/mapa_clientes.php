<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$boot = api_boot(true);
$pdo = $boot['pdo'];
$usuario = $boot['usuario'];
exigirPerfil(['GESTOR', 'TECNICO'], $usuario);

try {
    $stmt = $pdo->query(
        "SELECT u.id, u.nome, u.latitude, u.longitude, u.cep, r.nome AS regiao
         FROM usuarios u
         LEFT JOIN regioes r ON r.id_regiao = u.id_regiao
         WHERE u.perfil = 'CLIENTE'
           AND u.latitude IS NOT NULL
           AND u.longitude IS NOT NULL"
    );
    json_out($stmt->fetchAll());
} catch (Throwable $e) {
    json_out(['erro' => 'Erro ao carregar clientes.'], 500);
}
