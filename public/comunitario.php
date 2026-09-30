<?php
// /public/comunitario.php
require_once __DIR__ . '/../src/Autenticacao/ServicoLogin.php';
require_once __DIR__ . '/../src/Provedor/ServicoComunitario.php';
require_once __DIR__ . '/includes/layout.php';

ServicoLogin::verificarAcessoRestrito(1);
$idProvedor =$_SESSION['id_provedor'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ação de Exclusão
    if (isset($_POST['acao']) &&$_POST['acao'] === 'excluir') {
        ServicoComunitario::excluirPonto($_POST['id_ponto'],$idProvedor);
        header("Location: comunitario.php");
        exit;
    }

    // Ação de Cadastro
    ServicoComunitario::cadastrarPonto(
        $idProvedor, 
        trim($_POST['nome_local']), 
        trim($_POST['cep']), 
        trim($_POST['bairro']), 
        trim($_POST['endereco']),
        $_POST['latitude_geografica'],$_POST['longitude_geografica']
    );
    header("Location: comunitario.php");
    exit;
}

$pontos = ServicoComunitario::listarPontos($idProvedor);

ob_start();
?>
<h2>Inclusão Digital - Pontos de Acesso Comunitário</h2>
<p style="color: #64748b; margin-bottom: 20px;">Cadastre hotspots sociais. O endereço e a geolocalização são preenchidos automaticamente pelo CEP.</p>

<div class="bloco-secao">
    <h3>Registar Novo Ponto Wi-Fi Social</h3>
    <form method="POST" style="margin-top: 15px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div style="grid-column: span 2;">
                <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:14px; color:#334155;">Nome do Local / Ponto</label>
                <input type="text" name="nome_local" placeholder="Ex: Praça Comunitária São Judas" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:14px; color:#334155;">CEP (Busca Automática)</label>
                <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000" required onblur="consultarCep(this.value)" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:14px; color:#334155;">Bairro</label>
                <input type="text" id="bairro" name="bairro" readonly required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f1f5f9;">
            </div>
            <div style="grid-column: span 2;">
                <label style="display:block; margin-bottom:5px; font-weight:bold; font-size:14px; color:#334155;">Endereço Completo</label>
                <input type="text" id="endereco" name="endereco" readonly required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f1f5f9;">
            </div>
        </div>

        <!-- Campos ocultos de Latitude e Longitude -->
        <input type="hidden" id="latitude_geografica" name="latitude_geografica">
        <input type="hidden" id="longitude_geografica" name="longitude_geografica">

        <button type="submit" class="botao-primario">Salvar Ponto Comunitário</button>
    </form>
</div>

<div class="bloco-secao">
    <h3>Pontos Ativos na Região</h3>
    <table>
        <tr><th>Local</th><th>CEP</th><th>Bairro</th><th>Endereço</th><th>Ação</th></tr>
        <?php if (empty($pontos)): ?>
            <tr><td colspan="5">Nenhum ponto comunitário registado.</td></tr>
        <?php else: ?>
            <?php foreach ($pontos as$pt): ?>
            <tr>
                <td><?= htmlspecialchars($pt['nome_local']) ?></td>
                <td><?= htmlspecialchars($pt['cep'] ?? 'N/D') ?></td>
                <td><?= htmlspecialchars($pt['bairro']) ?></td>
                <td><?= htmlspecialchars($pt['endereco']) ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este ponto comunitário?');" style="display:inline;">
                        <input type="hidden" name="acao" value="excluir">
                        <input type="hidden" name="id_ponto" value="<?= $pt['id_ponto'] ?>">
                        <button type="submit" style="background: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">Excluir</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

<script>
    async function consultarCep(cep) {
        cep = cep.replace(/\D/g, '');
        if (cep.length !== 8) return;

        try {
            let respostaCep = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            let dadosCep = await respostaCep.json();

            if (dadosCep.erro) {
                alert("CEP não encontrado.");
                return;
            }

            document.getElementById('bairro').value = dadosCep.bairro || '';
            document.getElementById('endereco').value = `${dadosCep.logradouro || ''}, ${dadosCep.localidade} - ${dadosCep.uf}`;

            // Busca coordenadas para o mapa
            let queryEndereco = `${dadosCep.logradouro}, ${dadosCep.bairro}, ${dadosCep.localidade} - ${dadosCep.uf}`;
            let respostaGeo = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(queryEndereco)}`);
            let dadosGeo = await respostaGeo.json();

            if (dadosGeo && dadosGeo.length > 0) {
                document.getElementById('latitude_geografica').value = dadosGeo[0].lat;
                document.getElementById('longitude_geografica').value = dadosGeo[0].lon;
            } else {
                document.getElementById('latitude_geografica').value = "-23.550520";
                document.getElementById('longitude_geografica').value = "-46.633309";
            }
        } catch (erro) {
            console.error("Erro ao consultar geolocalização do CEP:", erro);
            document.getElementById('latitude_geografica').value = "-23.550520";
            document.getElementById('longitude_geografica').value = "-46.633309";
        }
    }
</script>
<?php
$conteudoHTML = ob_get_clean();
renderizarLayout("Inclusão Comunitária", $conteudoHTML, 1, 'comunitario');
?>