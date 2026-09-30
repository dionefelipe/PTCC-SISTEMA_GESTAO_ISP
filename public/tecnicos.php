<?php
// /public/tecnicos.php
require_once __DIR__ . '/../src/Autenticacao/ServicoLogin.php';
require_once __DIR__ . '/../src/Provedor/ServicoTecnico.php';
require_once __DIR__ . '/includes/layout.php';

ServicoLogin::verificarAcessoRestrito(1);
$idProvedorLogado = $_SESSION['id_provedor'];
$mensagemErro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome_completo']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $matricula = trim($_POST['matricula']);
    $telefone = trim($_POST['telefone']);

    $sucesso = ServicoTecnico::criarTecnico($idProvedorLogado, $nome, $email, $senha, $matricula, $telefone);
    if ($sucesso) {
        header("Location: tecnicos.php");
        exit;
    } else {
        $mensagemErro = "Erro ao cadastrar técnico. Verifique se o e-mail já existe.";
    }
}

$listaTecnicos = ServicoTecnico::listarTecnicos($idProvedorLogado);

ob_start();
?>
<h2>Gestão de Técnicos</h2>
<p style="color: #64748b; margin-bottom: 20px;">Cadastre e gerencie a equipe técnica do seu provedor.</p>

<?php if ($mensagemErro): ?>
    <div class="alerta-erro"><?= htmlspecialchars($mensagemErro) ?></div>
<?php endif; ?>

<div class="bloco-secao">
    <h3>Cadastrar Novo Técnico</h3>
    <form method="POST" style="margin-top: 15px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <input type="text" name="nome_completo" placeholder="Nome Completo" required style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            <input type="email" name="email" placeholder="E-mail de Acesso" required style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            <input type="password" name="senha" placeholder="Senha Temporária" required style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            <input type="text" name="matricula" placeholder="Matrícula Trabalhista" required style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            <input type="text" name="telefone" placeholder="Telefone / Celular" required style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>
        <button type="submit" class="botao-primario">Salvar Técnico</button>
    </form>
</div>

<div class="bloco-secao">
    <h3>Técnicos da Equipe</h3>
    <table>
        <tr><th>Nome</th><th>Matrícula</th><th>E-mail</th><th>Estado</th></tr>
        <?php if (empty($listaTecnicos)): ?>
            <tr><td colspan="4">Nenhum técnico registado.</td></tr>
        <?php else: ?>
            <?php foreach ($listaTecnicos as $tec): ?>
            <tr>
                <td><?= htmlspecialchars($tec['nome_completo']) ?></td>
                <td><?= htmlspecialchars($tec['matricula_trabalhista']) ?></td>
                <td><?= htmlspecialchars($tec['email_login']) ?></td>
                <td><?= htmlspecialchars($tec['status_disponibilidade']) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>
<?php
$conteudoHTML = ob_get_clean();
renderizarLayout("Gestão de Técnicos", $conteudoHTML, 1, 'tecnicos');
?>