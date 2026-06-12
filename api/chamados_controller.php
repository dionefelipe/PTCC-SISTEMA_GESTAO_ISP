<?php
// 1. O CABEÇALHO E A SEGURANÇA 
header("Content-type: application/json");
require '../config.php'; // Ajuste o caminho se necessário para o arquivo principal de conexão
require 'auth_jwt.php'; // Ajuste o caminho para o arquivo de autenticação 

$token_decodificado = validarToken(); // A portaria 

// 2. Descobrindo o que o usuário quer fazer
$metodo = $_SERVER['REQUEST_METHOD'];

// 3. AS ROTAS
switch ($metodo) {

    // ROTA GET: LISTAR CHAMADOS
    case 'GET':

        try {

            $sql = "SELECT * FROM chamados ORDER BY data DESC";
            $stmt = $pdo->query($sql);

            $chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode($chamados);
        } catch (Exception $e) {

            http_response_code(500);
            echo json_encode(["erro" => "Falha ao buscar chamados"]);
        }

        break;


    // ROTA POST: ABRIR UM NOVO CHAMADO
    case 'POST':

        // Ler o JSON 
        $json = file_get_contents("php://input");
        $dados = json_decode($json, true);

        if (!isset($dados["descricao"]) || !isset($dados["local"])) { // TORNA OBRIGATORIO UM DESCRIÇAO E LOCAL PARA CRIAR UM CHAMADO

            http_response_code(400);
            echo json_encode(["Erro" => " Por Favor, descreva o problema"]);
            exit;
        }

        $id_cliente = $token_decodificado->uid;
        $descricao = $dados["descricao"];
        $status = "Aberto";
        $local = $dados["local"];

        try {

            $sql = "INSERT INTO chamados (cliente, descricao, status,local) VALUES (:id_cliente, :descricao, :status, :local)";
            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(":id_cliente", $id_cliente);
            $stmt->bindValue(":descricao", $descricao);
            $stmt->bindValue(":status", $status);
            $stmt->bindValue(":local", $local);

            $stmt->execute();

            http_response_code(201);
            echo json_encode(["mensagem" => "Chamado Aberto com sucesso!"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["erro" => "Falha ao salvar no banco"]);
            exit;
        }
        break;


    // ROTA PUT: ATUALIZAR UM CHAMADO EXISTENTE
    case 'PUT':

        // Ler o JSON 
        $json = file_get_contents("php://input");
        $dados = json_decode($json, true);

        // 1. Validação de dados obrigatórios
        if (!isset($dados["id_chamado"]) || !isset($dados["novo_status"]) || !isset($dados["responsavel"])) {
            http_response_code(400);
            echo json_encode(["erro" => "Por favor, preencha todos os campos."]);
            exit;
        }

        // 2. Regra de negócio: Solução obrigatória para status Resolvido
        if ($dados["novo_status"] == "Resolvido" && empty($dados["solucao"])) {
            http_response_code(400);
            echo json_encode(["erro" => "Por favor, preencha a solucao do problema."]);
            exit;
        }

        // PARA ATUALIZAR UM CHAMADO, DEVERA TER OS SEGUINTES REQUISITOS PREENCHIDOS
        $solucao = isset($dados["solucao"]) ? $dados["solucao"] : null;
        $id_chamado = $dados["id_chamado"];
        $status = $dados["novo_status"];
        $responsavel = $dados["responsavel"];

        try {

            $sql = "UPDATE chamados SET status = :novo_status, solucao = :solucao, responsavel = :responsavel  WHERE id_chamados = :id_chamado";
            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(":id_chamado", $id_chamado);
            $stmt->bindValue(":solucao", $solucao);
            $stmt->bindValue(":novo_status", $status);
            $stmt->bindValue(":responsavel", $responsavel);

            $stmt->execute();

            // Retorno de sucesso (200 OK)
            http_response_code(200);
            echo json_encode(["mensagem" => "Atualizacao feita com sucesso!"]);
        } catch (Exception $e) {
            // Retorno de erro no servidor (500)
            http_response_code(500);
            echo json_encode(["erro" => "Falha na atualizacao", "motivo" => $e->getMessage()]);
            exit;
        }


        break;


    // ROTA PADRÃO (Se o usuário tentar usar outro método como DELETE, bloqueamos)
    default:
        http_response_code(405); // Código 405 significa "Método Não Permitido"
        echo json_encode(["erro" => "Metodo HTTP nao suportado para esta rota."]);
        break;
}
