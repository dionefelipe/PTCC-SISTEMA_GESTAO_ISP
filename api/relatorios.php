<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$boot = api_boot(true);
$pdo = $boot['pdo'];
$usuario = $boot['usuario'];
exigirPerfil(['GESTOR'], $usuario);

$tipo = $_GET['tipo'] ?? 'chamados';
$formato = $_GET['formato'] ?? 'json';

if ($tipo === 'chamados') {
    $sql = "SELECT c.id_chamados, u.nome AS cliente, c.descricao, c.status, r.nome AS regiao,
                   r.prioridade, c.responsavel, c.solucao, c.data
            FROM chamados c
            LEFT JOIN usuarios u ON u.id = c.cliente
            LEFT JOIN regioes r ON r.id_regiao = c.id_local
            WHERE 1=1";
    $params = [];
    if (!empty($_GET['regiao'])) {
        $sql .= ' AND c.id_local = :regiao';
        $params['regiao'] = (int) $_GET['regiao'];
    }
    if (!empty($_GET['status'])) {
        $sql .= ' AND c.status = :status';
        $params['status'] = (string) $_GET['status'];
    }
    $sql .= ' ORDER BY c.data DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $linhas = $stmt->fetchAll();
    $cabecalho = ['id', 'cliente', 'descricao', 'status', 'regiao', 'prioridade', 'responsavel', 'solucao', 'data'];
} else {
    $linhas = $pdo->query(
        "SELECT u.nome, u.email, u.cep, p.nome AS plano, r.nome AS regiao
         FROM usuarios u
         LEFT JOIN planos p ON p.id_plano = u.id_plano
         LEFT JOIN regioes r ON r.id_regiao = u.id_regiao
         WHERE u.perfil = 'CLIENTE'
         ORDER BY u.nome"
    )->fetchAll();
    $cabecalho = ['nome', 'email', 'cep', 'plano', 'regiao'];
}

if ($formato === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio-' . $tipo . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, $cabecalho, ';');
    foreach ($linhas as $linha) {
        fputcsv($out, array_values($linha), ';');
    }
    fclose($out);
    exit;
}

json_out(['dados' => $linhas, 'total' => count($linhas)]);
