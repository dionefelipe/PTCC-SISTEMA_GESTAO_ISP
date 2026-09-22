<?php
include_once '../config.php'; // Sua conexão PDO

header('Content-Type: application/json');

try {
    // Busca apenas usuários com coordenadas preenchidas (conforme o script SQL que atualizamos)
    $stmt = $pdo->query("SELECT nome, latitude, longitude, cep FROM usuarios WHERE latitude IS NOT NULL");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($clientes);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>