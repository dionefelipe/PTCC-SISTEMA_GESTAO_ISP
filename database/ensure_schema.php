<?php

declare(strict_types=1);

function tabela_existe(PDO $pdo, string $tabela): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabela'
    );
    $stmt->execute(['tabela' => $tabela]);
    return (int) $stmt->fetchColumn() > 0;
}

function coluna_existe(PDO $pdo, string $tabela, string $coluna): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :tabela
           AND COLUMN_NAME = :coluna'
    );
    $stmt->execute(['tabela' => $tabela, 'coluna' => $coluna]);
    return (int) $stmt->fetchColumn() > 0;
}

function garantir_coluna(PDO $pdo, string $tabela, string $coluna, string $ddl): void
{
    if (!coluna_existe($pdo, $tabela, $coluna)) {
        $pdo->exec("ALTER TABLE {$tabela} ADD COLUMN {$ddl}");
    }
}

function ensure_schema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS regioes (
            id_regiao INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            prioridade INT NOT NULL DEFAULT 3,
            descricao VARCHAR(255) NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS planos (
            id_plano INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            velocidade VARCHAR(50) NOT NULL,
            valor DECIMAL(10,2) NOT NULL DEFAULT 0,
            descricao TEXT NULL,
            ativo TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            senha_hash VARCHAR(255) NOT NULL,
            perfil VARCHAR(50) NOT NULL,
            latitude VARCHAR(50) NULL,
            longitude VARCHAR(50) NULL,
            cep VARCHAR(20) NULL,
            telefone VARCHAR(20) NULL,
            id_plano INT NULL,
            id_regiao INT NULL,
            ativo TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    garantir_coluna($pdo, 'usuarios', 'telefone', 'telefone VARCHAR(20) NULL');
    garantir_coluna($pdo, 'usuarios', 'id_plano', 'id_plano INT NULL');
    garantir_coluna($pdo, 'usuarios', 'id_regiao', 'id_regiao INT NULL');
    garantir_coluna($pdo, 'usuarios', 'ativo', 'ativo TINYINT(1) NOT NULL DEFAULT 1');
    garantir_coluna($pdo, 'usuarios', 'latitude', 'latitude VARCHAR(50) NULL');
    garantir_coluna($pdo, 'usuarios', 'longitude', 'longitude VARCHAR(50) NULL');
    garantir_coluna($pdo, 'usuarios', 'cep', 'cep VARCHAR(20) NULL');

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS chamados (
            id_chamados INT AUTO_INCREMENT PRIMARY KEY,
            cliente INT NULL,
            descricao TEXT NOT NULL,
            status VARCHAR(50) DEFAULT 'Aberto',
            id_local INT NULL,
            solucao TEXT NULL,
            responsavel VARCHAR(100) NULL,
            id_tecnico INT NULL,
            data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    garantir_coluna($pdo, 'chamados', 'id_tecnico', 'id_tecnico INT NULL');
    garantir_coluna($pdo, 'chamados', 'solucao', 'solucao TEXT NULL');
    garantir_coluna($pdo, 'chamados', 'responsavel', 'responsavel VARCHAR(100) NULL');

    $regioesCount = (int) $pdo->query('SELECT COUNT(*) FROM regioes')->fetchColumn();
    if ($regioesCount === 0) {
        $pdo->exec(
            "INSERT INTO regioes (nome, prioridade, descricao) VALUES
            ('Centro', 1, 'Área central de alta densidade'),
            ('Zona Norte', 2, 'Atendimento comunitário zona norte'),
            ('Zona Sul', 3, 'Cobertura residencial zona sul'),
            ('Zona Leste', 4, 'Expansão de inclusão digital'),
            ('Zona Oeste', 5, 'Atendimento zona oeste')"
        );
    }

    $planosCount = (int) $pdo->query('SELECT COUNT(*) FROM planos')->fetchColumn();
    if ($planosCount === 0) {
        $pdo->exec(
            "INSERT INTO planos (nome, velocidade, valor, descricao, ativo) VALUES
            ('Essencial', '50 Mbps', 59.90, 'Plano de inclusão digital para uso básico.', 1),
            ('Família', '200 Mbps', 89.90, 'Ideal para estudo e streaming simultâneo.', 1),
            ('Comunidade+', '400 Mbps', 119.90, 'Alta velocidade para teletrabalho e aulas online.', 1)"
        );
    }

    $senha = password_hash('123456', PASSWORD_DEFAULT);
    $contas = [
        ['Gestor Admin', 'admin@conectasocial.com', 'GESTOR', null, 1, '-23.5505', '-46.6333', '01001000'],
        ['Técnico Silva', 'tecnico@conectasocial.com', 'TECNICO', null, 1, '-23.5400', '-46.6400', '01153000'],
        ['Cliente Souza', 'cliente@conectasocial.com', 'CLIENTE', 1, 1, '-23.5614', '-46.6560', '01310100'],
    ];

    $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
    $insert = $pdo->prepare(
        'INSERT INTO usuarios (nome, email, senha_hash, perfil, id_plano, id_regiao, latitude, longitude, cep)
         VALUES (:nome, :email, :senha, :perfil, :plano, :regiao, :lat, :lng, :cep)'
    );

    foreach ($contas as $conta) {
        [$nome, $email, $perfil, $plano, $regiao, $lat, $lng, $cep] = $conta;
        $check->execute(['email' => $email]);
        if (!$check->fetch()) {
            $insert->execute([
                'nome' => $nome,
                'email' => $email,
                'senha' => $senha,
                'perfil' => $perfil,
                'plano' => $plano,
                'regiao' => $regiao,
                'lat' => $lat,
                'lng' => $lng,
                'cep' => $cep,
            ]);
        }
    }

    $chamadosCount = (int) $pdo->query('SELECT COUNT(*) FROM chamados')->fetchColumn();
    if ($chamadosCount === 0) {
        $clienteId = (int) $pdo->query("SELECT id FROM usuarios WHERE email = 'cliente@conectasocial.com'")->fetchColumn();
        if ($clienteId) {
            $insChamado = $pdo->prepare(
                'INSERT INTO chamados (cliente, descricao, status, id_local) VALUES (:cliente, :descricao, :status, :local)'
            );
            $insChamado->execute([
                'cliente' => $clienteId,
                'descricao' => 'Sem conexão intermitente no período da manhã.',
                'status' => 'Aberto',
                'local' => 1,
            ]);
        }
    }
}
