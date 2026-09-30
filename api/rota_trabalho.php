<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$boot = api_boot(true);
$pdo = $boot['pdo'];
$usuario = $boot['usuario'];
exigirPerfil(['TECNICO', 'GESTOR'], $usuario);

$stmt = $pdo->query(
    "SELECT c.id_chamados, c.descricao, c.status, c.data,
            u.nome AS cliente_nome, u.latitude, u.longitude, u.cep,
            r.nome AS regiao_nome, r.prioridade
     FROM chamados c
     LEFT JOIN usuarios u ON u.id = c.cliente
     LEFT JOIN regioes r ON r.id_regiao = c.id_local
     WHERE c.status IN ('Aberto', 'Em andamento')
     ORDER BY COALESCE(r.prioridade, 99) ASC, c.data ASC"
);

json_out($stmt->fetchAll());
