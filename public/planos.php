<?php
// /public/planos.php
require_once __DIR__ . '/../src/Autenticacao/ServicoLogin.php';
require_once __DIR__ . '/../src/Provedor/ServicoPlano.php';
require_once __DIR__ . '/includes/layout.php';

ServicoLogin::verificarAcessoRestrito(1);
$idProvedorLogado = $_SESSION['id_provedor'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    ServicoPlano::criarPlano(
        $idProvedorLogado, $_POST['nome_plano'], $_POST['velocidade'], 
        $_POST['valor'], $_POST['descricao']
    );
    header("Location: planos.php");
    exit;
}

$listaPlanos = ServicoPlano::listarPlanos($idProvedorLogado);

ob_start();
?>
<h2>Gestão de Planos de Internet</h2>
<p style="color: #64748b; margin-bottom: 20px;">Crie e gira os planos oferecidos aos clientes do seu provedor.</p>

<div class="bloco-secao">
    <h3>Cadastrar Novo Plano</h3>
    <form method="POST" style="margin-top: 15px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <input type="text" name="nome_plano" placeholder="Nome do Plano (Ex: Fibra 500MB)" required style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            <input type="number" name="velocidade" placeholder="Velocidade (Megas)" required style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            <input type="number" step="0.01" name="valor" placeholder="Valor Mensal (R$)" required style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            <input type="text" name="descricao" placeholder="Descrição rápida" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>
        <button type="submit" class="botao-primario">Salvar Plano</button>
    </form>
</div>

<div class="bloco-secao">
    <h3>Planos Cadastrados</h3>
    <table>
        <tr><th>Nome do Plano</th><th>Velocidade</th><th>Valor Mensal</th><th>Status</th></tr>
        <?php if (empty($listaPlanos)): ?>
            <tr><td colspan="4">Nenhum plano registado.</td></tr>
        <?php else: ?>
            <?php foreach ($listaPlanos as $plano): ?>
            <tr>
                <td><?= htmlspecialchars($plano['nome_plano']) ?></td>
                <td><?= $plano['velocidade_megas'] ?> MB</td>
                <td>R$ <?= number_format($plano['valor_mensal'], 2, ',', '.') ?></td>
                <td><?= $plano['status_disponivel'] ? 'Ativo' : 'Inativo' ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>
<?php
$conteudoHTML = ob_get_clean();
renderizarLayout("Gestão de Planos", $conteudoHTML, 1, 'planos');
?>