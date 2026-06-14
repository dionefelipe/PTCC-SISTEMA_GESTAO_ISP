<?php
// --- CONFIGURAÇÃO INICIAL E IMPORTAÇÕES ---
// Carrega a biblioteca para trabalhar com tokens JWT
use Firebase\JWT\JWT;

// Define que esta API vai sempre responder no formato JSON
header('Content-Type: application/json');

// Importa a conexão com o banco de dados e as dependências (via Composer)
require '../config.php';
require '../vendor/autoload.php';

// --- RECEBIMENTO DOS DADOS ---
// Lê o que foi enviado pelo front-end (no corpo da requisição)
$json = file_get_contents('php://input');
// Converte o JSON recebido em um array do PHP para podermos manipular
$dados = json_decode($json, true);

// --- VALIDAÇÃO DE ENTRADA ---
// Verifica se o usuário enviou email e senha. Se não, bloqueia e avisa
if (isset($dados['senha']) && isset($dados['email'])) {
    $senha = $dados['senha'];
    $email = $dados['email'];
} else {
    http_response_code(400); // Erro de solicitação inválida
    echo json_encode(["erro" => "Por favor, preencha email e senha."]);
    exit; // Para a execução aqui se faltar dados
}

// --- BUSCA DO USUÁRIO NO BANCO ---
// Prepara uma consulta para buscar o usuário pelo email
$res = $pdo->prepare("SELECT id, senha_hash, perfil FROM usuarios WHERE email = :x");
$res->bindValue(":x", $email);
$res->execute();

// Pega o resultado da busca (se existir)
$usuario = $res->fetch(PDO::FETCH_ASSOC);

// --- VERIFICAÇÃO DE SENHA E AUTENTICAÇÃO ---
// Verifica se o usuário foi encontrado E se a senha enviada bate com o hash no banco
if ($usuario && password_verify($senha, $usuario['senha_hash'])) {

    // --- CRIAÇÃO DO TOKEN (JWT) ---
    // Chave secreta usada para assinar o token (garante a autenticidade)
    $key = "essasenhatemquesermuitograndeparadarcerto";
    
    // Define o tempo atual para calcular a validade
    $tempo_atual = time();

    // Cria o "crachá" de acesso com as informações necessárias
    $payload = [
        "iat" => $tempo_atual,                  // Hora que o token foi criado
        "exp" => $tempo_atual + (60 * 60 * 8),  // Validade: 8 horas (60s * 60m * 8h)
        "uid" => $usuario['id'],                // Guarda o ID do usuário para identificar quem é
        "perfil" => $usuario['perfil']          // Guarda o cargo/nível de acesso (ex: admin ou cliente)
    ];

    // Transforma o array em uma string criptografada (o token JWT final)
    $jwt = \Firebase\JWT\JWT::encode($payload, $key, 'HS256');

    // --- RESPOSTA DE SUCESSO ---
    // Envia o token para o front-end, que vai salvá-lo para usar nas próximas requisições
    echo json_encode([
        "mensagem" => "Login efetuado com sucesso!",
        "token" => $jwt,
        "perfil" => $payload['perfil']
    ]);

} else {
    // --- RESPOSTA DE ERRO ---
    // Caso a senha esteja errada ou o usuário não exista
    http_response_code(401); // Erro de "Não Autorizado"
    echo json_encode(["erro" => "Tudo errado!"]);
    exit;
}
?>