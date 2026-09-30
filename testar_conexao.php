<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT id, email, perfil, senha_hash FROM usuarios WHERE email = 'admin@conectasocial.com'");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h2>Estado da Base de Dados:</h2>";
    if ($admin) {
        echo "<p style='color: green;'><b>Utilizador encontrado com sucesso!</b></p>";
        echo "<pre>";
        print_r($admin);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'><b>ERRO: O utilizador 'admin@conectasocial.com' NÃO EXISTE na base de dados 'SocialConectaBD'.</b></p>";
        
        // Insere automaticamente para corrigir agora mesmo
        $senha_hash = password_hash('password', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES ('Gestor Admin', 'admin@conectasocial.com', :senha, 'GESTOR')");
        $insert->execute(['senha' => $senha_hash]);
        echo "<p style='color: blue;'><b>Correção aplicada!</b> O utilizador admin foi inserido agora com a senha <i>password</i>. Tente fazer login novamente.</p>";
    }
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Erro ao consultar o banco: " . $e->getMessage() . "</h3>";
}
?>