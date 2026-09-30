<?php
// /public/dashboard_tecnico.php
require_once __DIR__ . '/../src/Autenticacao/ServicoLogin.php';
require_once __DIR__ . '/../config/conexao_banco.php';
require_once __DIR__ . '/includes/layout.php';

ServicoLogin::verificarAcessoRestrito(2); // Apenas Técnico
$idUsuarioLogado = $_SESSION['id_usuario'];
$pdo = ConexaoBanco::obterConexao();

$sqlTec = "SELECT id_tecnico, nome_completo FROM tecnicos WHERE id_usuario = :id_usuario";
$cmdTec = $pdo->prepare($sqlTec);
$cmdTec->execute([':id_usuario' => $idUsuarioLogado]);
$tecnico = $cmdTec->fetch();
$idTecnico = $tecnico['id_tecnico'];

$mensagemErro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idChamado = $_POST['id_chamado'];
    $acao = $_POST['acao_status'];

    if ($acao === 'Em Atendimento') {
        $sqlUp = "UPDATE chamados_suporte SET status_chamado = 'Em Atendimento' WHERE id_chamado = :id AND id_tecnico_atribuido = :tec";
        $cmdUp = $pdo->prepare($sqlUp);
        $cmdUp->execute([':id' => $idChamado, ':tec' => $idTecnico]);
        header("Location: dashboard_tecnico.php");
        exit;
    } elseif ($acao === 'Concluir') {
        $solucao = trim($_POST['solucao']);
        if (empty($solucao)) {
            $mensagemErro = "A descrição da solução é obrigatória para finalizar o chamado.";
        } else {
            $sqlFim = "UPDATE chamados_suporte SET status_chamado = 'Concluído', solucao = :solucao, data_conclusao = NOW() WHERE id_chamado = :id AND id_tecnico_atribuido = :tec";
            $cmdFim = $pdo->prepare($sqlFim);
            $cmdFim->execute([':solucao' => $solucao, ':id' => $idChamado, ':tec' => $idTecnico]);
            header("Location: dashboard_tecnico.php");
            exit;
        }
    }
}

// Busca a rota do técnico ordenada pelo algoritmo de peso de forma invisível
$sqlRota = "SELECT c.id_chamado, c.assunto_chamado, c.descricao_problema, c.status_chamado, cl.nome_completo, cl.endereco_completo, cl.bairro 
            FROM chamados_suporte c
            INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
            WHERE c.id_tecnico_atribuido = :id_tecnico AND c.status_chamado != 'Concluído'
            ORDER BY c.data_abertura ASC";
$cmdRota = $pdo->prepare($sqlRota);
$cmdRota->execute([':id_tecnico' => $idTecnico]);
$rotaTrabalho = $cmdRota->fetchAll();

ob_start();
?>
<h2>Rota de Trabalho - Técnico <?= htmlspecialchars($tecnico['nome_completo']) ?></h2>
<p style="color: #64748b; margin-bottom: 20px;">Gerencie os seus atendimentos e preencha a solução ao concluir o serviço.</p>

<?php if ($mensagemErro): ?>
    <div class="alerta-erro"><?= htmlspecialchars($mensagemErro) ?></div>
<?php endif; ?>

<?php if (empty($rotaTrabalho)): ?>
    <div class="bloco-secao">
        <p>Não possui chamados atribuídos pendentes no momento.</p>
    </div>
<?php else: ?>
    <?php foreach ($rotaTrabalho as $tarefa): ?>
    <div class="bloco-secao" style="border-left: 4px solid #2563eb;">
        <h3>Chamado #<?= $tarefa['id_chamado'] ?> - <?= htmlspecialchars($tarefa['assunto_chamado']) ?></h3>
        <p style="margin-top: 8px;"><strong>Cliente:</strong> <?= htmlspecialchars($tarefa['nome_completo']) ?> | <strong>Área:</strong> <?= htmlspecialchars($tarefa['bairro']) ?></p>
        <p><strong>Endereço:</strong> <?= htmlspecialchars($tarefa['endereco_completo']) ?></p>
        <p><strong>Descrição:</strong> <?= htmlspecialchars($tarefa['descricao_problema']) ?></p>
        <p style="margin-top: 5px;"><strong>Estado Atual:</strong> <strong><?= $tarefa['status_chamado'] ?></strong></p>
        
        <?php if ($tarefa['status_chamado'] == 'Atribuído'): ?>
            <form method="POST" style="margin-top: 15px;">
                <input type="hidden" name="id_chamado" value="<?= $tarefa['id_chamado'] ?>">
                <button type="submit" name="acao_status" value="Em Atendimento" class="botao-primario" style="background: #0284c7;">Iniciar Atendimento (Em Andamento)</button>
            </form>
        <?php elseif ($tarefa['status_chamado'] == 'Em Atendimento'): ?>
            <form method="POST" style="margin-top: 15px; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #cbd5e1;">
                <input type="hidden" name="id_chamado" value="<?= $tarefa['id_chamado'] ?>">
                <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:13px; color:#334155;">Relatório de Solução / Justificativa (Obrigatório)</label>
                <textarea name="solucao" rows="3" placeholder="Descreva detalhadamente o serviço executado..." required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; margin-bottom: 10px;"></textarea>
                <button type="submit" name="acao_status" value="Concluir" class="botao-primario" style="background: #16a34a;">Finalizar Chamado</button>
            </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php
$conteudoHTML = ob_get_clean();
renderizarLayout("Painel do Técnico", $conteudoHTML, 2, 'rota');
?>