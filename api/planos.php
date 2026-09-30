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
        $sql = 'SELECT id_plano, nome, velocidade, valor, descricao, ativo FROM planos';
        if ($perfil === 'CLIENTE') {
            $sql .= ' WHERE ativo = 1';
        }
        $sql .= ' ORDER BY valor ASC';
        json_out($pdo->query($sql)->fetchAll());
        break;

    case 'POST':
        $dados = json_input();

        if (($dados['acao'] ?? '') === 'contratar') {
            exigirPerfil(['CLIENTE'], $usuario);
            if (empty($dados['id_plano'])) {
                json_out(['erro' => 'Selecione um plano.'], 400);
            }
            $stmt = $pdo->prepare('SELECT id_plano FROM planos WHERE id_plano = :id AND ativo = 1');
            $stmt->execute(['id' => $dados['id_plano']]);
            if (!$stmt->fetch()) {
                json_out(['erro' => 'Plano indisponível.'], 400);
            }
            $upd = $pdo->prepare('UPDATE usuarios SET id_plano = :plano WHERE id = :id');
            $upd->execute(['plano' => $dados['id_plano'], 'id' => (int) $usuario->uid]);
            json_out(['mensagem' => 'Plano contratado/alterado com sucesso.']);
        }

        exigirPerfil(['GESTOR'], $usuario);
        if (empty($dados['nome']) || empty($dados['velocidade']) || !isset($dados['valor'])) {
            json_out(['erro' => 'Preencha nome, velocidade e valor.'], 400);
        }
        $stmt = $pdo->prepare(
            'INSERT INTO planos (nome, velocidade, valor, descricao, ativo)
             VALUES (:nome, :velocidade, :valor, :descricao, :ativo)'
        );
        $stmt->execute([
            'nome' => trim((string) $dados['nome']),
            'velocidade' => trim((string) $dados['velocidade']),
            'valor' => (float) $dados['valor'],
            'descricao' => $dados['descricao'] ?? '',
            'ativo' => isset($dados['ativo']) ? (int) $dados['ativo'] : 1,
        ]);
        json_out(['mensagem' => 'Plano criado com sucesso.'], 201);
        break;

    case 'PUT':
        exigirPerfil(['GESTOR'], $usuario);
        $dados = json_input();
        if (empty($dados['id_plano'])) {
            json_out(['erro' => 'Informe o plano.'], 400);
        }
        $stmt = $pdo->prepare(
            'UPDATE planos
             SET nome = :nome,
                 velocidade = :velocidade,
                 valor = :valor,
                 descricao = :descricao,
                 ativo = :ativo
             WHERE id_plano = :id'
        );
        $stmt->execute([
            'nome' => $dados['nome'] ?? '',
            'velocidade' => $dados['velocidade'] ?? '',
            'valor' => (float) ($dados['valor'] ?? 0),
            'descricao' => $dados['descricao'] ?? '',
            'ativo' => (int) ($dados['ativo'] ?? 1),
            'id' => (int) $dados['id_plano'],
        ]);
        json_out(['mensagem' => 'Plano atualizado.']);
        break;

    case 'DELETE':
        exigirPerfil(['GESTOR'], $usuario);
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            json_out(['erro' => 'Informe o plano.'], 400);
        }
        $pdo->prepare('UPDATE planos SET ativo = 0 WHERE id_plano = :id')->execute(['id' => $id]);
        json_out(['mensagem' => 'Plano desativado.']);
        break;

    default:
        json_out(['erro' => 'Método não suportado.'], 405);
}
