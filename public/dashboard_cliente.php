<?php
// /public/dashboard_cliente.php
require_once __DIR__ . '/../src/Autenticacao/ServicoLogin.php';
require_once __DIR__ . '/../config/conexao_banco.php';
require_once __DIR__ . '/includes/layout.php';

ServicoLogin::verificarAcessoRestrito(3);
$idUsuarioLogado = $_SESSION['id_usuario'];

$pdo = ConexaoBanco::obterConexao();
$sqlCliente = "SELECT nome_completo, id_cliente FROM clientes WHERE id_usuario = :id_usuario";
$cmdCliente = $pdo->prepare($sqlCliente);
$cmdCliente->execute([':id_usuario' => $idUsuarioLogado]);
$cliente = $cmdCliente->fetch();

$sqlChamados = "SELECT id_chamado, assunto_chamado, status_chamado, data_abertura 
                FROM chamados_suporte WHERE id_cliente = :id_cliente ORDER BY data_abertura DESC";
$cmdChamados = $pdo->prepare($sqlChamados);
$cmdChamados->execute([':id_cliente' => $cliente['id_cliente']]);
$chamados = $cmdChamados->fetchAll();

ob_start();
?>
<h2>Painel do Cliente - Acompanhamento</h2>
<p style="color: #64748b; margin-bottom: 20px;">Consulte o estado atual dos seus pedidos de suporte técnico.</p>

<div class="bloco-secao">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3>Os Meus Chamados</h3>
        <a href="abrir_chamado.php" class="botao-primario">+ Abrir Novo Chamado</a>
    </div>
    <table>
        <tr><th>Protocolo</th><th>Assunto</th><th>Data de Abertura</th><th>Estado Atual</th></tr>
        <?php if (empty($chamados)): ?>
            <tr><td colspan="4">Não tem nenhum chamado registado.</td></tr>
        <?php else: ?>
            <?php foreach ($chamados as $ticket): ?>
            <tr>
                <td>#<?= $ticket['id_chamado'] ?></td>
                <td><?= htmlspecialchars($ticket['assunto_chamado']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($ticket['data_abertura'])) ?></td>
                <td><strong style="color: #2563eb;"><?= $ticket['status_chamado'] ?></strong></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>
<?php
$conteudoHTML = ob_get_clean();
renderizarLayout("Acompanhar Chamados", $conteudoHTML, 3, 'chamados');
?>