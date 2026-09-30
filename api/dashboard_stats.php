<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$boot = api_boot(true);
$pdo = $boot['pdo'];
$usuario = $boot['usuario'];
exigirPerfil(['GESTOR', 'TECNICO', 'CLIENTE'], $usuario);

$perfil = perfil_usuario($usuario);
$uid = (int) $usuario->uid;

$filtros = '';
$params = [];
if (!empty($_GET['regiao'])) {
    $filtros .= ' AND c.id_local = :regiao';
    $params['regiao'] = (int) $_GET['regiao'];
}
if (!empty($_GET['status'])) {
    $filtros .= ' AND c.status = :status';
    $params['status'] = (string) $_GET['status'];
}

$totais = [
    'clientes' => 0,
    'tecnicos' => 0,
    'planos' => 0,
    'chamados_abertos' => 0,
    'chamados_andamento' => 0,
    'chamados_finalizados' => 0,
    'areas' => 0,
    'meu_plano' => null,
    'meus_chamados' => 0,
];

if ($perfil === 'GESTOR') {
    $totais['clientes'] = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE perfil = 'CLIENTE'")->fetchColumn();
    $totais['tecnicos'] = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE perfil = 'TECNICO' AND ativo = 1")->fetchColumn();
    $totais['planos'] = (int) $pdo->query('SELECT COUNT(*) FROM planos WHERE ativo = 1')->fetchColumn();
    $totais['areas'] = (int) $pdo->query('SELECT COUNT(*) FROM regioes')->fetchColumn();

    $stmt = $pdo->prepare("SELECT status, COUNT(*) AS total FROM chamados c WHERE 1=1 {$filtros} GROUP BY status");
    $stmt->execute($params);
    foreach ($stmt->fetchAll() as $linha) {
        $status = strtolower((string) $linha['status']);
        if ($status === 'aberto') {
            $totais['chamados_abertos'] = (int) $linha['total'];
        } elseif (stripos($status, 'andamento') !== false) {
            $totais['chamados_andamento'] = (int) $linha['total'];
        } else {
            $totais['chamados_finalizados'] += (int) $linha['total'];
        }
    }

    $recentes = $pdo->prepare(
        "SELECT c.id_chamados, c.descricao, c.status, c.data, u.nome AS cliente_nome, r.nome AS regiao_nome
         FROM chamados c
         LEFT JOIN usuarios u ON u.id = c.cliente
         LEFT JOIN regioes r ON r.id_regiao = c.id_local
         WHERE 1=1 {$filtros}
         ORDER BY c.data DESC
         LIMIT 8"
    );
    $recentes->execute($params);
    $totais['recentes'] = $recentes->fetchAll();
} elseif ($perfil === 'TECNICO') {
    $totais['chamados_abertos'] = (int) $pdo->query("SELECT COUNT(*) FROM chamados WHERE status = 'Aberto'")->fetchColumn();
    $totais['chamados_andamento'] = (int) $pdo->query("SELECT COUNT(*) FROM chamados WHERE status = 'Em andamento'")->fetchColumn();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM chamados WHERE id_tecnico = :id AND status IN ('Finalizado','Resolvido')");
    $stmt->execute(['id' => $uid]);
    $totais['chamados_finalizados'] = (int) $stmt->fetchColumn();
    $totais['recentes'] = $pdo->query(
        "SELECT c.id_chamados, c.descricao, c.status, c.data, u.nome AS cliente_nome, r.nome AS regiao_nome
         FROM chamados c
         LEFT JOIN usuarios u ON u.id = c.cliente
         LEFT JOIN regioes r ON r.id_regiao = c.id_local
         WHERE c.status IN ('Aberto','Em andamento')
         ORDER BY COALESCE(r.prioridade, 99), c.data
         LIMIT 8"
    )->fetchAll();
} else {
    $stmt = $pdo->prepare(
        'SELECT p.nome, p.velocidade, p.valor, p.descricao
         FROM usuarios u
         LEFT JOIN planos p ON p.id_plano = u.id_plano
         WHERE u.id = :id'
    );
    $stmt->execute(['id' => $uid]);
    $totais['meu_plano'] = $stmt->fetch() ?: null;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM chamados WHERE cliente = :id AND status = :status');
    $stmt->execute(['id' => $uid, 'status' => 'Aberto']);
    $totais['meus_chamados'] = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare(
        'SELECT c.id_chamados, c.descricao, c.status, c.data, r.nome AS regiao_nome
         FROM chamados c
         LEFT JOIN regioes r ON r.id_regiao = c.id_local
         WHERE c.cliente = :id
         ORDER BY c.data DESC
         LIMIT 8'
    );
    $stmt->execute(['id' => $uid]);
    $totais['recentes'] = $stmt->fetchAll();
}

json_out($totais);
