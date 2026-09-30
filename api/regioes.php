<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$boot = api_boot(true);
$pdo = $boot['pdo'];
$usuario = $boot['usuario'];
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($metodo) {
    case 'GET':
        $sql = "SELECT
                    r.id_regiao,
                    r.nome,
                    r.prioridade,
                    r.descricao,
                    COUNT(DISTINCT CASE WHEN u.perfil = 'CLIENTE' THEN u.id END) AS total_clientes,
                    COUNT(DISTINCT c.id_chamados) AS total_chamados,
                    SUM(CASE WHEN c.status = 'Aberto' THEN 1 ELSE 0 END) AS chamados_abertos
                FROM regioes r
                LEFT JOIN usuarios u ON u.id_regiao = r.id_regiao
                LEFT JOIN chamados c ON c.id_local = r.id_regiao
                GROUP BY r.id_regiao, r.nome, r.prioridade, r.descricao
                ORDER BY r.prioridade ASC";
        json_out($pdo->query($sql)->fetchAll());
        break;

    case 'POST':
        exigirPerfil(['GESTOR'], $usuario);
        $dados = json_input();
        if (empty($dados['nome'])) {
            json_out(['erro' => 'Informe o nome da área.'], 400);
        }
        $stmt = $pdo->prepare(
            'INSERT INTO regioes (nome, prioridade, descricao)
             VALUES (:nome, :prioridade, :descricao)'
        );
        $stmt->execute([
            'nome' => trim((string) $dados['nome']),
            'prioridade' => (int) ($dados['prioridade'] ?? 3),
            'descricao' => $dados['descricao'] ?? '',
        ]);
        json_out(['mensagem' => 'Área cadastrada.'], 201);
        break;

    default:
        json_out(['erro' => 'Método não suportado.'], 405);
}
