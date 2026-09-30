<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/database/ensure_schema.php';

$host = 'localhost';
$dbname = 'socialconectabd';
$user = 'root';
$pass = '';

if (!defined('JWT_SECRET')) {
    define('JWT_SECRET', 'essasenhatemquesermuitograndeparadarcerto');
}

try {
    $pdoRoot = new PDO(
        "mysql:host={$host};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    ensure_schema($pdo);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'erro' => 'Não foi possível conectar ao banco de dados. Confirme se o MySQL está ativo e se a base SocialConectaBD existe.',
    ]);
    exit;
}
