<?php

/*
|--------------------------------------------------------------------------
| DOCUMENTAÇÃO DE INTEGRAÇÃO DA API (CHAMADOS)
|--------------------------------------------------------------------------
| Este bloco define o contrato de dados entre este Controller e o Front-end.
| Qualquer alteração nestes campos deve ser comunicada à equipe.
|
| 1. MÉTODOS GET (LISTAGEM):
| - Parâmetro URL: 'regiao' (Ex: listar_chamados.php?regiao=1)
| - Retorno esperado: JSON com array de objetos:
|   ['id_chamados', 'descricao', 'status', 'nome' (região), 'prioridade']
|
| 2. MÉTODO POST (CRIAR CHAMADO):
| - Payload esperado (JSON):
|   {
|     "descricao": "string",
|     "id_local": int 
|   }
|
| 3. MÉTODO PUT (ATUALIZAR):
| - Payload esperado (JSON):
|   {
|     "id_chamado": int,
|     "novo_status": "string",
|     "responsavel": "string",
|     "solucao": "string" (Obrigatório se novo_status == 'Resolvido')
|   }
|
| NOTA: O campo 'id_local' é a chave estrangeira padrão para regiões.
|--------------------------------------------------------------------------
*/

// --- 1. CONFIGURAÇÃO INICIAL E SEGURANÇA (A PORTARIA) ---
// Define que todas as respostas desta página serão no formato JSON
header("Content-type: application/json");

// Importa o arquivo que faz a conexão com o banco de dados
require '../config.php'; 
// Importa o arquivo responsável por verificar tokens de acesso (JWT)
require 'auth_jwt.php'; 

// Executa a função de validação. Se o usuário não tiver um token válido, 
// o script para aqui mesmo e ele não acessa os chamados.
$token_decodificado = validarToken(); 

// --- 2. ROTEAMENTO DE AÇÕES ---
// Descobre qual método HTTP o Front-end enviou (GET, POST ou PUT)
$metodo = $_SERVER['REQUEST_METHOD'];

// O 'switch' atua como um trilho de trem, direcionando o código com base no método escolhido
switch ($metodo) {

    // ==========================================
    // ROTA GET: LER/LISTAR OS CHAMADOS
    // ==========================================
    case 'GET':
        try {
            // Prepara a consulta base: busca os chamados e cruza com a tabela de regiões 
            // para trazer o nome da região e sua prioridade.
            $sql = "SELECT 
                        chamados.id_chamados, 
                        chamados.descricao, 
                        chamados.status, 
                        regioes.nome, 
                        regioes.prioridade 
                    FROM chamados 
                    INNER JOIN regioes ON chamados.id_local = regioes.id_regiao";

            // FILTRO DINÂMICO: Se o usuário enviou '?regiao=X' na URL e não está vazio...
            if (isset($_GET['regiao']) && !empty($_GET['regiao'])) {
                // ...adiciona uma restrição (WHERE) para buscar só daquela região específica
                $sql .= " WHERE chamados.id_local = :regiao"; 
            }

            // ORDENAÇÃO INTELIGENTE: Organiza os resultados primeiro pela prioridade (urgência) 
            // e depois pelos chamados mais recentes.
            $sql .= " ORDER BY regioes.prioridade ASC, chamados.data DESC";

            // Prepara o SQL no banco para evitar ataques (SQL Injection)
            $stmt = $pdo->prepare($sql);

            // Se o filtro de região foi ativado, vincula o número da região com segurança
            if (isset($_GET['regiao']) && !empty($_GET['regiao'])) {
                $stmt->bindValue(":regiao", $_GET['regiao']);
            }

            // Executa a busca
            $stmt->execute();
            // Transforma o resultado do banco em um array (lista) do PHP
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Responde com sucesso (200 OK) e entrega a lista em formato JSON
            http_response_code(200);
            echo json_encode($resultado);

        } catch(Exception $e) {
            // Se o banco falhar, devolve um erro seguro (500) sem quebrar o sistema
            http_response_code(500);
            echo json_encode(["ERRO!", "motivo" => $e->getMessage()]);
        }
        break;


    // ==========================================
    // ROTA POST: CRIAR UM NOVO CHAMADO
    // ==========================================
    case 'POST':
        // Lê os dados JSON enviados pelo usuário no corpo da requisição
        $json = file_get_contents("php://input");
        $dados = json_decode($json, true);

        // VALIDAÇÃO DE ENTRADA: Confere se os campos obrigatórios foram enviados
        if (!isset($dados["descricao"]) || !isset($dados["id_local"])) { 
            http_response_code(400); // 400 = Dados enviados pelo usuário estão incorretos
            echo json_encode(["Erro" => "Por Favor, descreva o problema ou um id valido"]);
            exit; // Interrompe o processo
        }

        // Prepara as informações que serão salvas
        $id_cliente = $token_decodificado->uid; // Pega o ID de quem está logado diretamente do token
        $descricao = $dados["descricao"];
        $status = "Aberto"; // Todo chamado novo nasce como "Aberto"
        $verificar_idRegiao = $dados["id_local"];

        // INTEGRIDADE REFERENCIAL: Verifica se a região enviada realmente existe no banco
        $stmt = $pdo->prepare("SELECT id_regiao FROM regioes WHERE id_regiao = :id");
        $stmt->bindValue(":id", $verificar_idRegiao);
        $stmt->execute();

        $regiao_valida = $stmt->fetch();
        // Se o banco não achar a região, bloqueia a criação do chamado
        if (!$regiao_valida){
            http_response_code(400);
            echo json_encode("erro!");
            exit;
        }

        // SALVANDO NO BANCO: Processo de inserção do novo chamado
        try {
            $sql = "INSERT INTO chamados (cliente, descricao, status, id_local) VALUES (:id_cliente, :descricao, :status, :local)";
            $stmt = $pdo->prepare($sql);

            // Insere os dados de forma segura (prevenindo ataques)
            $stmt->bindValue(":id_cliente", $id_cliente);
            $stmt->bindValue(":descricao", $descricao);
            $stmt->bindValue(":status", $status);
            $stmt->bindValue(":local", $verificar_idRegiao);

            $stmt->execute();

            // Responde com sucesso (201 Criado)
            http_response_code(201);
            echo json_encode(["mensagem" => "Chamado Aberto com sucesso!"]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["erro" => "Falha ao salvar no banco"]);
            exit;
        }
        break;


    // ==========================================
    // ROTA PUT: ATUALIZAR UM CHAMADO EXISTENTE
    // ==========================================
    case 'PUT':
        // Lê os dados JSON enviados
        $json = file_get_contents("php://input");
        $dados = json_decode($json, true);

        // 1. VALIDAÇÃO BÁSICA: Todos estes campos são obrigatórios para atualizar
        if (!isset($dados["id_chamado"]) || !isset($dados["novo_status"]) || !isset($dados["responsavel"])) {
            http_response_code(400);
            echo json_encode(["erro" => "Por favor, preencha todos os campos."]);
            exit;
        }

        // 2. REGRA DE NEGÓCIO: Se o técnico disser que o status é "Resolvido", 
        // ele é OBRIGADO a escrever qual foi a solução do problema.
        if ($dados["novo_status"] == "Resolvido" && empty($dados["solucao"])) {
            http_response_code(400);
            echo json_encode(["erro" => "Por favor, preencha a solucao do problema."]);
            exit;
        }

        // 3. INTEGRIDADE REFERENCIAL: Verifica se o ID do chamado enviado realmente existe
        $stmt = $pdo->prepare("SELECT id_chamados FROM chamados WHERE id_chamados = :id");
        $stmt->bindValue(":id", $dados["id_chamado"]);
        $stmt->execute();

        $chamado_valido = $stmt->fetch();
        // Se o chamado não existir, avisa e para a execução
        if (!$chamado_valido){
            http_response_code(400);
            echo json_encode(["erro!"=> "chamado inexistente, digite um id valido!"]);
            exit;
        }

        // Prepara as informações validadas
        $solucao = isset($dados["solucao"]) ? $dados["solucao"] : null; // Solução pode ser nula se não for resolvido
        $id_chamado = $dados["id_chamado"];
        $status = $dados["novo_status"];
        $responsavel = $dados["responsavel"];

        // SALVANDO A ATUALIZAÇÃO NO BANCO
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
            http_response_code(500);
            echo json_encode(["erro" => "Falha na atualizacao", "motivo" => $e->getMessage()]);
            exit;
        }
        break;


    // ==========================================
    // ROTA PADRÃO (SEGURANÇA CONTRA OUTROS MÉTODOS)
    // ==========================================
    default:
        // Se o usuário tentar acessar com um método não programado (ex: DELETE), 
        // o sistema bloqueia e retorna erro 405 (Método Não Permitido).
        http_response_code(405); 
        echo json_encode(["erro" => "Metodo HTTP nao suportado para esta rota."]);
        break;
}