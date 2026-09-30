<?php

declare(strict_types=1);

function aplicar_cors(): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=UTF-8');

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

function json_input(): array
{
    $dados = json_decode((string) file_get_contents('php://input'), true);
    return is_array($dados) ? $dados : [];
}

function json_out($dados, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

function api_boot(bool $autenticar = true): array
{
    aplicar_cors();
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/auth_jwt.php';

    /** @var PDO $pdo */
    return [
        'pdo' => $pdo,
        'usuario' => $autenticar ? validarToken() : null,
    ];
}
