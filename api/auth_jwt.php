<?php

//importa  autoload do composer para carregar a biblioteca JWT

require_once '../vendor/autoload.php';

//classes necessarios da biblioteca JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//define a chave para assinar e verificar o JWT
define ('key', "essasenhatemquesermuitograndeparadarcerto");

function validarToken(){

// apache_request_headers() obtem todos os cabeçalhos da requisiçao do http feita no FrontEnd
// É no cabeçalho 'Autorizaçao' que deve ser enviado o token pelo Front
$headers = apache_request_headers();

if(!isset($headers['Authorization'])){

// verifica se foi enviado pelo frontEnd, caso nao, o acesso nao é autorizado
http_response_code(401);
echo json_encode(["erro" => "Token de acesso nao fornecido"]);
exit;

}

// o JWT geralmente infoema o token com a palvra Bearer na frente
// Aqui se separa e atribui apenas os numeros a variavel
$token = str_replace('Bearer ', '', $headers['Authorization']);


try{

    // tenta decodificar o token segundo a biblioteca JWT e verfica:
        //1. se ele é valido
        //2. se ele ja expirou
    $decoded = JWT::decode($token, new Key(key, 'HS256'));

    return $decoded;

} catch( Exception $e) {

        http_response_code(401);
        echo json_encode(["erro"=>"Token invalido ou expirado","motivo"=>$e->getMessage()]);
        exit;
}


}

?>