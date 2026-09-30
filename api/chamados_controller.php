<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$boot = api_boot(true);
$pdo = $boot['pdo'];
$usuario = $boot['usuario'];
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$perfil = perfil_usuario($usuario);

switch ($metodo) {
    case 'GET':
        try {
            $sql = "SELECT
                        c.id_chamados,
                        c.descricao,
                        c.status,
                        c.solucao,
                        c.responsavel,
                        c.id_tecnico,
                        c.id_local,
                        c.cliente,
                        c.data,
                        r.nome AS regiao_nome,
                        r.prioridade,
                        u.nome AS cliente_nome,
                        u.latitude,
                        u.longitude,
                        t.nome AS tecnico_nome
                    FROM chamados c
                    LEFT JOIN regioes r ON c.id_local = r.id_regiao
                    LEFT JOIN usuarios u ON c.cliente = u.id
                    LEFT JOIN usuarios t ON c.id_tecnico = t.id
                    WHERE 1=1";
            $params = [];

            if ($perfil === 'CLIENTE') {
                $sql .= ' AND c.cliente = :uid';
                $params['uid'] = (int) $usuario->uid;
            }

            if (!empty($_GET['regiao'])) {
                $sql .= ' AND c.id_local = :regiao';
                $params['regiao'] = (int) $_GET['regiao'];
            }

            if (!empty($_GET['status'])) {
                $sql .= ' AND c.status = :status';
                $params['status'] = (string) $_GET['status'];
            }

            $sql .= ' ORDER BY COALESCE(r.prioridade, 99) ASC, c.data DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            json_out($stmt->fetchAll());
        } catch (Throwable $e) {
            json_out(['erro' => 'Falha ao listar chamados.'], 500);
        }
        break;

    case 'POST':
        exigirPerfil(['CLIENTE', 'GESTOR'], $usuario);
        $dados = json_input();

        if (empty($dados['descricao']) || empty($dados['id_local'])) {
            json_out(['erro' => 'Informe a descrição e a região do chamado.'], 400);
        }

        $stmt = $pdo->prepare('SELECT id_regiao FROM regioes WHERE id_regiao = :id');
        $stmt->execute(['id' => $dados['id_local']]);
        if (!$stmt->fetch()) {
            json_out(['erro' => 'Região inválida.'], 400);
        }

        $clienteId = $perfil === 'GESTOR' && !empty($dados['cliente'])
            ? (int) $dados['cliente']
            : (int) $usuario->uid;

        $stmt = $pdo->prepare(
            'INSERT INTO chamados (cliente, descricao, status, id_local)
             VALUES (:cliente, :descricao, :status, :local)'
        );
        $stmt->execute([
            'cliente' => $clienteId,
            'descricao' => trim((string) $dados['descricao']),
            'status' => 'Aberto',
            'local' => (int) $dados['id_local'],
        ]);

        json_out(['mensagem' => 'Chamado aberto com sucesso!'], 201);
        break;

    case 'PUT':
        exigirPerfil(['TECNICO', 'GESTOR'], $usuario);
        $dados = json_input();

        if (empty($dados['id_chamado']) || empty($dados['novo_status'])) {
            json_out(['erro' => 'Informe o chamado e o novo status.'], 400);
        }

        $status = (string) $dados['novo_status'];
        if (in_array($status, ['Resolvido', 'Finalizado'], true) && empty($dados['solucao'])) {
            json_out(['erro' => 'Para finalizar o chamado, descreva a solução.'], 400);
        }

        $stmt = $pdo->prepare('SELECT id_chamados FROM chamados WHERE id_chamados = :id');
        $stmt->execute(['id' => $dados['id_chamado']]);
        if (!$stmt->fetch()) {
            json_out(['erro' => 'Chamado inexistente.'], 400);
        }

        $responsavel = $dados['responsavel'] ?? ($usuario->nome ?? 'Técnico');
        $idTecnico = $perfil === 'TECNICO' ? (int) $usuario->uid : ($dados['id_tecnico'] ?? null);

        $stmt = $pdo->prepare(
            'UPDATE chamados
             SET status = :status,
                 solucao = :solucao,
                 responsavel = :responsavel,
                 id_tecnico = :tecnico
             WHERE id_chamados = :id'
        );
        $stmt->execute([
            'status' => $status,
            'solucao' => $dados['solucao'] ?? null,
            'responsavel' => $responsavel,
            'tecnico' => $idTecnico,
            'id' => (int) $dados['id_chamado'],
        ]);

        json_out(['mensagem' => 'Chamado atualizado com sucesso.']);
        break;

    default:
        json_out(['erro' => 'Método HTTP não suportado.'], 405);
}
