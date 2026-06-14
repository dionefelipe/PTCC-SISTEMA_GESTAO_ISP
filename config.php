<?php

require_once 'vendor/autoload.php';

$host = 'localhost';
$dbname = 'testetcc';
$user = 'root'; 
$pass = '0000'; 
try {
    // Abre a conexão com suporte a acentos (utf8)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    
    // Configura o PDO para avisar na tela se houver algum erro de sintaxe ou banco
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Se a conexão falhar, interrompe e mostra o erro em formato JSON
    die(json_encode(["erro" => "Falha na conexão com o banco: " . $e->getMessage()]));
}
?>
