<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$boot = api_boot(true);
$pdo = $boot['pdo'];
$usuario = $boot['usuario'];
exigirPerfil(['GESTOR'], $usuario);
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($metodo) {
    case 'GET':
        $stmt = $pdo->query(
            "SELECT u.id, u.nome, u.email, u.telefone, u.cep, u.ativo, r.nome AS regiao
             FROM usuarios u
             LEFT JOIN regioes r ON u.id_regiao = r.id_regiao
             WHERE u.perfil = 'TECNICO'
             ORDER BY u.nome"
        );
        json_out($stmt->fetchAll());
        break;

    case 'POST':
        $dados = json_input();
        if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha'])) {
            json_out(['erro' => 'Nome, e-mail e senha são obrigatórios.'], 400);
        }
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nome, email, senha_hash, perfil, telefone, id_regiao, cep, latitude, longitude)
                 VALUES (:nome, :email, :senha, :perfil, :telefone, :regiao, :cep, :lat, :lng)'
            );
            $stmt->execute([
                'nome' => trim((string) $dados['nome']),
                'email' => trim((string) $dados['email']),
                'senha' => password_hash((string) $dados['senha'], PASSWORD_DEFAULT),
                'perfil' => 'TECNICO',
                'telefone' => $dados['telefone'] ?? null,
                'regiao' => $dados['id_regiao'] ?? null,
                'cep' => $dados['cep'] ?? null,
                'lat' => $dados['latitude'] ?? null,
                'lng' => $dados['longitude'] ?? null,
            ]);
            json_out(['mensagem' => 'Técnico cadastrado com sucesso.'], 201);
        } catch (PDOException $e) {
            json_out(['erro' => $e->getCode() == 23000 ? 'E-mail já cadastrado.' : 'Falha ao cadastrar técnico.'], 500);
        }
        break;

    case 'PUT':
        $dados = json_input();
        if (empty($dados['id'])) {
            json_out(['erro' => 'Informe o técnico.'], 400);
        }
        $sql = 'UPDATE usuarios SET nome = :nome, telefone = :telefone, id_regiao = :regiao, ativo = :ativo
                WHERE id = :id AND perfil = :perfil';
        $params = [
            'nome' => $dados['nome'] ?? '',
            'telefone' => $dados['telefone'] ?? null,
            'regiao' => $dados['id_regiao'] ?? null,
            'ativo' => (int) ($dados['ativo'] ?? 1),
            'id' => (int) $dados['id'],
            'perfil' => 'TECNICO',
        ];
        if (!empty($dados['senha'])) {
            $sql = 'UPDATE usuarios SET nome = :nome, telefone = :telefone, id_regiao = :regiao, ativo = :ativo,
                    senha_hash = :senha WHERE id = :id AND perfil = :perfil';
            $params['senha'] = password_hash((string) $dados['senha'], PASSWORD_DEFAULT);
        }
        $pdo->prepare($sql)->execute($params);
        json_out(['mensagem' => 'Técnico atualizado.']);
        break;

    default:
        json_out(['erro' => 'Método não suportado.'], 405);
}
