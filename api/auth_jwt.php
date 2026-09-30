<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validarToken(): object
{
    $cabecalho = obterCabecalhoAutorizacao();

    if (!$cabecalho || !preg_match('/Bearer\s+(.+)/i', $cabecalho, $matches)) {
        http_response_code(401);
        echo json_encode(['erro' => 'Token de acesso não fornecido.']);
        exit;
    }

    try {
        return JWT::decode($matches[1], new Key(JWT_SECRET, 'HS256'));
    } catch (Throwable $e) {
        http_response_code(401);
        echo json_encode(['erro' => 'Token inválido ou expirado.']);
        exit;
    }
}

function obterCabecalhoAutorizacao(): ?string
{
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $nome => $valor) {
            if (strtolower((string) $nome) === 'authorization') {
                return $valor;
            }
        }
    }

    return $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
}

function exigirPerfil(array $perfis, object $usuario): void
{
    $perfil = strtoupper((string) ($usuario->perfil ?? ''));
    $permitidos = array_map('strtoupper', $perfis);

    if (!in_array($perfil, $permitidos, true)) {
        http_response_code(403);
        echo json_encode(['erro' => 'Você não possui permissão para esta operação.']);
        exit;
    }
}

function perfil_usuario(object $usuario): string
{
    return strtoupper((string) ($usuario->perfil ?? ''));
}
