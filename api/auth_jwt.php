<?php

/*
|--------------------------------------------------------------------------
| ARQUIVO DE AUTENTICAÇÃO (A PORTARIA DO SISTEMA)
|--------------------------------------------------------------------------
| Este arquivo funciona como o segurança do nosso backend. Antes de qualquer
| arquivo (como o chamados_controller.php) devolver informações do banco de 
| dados, ele chama a função validarToken() daqui para ter certeza de que 
| o usuário tem permissão (está logado).
|--------------------------------------------------------------------------
*/

// --- 1. IMPORTAÇÕES DE BIBLIOTECAS ---
// O 'autoload' carrega automaticamente as ferramentas de terceiros que 
// instalamos no projeto (neste caso, a biblioteca que lida com o JWT).
require_once '../vendor/autoload.php';

// Avisa o PHP quais "ferramentas específicas" da biblioteca vamos usar aqui.
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// --- 2. A CHAVE MESTRA ---
// Esta é a mesma senha secreta usada lá no momento do login.
// O login usa essa chave para "trancar" (assinar) o token. 
// Nós usamos essa mesma chave aqui para "destrancar" e verificar se o token é legítimo.
define ('key', "essasenhatemquesermuitograndeparadarcerto");

// --- 3. A FUNÇÃO DE VALIDAÇÃO (O VERIFICADOR DE CRACHÁS) ---
function validarToken(){

    // Pega todos os cabeçalhos (headers) da requisição HTTP. 
    // Pense nisso como o envelope da carta que o front-end (ex: a tela da Laura) nos enviou.
    $headers = apache_request_headers();

    // VALIDAÇÃO 1: O usuário enviou o crachá?
    // O padrão da web é enviar o token dentro de uma variável chamada 'Authorization'.
    // Se essa variável não existir no envelope, bloqueamos na hora.
    if(!isset($headers['Authorization'])){
        // 401 significa "Não Autorizado"
        http_response_code(401);
        echo json_encode(["erro" => "Token de acesso nao fornecido"]);
        exit; // Para a execução do script inteiro aqui. Ninguém passa.
    }

    // LIMPEZA DO TOKEN
    // Por padrão mundial, o token costuma vir escrito assim: "Bearer eyJhbGciOiJIUz..."
    // A palavra 'Bearer' (Portador) é só um aviso. Nós só precisamos do código bagunçado.
    // O comando abaixo pega o texto e troca a palavra 'Bearer ' por nada (apagando ela).
    $token = str_replace('Bearer ', '', $headers['Authorization']);

    // TENTATIVA DE LEITURA (O TESTE DE FOGO)
    try {
        // O JWT::decode faz o trabalho pesado. Ele tenta ler o token usando nossa chave secreta.
        // Ele verifica automaticamente duas coisas muito importantes:
        // 1. A assinatura bate? (Alguém tentou falsificar o token?)
        // 2. A data de validade ('exp') já passou? (O token expirou?)
        $decoded = JWT::decode($token, new Key(key, 'HS256'));

        // Se passar por tudo sem dar erro, ele devolve as informações do usuário (o payload).
        // Quem chamou essa função (ex: o chamados_controller) vai receber o ID do usuário e o perfil.
        return $decoded;

    } catch( Exception $e ) {
        // SE DER ERRO NO TESTE DE FOGO:
        // O 'catch' é como um alarme. Se a assinatura for falsa ou o token estiver vencido, 
        // ele cai aqui automaticamente.
        
        http_response_code(401); // 401 Não Autorizado
        
        // Retornamos o motivo exato (ex: "Expired token") para facilitar caso a equipe 
        // de front-end precise entender por que a tela parou de carregar.
        echo json_encode([
            "erro" => "Token invalido ou expirado",
            "motivo" => $e->getMessage()
        ]);
        
        exit; // Bloqueia a execução.
    }

}

?>