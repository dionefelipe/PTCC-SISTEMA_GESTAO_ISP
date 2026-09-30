<?php
// /public/alterar_plano.php
require_once __DIR__ . '/../src/Autenticacao/ServicoLogin.php';
require_once __DIR__ . '/../src/Cliente/ServicoCliente.php';
require_once __DIR__ . '/../config/conexao_banco.php';
require_once __DIR__ . '/includes/layout.php';

ServicoLogin::verificarAcessoRestrito(3); // Apenas Cliente
$idUsuarioLogado = $_SESSION['id_usuario'];
$pdo = ConexaoBanco::obterConexao();

// Busca dados do cliente, plano atual, renda e bairro
$sqlCli = "SELECT c.id_cliente, c.id_plano_contratado, c.renda_mensal_bruta, c.bairro, 
                  p.nome_plano as plano_atual, p.valor_mensal as valor_atual, pr.razao_social as provedor_atual 
           FROM clientes c
           LEFT JOIN planos_internet p ON c.id_plano_contratado = p.id_plano
           LEFT JOIN provedores pr ON p.id_provedor = pr.id_provedor
           WHERE c.id_usuario = :id_usuario";
$cmdCli = $pdo->prepare($sqlCli);
$cmdCli->execute([':id_usuario' => $idUsuarioLogado]);
$cliente = $cmdCli->fetch();

$mensagem = '';
// Processa a mudança de plano
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['novo_id_plano'])) {
    $novoPlano = $_POST['novo_id_plano'];
    $sqlUpdate = "UPDATE clientes SET id_plano_contratado = :novo_plano WHERE id_cliente = :id_cliente";
    $cmdUp = $pdo->prepare($sqlUpdate);
    if ($cmdUp->execute([':novo_plano' => $novoPlano, ':id_cliente' => $cliente['id_cliente']])) {
        $mensagem = "Plano alterado com sucesso!";
        header("Refresh: 1; URL=alterar_plano.php");
    }
}

// Verifica se o cliente clicou para ver os planos concorrentes
$mostrarCatalogo = isset($_GET['ver_planos']) && $_GET['ver_planos'] == '1';
$planosConcorrentes = [];

if ($mostrarCatalogo && !empty($cliente['bairro'])) {
    $rendaBruta = $cliente['renda_mensal_bruta'] > 0 ? $cliente['renda_mensal_bruta'] : 1000.00;
    $planosConcorrentes = ServicoCliente::buscarPlanosConcorrentesElegiveis($cliente['bairro'], $rendaBruta);
}

ob_start();
?>
<h2>Mudança e Concorrência de Planos de Internet</h2>
<p style="color: #64748b; margin-bottom: 20px;">Consulte o seu plano atual ou explore ofertas de diferentes operadoras na sua região, elegíveis à sua renda familiar.</p>

<?php if ($mensagem): ?>
    <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-weight: bold;"><?= $mensagem ?></div>
<?php endif; ?>

<!-- Bloco do Plano Atual -->
<div class="bloco-secao" style="border-left: 4px solid #2563eb;">
    <h3>O Seu Plano Atual</h3>
    <div style="margin-top: 10px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <div>
            <span style="font-size: 13px; color: #64748b;">Operadora:</span><br>
            <strong><?= htmlspecialchars($cliente['provedor_atual'] ?? 'N/D') ?></strong>
        </div>
        <div>
            <span style="font-size: 13px; color: #64748b;">Plano Contratado:</span><br>
            <strong><?= htmlspecialchars($cliente['plano_atual'] ?? 'Nenhum plano') ?></strong>
        </div>
        <div>
            <span style="font-size: 13px; color: #64748b;">Valor Mensal:</span><br>
            <strong>R$ <?= number_format($cliente['valor_atual'] ?? 0, 2, ',', '.') ?></strong>
        </div>
    </div>

    <?php if (!$mostrarCatalogo): ?>
        <div style="margin-top: 20px;">
            <a href="alterar_plano.php?ver_planos=1" class="botao-primario" style="background: #0ea5e9; text-decoration: none; display: inline-block;">🔍 Ver Planos Disponíveis no Mercado</a>
        </div>
    <?php endif; ?>
</div>

<!-- Catálogo de Concorrência de Preços (Cards) -->
<?php if ($mostrarCatalogo): ?>
<div class="bloco-secao" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3>Ofertas Concorrentes na Região (<?= htmlspecialchars($cliente['bairro']) ?>)</h3>
        <a href="alterar_plano.php" style="color: #64748b; text-decoration: none; font-size: 14px;">Ocultar Catálogo ✕</a>
    </div>
    <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">Estes planos respeitam o limite regulamentar de comprometer no máximo 9% da sua renda familiar bruta informada (R$ <?= number_format($cliente['renda_mensal_bruta'], 2, ',', '.') ?>).</p>

    <?php if (empty($planosConcorrentes)): ?>
        <p style="color: #991b1b; background: #fee2e2; padding: 12px; border-radius: 6px;">Não foram encontrados planos elegíveis dentro do limite de 9% da sua renda para a sua região no momento.</p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <?php foreach ($planosConcorrentes as $plano): 
                $impactoRenda = ($cliente['renda_mensal_bruta'] > 0) ? ($plano['valor_mensal'] / $cliente['renda_mensal_bruta']) * 100 : 0;
            ?>
            <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <!-- Badge da Operadora -->
                    <span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase;"><?= htmlspecialchars($plano['razao_social']) ?></span>
                    
                    <h4 style="margin-top: 12px; font-size: 18px; color: #1e293b;"><?= htmlspecialchars($plano['nome_plano']) ?></h4>
                    <p style="font-size: 14px; color: #0284c7; font-weight: bold; margin-top: 4px;"><?= $plano['velocidade_megas'] ?> Megas de Velocidade</p>
                    <p style="font-size: 13px; color: #64748b; margin-top: 8px;">Conectividade de alta estabilidade para a sua residência.</p>
                </div>

                <div style="margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                    <div style="margin-bottom: 12px;">
                        <span style="font-size: 12px; color: #64748b;">Mensalidade:</span><br>
                        <strong style="font-size: 20px; color: #0f172a;">R$ <?= number_format($plano['valor_mensal'], 2, ',', '.') ?></strong>
                    </div>

                    <!-- Indicador de Impacto na Renda Familiar -->
                    <div style="background: #f1f5f9; padding: 8px 10px; border-radius: 6px; margin-bottom: 15px; font-size: 12px; color: #334155;">
                        📊 Consome <strong><?= number_format($impactoRenda, 1, ',', '.') ?>%</strong> da sua renda familiar bruta mensal.
                    </div>

                    <?php if ($cliente['id_plano_contratado'] == $plano['id_plano']): ?>
                        <div style="text-align: center; background: #dcfce7; color: #166534; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px;">Plano Atual Ativo</div>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="novo_id_plano" value="<?= $plano['id_plano'] ?>">
                            <button type="submit" class="botao-primario" style="width: 100%;">Mudar para este Plano</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php
$conteudoHTML = ob_get_clean();
renderizarLayout("Mudança de Planos", $conteudoHTML, 3, 'alterar_plano');
?>