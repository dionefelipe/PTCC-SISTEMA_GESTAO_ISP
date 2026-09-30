<?php
// /public/setup.php
require_once __DIR__ . '/../config/conexao_banco.php';

try {
    $pdo = ConexaoBanco::obterConexao();
    
    // 1. Perfis
    $pdo->exec("INSERT IGNORE INTO perfis_acesso (id_perfil, nome_perfil) VALUES (1, 'Provedor'), (2, 'Técnico'), (3, 'Cliente')");
    
    // 2. Provedor de Teste
    $pdo->exec("INSERT IGNORE INTO provedores (id_provedor, razao_social, cnpj, email_contato) VALUES (1, 'SocialConecta Provedor', '11.111.111/0001-11', 'contato@socialconecta.com')");
    
    // 3. Plano de Internet de Teste
    $pdo->exec("INSERT IGNORE INTO planos_internet (id_plano, id_provedor, nome_plano, velocidade_megas, valor_mensal, status_disponivel) VALUES (1, 1, 'Fibra 300MB', 300, 99.90, 1)");

    $senhaHash = password_hash("123456", PASSWORD_DEFAULT);
    
    // 4. Usuário Administrador (Provedor)
    $pdo->exec("INSERT IGNORE INTO usuarios_sistema (id_usuario, id_perfil, id_provedor, email_login, senha_hash, status_ativo) 
                VALUES (1, 1, 1, 'admin@provedor.com', '$senhaHash', 1)");

    // 5. Usuário Técnico
    $pdo->exec("INSERT IGNORE INTO usuarios_sistema (id_usuario, id_perfil, id_provedor, email_login, senha_hash, status_ativo) 
                VALUES (2, 2, 1, 'tecnico@provedor.com', '$senhaHash', 1)");
    $pdo->exec("INSERT IGNORE INTO tecnicos (id_tecnico, id_usuario, nome_completo, matricula_trabalhista, telefone_celular, status_disponibilidade) 
                VALUES (1, 2, 'Carlos Técnico', 'TEC-2026', '(11) 98888-8888', 'Disponível')");

    // 6. Usuário Cliente
    $pdo->exec("INSERT IGNORE INTO usuarios_sistema (id_usuario, id_perfil, id_provedor, email_login, senha_hash, status_ativo) 
                VALUES (3, 3, 1, 'cliente@provedor.com', '$senhaHash', 1)");
    $pdo->exec("INSERT IGNORE INTO clientes (id_cliente, id_usuario, id_plano_contratado, nome_completo, cpf, telefone_celular, bairro, endereco_completo) 
                VALUES (1, 3, 1, 'Ana Cliente', '123.456.789-00', '(11) 97777-7777', 'Centro', 'Rua Principal, 100')");

    // 7. Matriz de Pesos (Necessária para o algoritmo do Técnico funcionar)
    $pdo->exec("INSERT IGNORE INTO matriz_pesos_bairros (id_provedor, bairro_origem, bairro_destino, peso_deslocamento) 
                VALUES (1, 'Centro', 'Centro', 5), (1, 'Centro', 'Jardim América', 15), (1, 'Centro', 'Industrial', 25)");

    echo "<h1>Setup atualizado com sucesso!</h1>";
    echo "<p>Agora você pode testar com os seguintes logins (Senha para todos: <strong>123456</strong>):</p>";
    echo "<ul>";
    echo "<li><strong>Provedor:</strong> admin@provedor.com</li>";
    echo "<li><strong>Técnico:</strong> tecnico@provedor.com</li>";
    echo "<li><strong>Cliente:</strong> cliente@provedor.com</li>";
    echo "</ul>";
    echo "<br><a href='index.php'>Ir para a Tela de Login</a>";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>