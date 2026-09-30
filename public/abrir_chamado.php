<?php
// /public/abrir_chamado.php
require_once __DIR__ . '/../src/Autenticacao/ServicoLogin.php';
require_once __DIR__ . '/../config/conexao_banco.php';
require_once __DIR__ . '/includes/layout.php';

ServicoLogin::verificarAcessoRestrito(3); // Apenas Cliente
$idUsuarioLogado = $_SESSION['id_usuario'];
$pdo = ConexaoBanco::obterConexao();

// Busca dados do cliente, área (bairro cadastrado pelo CEP) e provedor associado
$sqlCliente = "SELECT c.id_cliente, c.nome_completo, c.bairro, p.id_provedor 
               FROM clientes c
               LEFT JOIN planos_internet p ON c.id_plano_contratado = p.id_plano
               WHERE c.id_usuario = :id_usuario";
$cmd = $pdo->prepare($sqlCliente);
$cmd->execute([':id_usuario' => $idUsuarioLogado]);
$cliente = $cmd->fetch();

$mensagem = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assunto = trim($_POST['assunto']);
    $descricao = trim($_POST['descricao']);
    $idProvedor = $cliente['id_provedor'] ?? 1;
    $bairroCliente = $cliente['bairro'];

    // 1. Consulta automática do peso da região com base no bairro do cliente
    $sqlPeso = "SELECT peso_deslocamento FROM matriz_pesos_bairros 
                WHERE id_provedor = :provedor AND bairro_destino = :bairro LIMIT 1";
    $cmdPeso = $pdo->prepare($sqlPeso);
    $cmdPeso->execute([':provedor' => $idProvedor, ':bairro' => $bairroCliente]);
    $resultadoPeso = $cmdPeso->fetch();

    // Define um peso padrão (ex: 10) caso o bairro específico não esteja mapeado na matriz
    $pesoRegiao = $resultadoPeso ? $resultadoPeso['peso_deslocamento'] : 10;

    // 2. Atribui a prioridade de forma automatizada com base no custo/peso de deslocamento da região
    if ($pesoRegiao > 20) {
        $prioridadeCalculada = 'Urgente';
    } elseif ($pesoRegiao > 15) {
        $prioridadeCalculada = 'Alta';
    } elseif ($pesoRegiao > 8) {
        $prioridadeCalculada = 'Normal';
    } else {
        $prioridadeCalculada = 'Baixa';
    }

    // 3. Insere o chamado gravando o cliente, descrição, área e a prioridade calculada automaticamente
    $sqlInsert = "INSERT INTO chamados_suporte (id_provedor, id_cliente, assunto_chamado, descricao_problema, prioridade, status_chamado) 
                  VALUES (:provedor, :cliente, :assunto, :descricao, :prioridade, 'Aberto')";
    $cmdInsert = $pdo->prepare($sqlInsert);
    $executou = $cmdInsert->execute([
        ':provedor' => $idProvedor,
        ':cliente' => $cliente['id_cliente'],
        ':assunto' => $assunto,
        ':descricao' => $descricao,
        ':prioridade' => $prioridadeCalculada
    ]);

    if ($executou) {
        header("Location: dashboard_cliente.php");
        exit;
    } else {
        $mensagem = "Erro ao abrir o chamado. Tente novamente.";
    }
}

ob_start();
?>
<h2>Abrir Novo Chamado de Suporte</h2>
<p style="color: #64748b; margin-bottom: 20px;">Descreva o seu problema abaixo. A prioridade de atendimento é calculada automaticamente de acordo com a sua área geográfica.</p>

<?php if ($mensagem): ?>
    <div class="alerta-erro"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>

<div class="bloco-secao">
    <form method="POST">
        <div style="margin-bottom: 15px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:14px; color:#334155;">Assunto</label>
            <input type="text" name="assunto" placeholder="Ex: Queda de Conexão / Sem Sinal" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:14px; color:#334155;">Descrição Detalhada do Problema</label>
            <textarea name="descricao" rows="5" placeholder="Explique detalhadamente o que está a acontecer..." required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;"></textarea>
        </div>

        <button type="submit" class="botao-primario">Submeter Chamado</button>
    </form>
</div>
<?php
$conteudoHTML = ob_get_clean();
renderizarLayout("Abrir Chamado", $conteudoHTML, 3, 'abrir_chamado');
?>