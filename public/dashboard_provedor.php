<?php
// /public/dashboard_provedor.php
require_once __DIR__ . '/../src/Autenticacao/ServicoLogin.php';
require_once __DIR__ . '/../config/conexao_banco.php';
require_once __DIR__ . '/includes/layout.php';

ServicoLogin::verificarAcessoRestrito(1);
$idProvedorLogado = $_SESSION['id_provedor'];
$pdo = ConexaoBanco::obterConexao();

// Processa a designação do técnico pelo provedor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_chamado'], $_POST['id_tecnico'])) {
    $idChamado = $_POST['id_chamado'];
    $idTecnico = $_POST['id_tecnico'];

    $sqlAtribuir = "UPDATE chamados_suporte SET id_tecnico_atribuido = :tecnico, status_chamado = 'Atribuído' WHERE id_chamado = :chamado AND id_provedor = :provedor";
    $cmdAtrib = $pdo->prepare($sqlAtribuir);
    $cmdAtrib->execute([':tecnico' => $idTecnico, ':chamado' => $idChamado, ':provedor' => $idProvedorLogado]);
    header("Location: dashboard_provedor.php");
    exit;
}

// Busca chamados, técnicos e a solução informada pelo técnico (sem expor a prioridade visualmente)
$sqlChamados = "SELECT c.id_chamado, c.assunto_chamado, c.descricao_problema, c.status_chamado, c.data_abertura, c.solucao,
                       cl.nome_completo as cliente, cl.bairro, t.nome_completo as tecnico_atual
                FROM chamados_suporte c
                INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
                LEFT JOIN tecnicos t ON c.id_tecnico_atribuido = t.id_tecnico
                WHERE c.id_provedor = :id_provedor
                ORDER BY c.data_abertura DESC";
$cmd = $pdo->prepare($sqlChamados);
$cmd->execute([':id_provedor' => $idProvedorLogado]);
$chamados = $cmd->fetchAll();

// Lista de técnicos disponíveis do provedor
$sqlTec = "SELECT t.id_tecnico, t.nome_completo FROM tecnicos t INNER JOIN usuarios_sistema u ON t.id_usuario = u.id_usuario WHERE u.id_provedor = :prov";
$cmdTec = $pdo->prepare($sqlTec);
$cmdTec->execute([':prov' => $idProvedorLogado]);
$tecnicos = $cmdTec->fetchAll();

ob_start();
?>
<h2>Painel Gerencial - Gestão e Acompanhamento de Chamados</h2>
<p style="color: #64748b; margin-bottom: 20px;">Atribua técnicos com base na área e consulte os relatórios de solução após o encerramento.</p>

<div class="bloco-secao">
    <h3>Histórico e Estado dos Chamados</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Cliente / Área</th>
            <th>Assunto / Descrição</th>
            <th>Estado</th>
            <th>Técnico / Ação / Solução</th>
        </tr>
        <?php if (empty($chamados)): ?>
            <tr><td colspan="5">Nenhum chamado registado.</td></tr>
        <?php else: ?>
            <?php foreach ($chamados as $ch): ?>
            <tr>
                <td>#<?= $ch['id_chamado'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($ch['cliente']) ?></strong><br>
                    <span style="font-size: 12px; color: #64748b;">Área: <?= htmlspecialchars($ch['bairro']) ?></span>
                </td>
                <td>
                    <strong><?= htmlspecialchars($ch['assunto_chamado']) ?></strong><br>
                    <span style="font-size: 13px; color: #475569;"><?= htmlspecialchars($ch['descricao_problema']) ?></span>
                </td>
                <td><strong><?= $ch['status_chamado'] ?></strong></td>
                <td>
                    <?php if ($ch['status_chamado'] == 'Aberto'): ?>
                        <form method="POST" style="display: flex; gap: 5px;">
                            <input type="hidden" name="id_chamado" value="<?= $ch['id_chamado'] ?>">
                            <select name="id_tecnico" required style="padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px;">
                                <option value="">Designar Técnico...</option>
                                <?php foreach($tecnicos as $t): ?>
                                    <option value="<?= $t['id_tecnico'] ?>"><?= htmlspecialchars($t['nome_completo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="botao-primario" style="padding: 6px 12px; font-size: 13px;">Atribuir</button>
                        </form>
                    <?php else: ?>
                        <span style="font-size: 13px;"><strong>Técnico:</strong> <?= htmlspecialchars($ch['tecnico_atual']) ?></span>
                        <?php if ($ch['status_chamado'] == 'Concluído' && !empty($ch['solucao'])): ?>
                            <div style="margin-top: 6px; background: #f1f5f9; padding: 8px; border-radius: 4px; font-size: 12px;">
                                <strong>Justificativa/Solução:</strong><br>
                                <?= htmlspecialchars($ch['solucao']) ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>
<?php
$conteudoHTML = ob_get_clean();
renderizarLayout("Dashboard Provedor", $conteudoHTML, 1, 'dashboard');
?>