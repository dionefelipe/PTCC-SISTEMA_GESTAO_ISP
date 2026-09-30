<?php
// /public/cadastro.php
require_once __DIR__ . '/../src/Autenticacao/ServicoCadastro.php';

$mensagem = '';$sucesso = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipoPerfil =$_POST['tipo_perfil'] ?? '';

    if ($tipoPerfil === 'provedor') {$sucesso = ServicoCadastro::registarProvedor(
            trim($_POST['nome_social']), trim($_POST['cnpj']), trim($_POST['cep']), 
            trim($_POST['locais_atendimento']), trim($_POST['email']), trim($_POST['senha'])
        );
        $mensagem =$sucesso ? "Provedor registado com sucesso!" : "Erro ao registar provedor.";
    } 
    elseif ($tipoPerfil === 'tecnico') {$sucesso = ServicoCadastro::registarTecnico(
            intval($_POST['id_provedor']), trim($_POST['email_institucional']), trim($_POST['senha'])
        );
        $mensagem =$sucesso ? "Técnico registado com sucesso!" : "Erro ao registar técnico.";
    } 
    elseif ($tipoPerfil === 'cliente') {$sucesso = ServicoCadastro::registarCliente(
            trim($_POST['nome']), trim($_POST['cpf']), trim($_POST['cep']), 
            floatval($_POST['renda_bruta']), trim($_POST['email']), trim($_POST['senha']),
            trim($_POST['bairro']), trim($_POST['endereco']), $_POST['latitude_geografica'] ?? null, $_POST['longitude_geografica'] ?? null
        );
        $mensagem =$sucesso ? "Cliente registado com sucesso! Já pode fazer login." : "Erro ao registar cliente (e-mail ou CPF duplicado).";
    }
}

$provedoresDisponiveis = ServicoCadastro::listarProvedores();
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registo - SocialConecta ISP</title>
    <link rel="stylesheet" href="css/estilo_base.css">
    <style>
        body { background: #f1f5f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; font-family: sans-serif; }
        .card-registo { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 100%; max-width: 550px; }
        .abas { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .aba-btn { background: none; border: none; font-size: 15px; font-weight: bold; color: #64748b; cursor: pointer; padding: 8px 16px; border-radius: 6px; transition: 0.2s; }
        .aba-btn.ativo { background: #2563eb; color: #fff; }
        .formulario-grupo { display: none; }
        .formulario-grupo.ativo { display: block; }
        .form-control { margin-bottom: 15px; }
        .form-control label { display: block; font-size: 13px; font-weight: bold; color: #334155; margin-bottom: 5px; }
        .form-control input, .form-control select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        .botao-submit { background: #10b981; color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 10px; }
        .botao-submit:hover { background: #059669; }
        .botao-submit:disabled { background: #94a3b8; cursor: not-allowed; }
        .voltar-login { display: block; text-align: center; margin-top: 15px; color: #2563eb; text-decoration: none; font-size: 14px; }
        #status-geo { font-size: 12px; margin-top: 5px; font-weight: bold; }
    </style>
</head>
<body>

<div class="card-registo">
    <h2 style="margin-top: 0; color: #1e293b; text-align: center;">SocialConecta - Registo</h2>

    <?php if ($mensagem): ?>
        <div style="padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-weight: bold; background: <?= $sucesso ? '#dcfce7; color: #166534;' : '#fee2e2; color: #991b1b;' ?>">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <div class="abas">
        <button type="button" class="aba-btn ativo" onclick="mudarAba('cliente', event)">👤 Cliente</button>
        <button type="button" class="aba-btn" onclick="mudarAba('provedor', event)">🏢 Provedor</button>
        <button type="button" class="aba-btn" onclick="mudarAba('tecnico', event)">🔧 Técnico</button>
    </div>

    <!-- 1. Formulário Cliente -->
    <form id="form-cliente" class="formulario-grupo ativo" method="POST">
        <input type="hidden" name="tipo_perfil" value="cliente">
        
        <div class="form-control">
            <label>Nome</label>
            <input type="text" name="nome" required>
        </div>
        <div class="form-control">
            <label>CPF</label>
            <input type="text" name="cpf" placeholder="000.000.000-00" required>
        </div>
        <div class="form-control">
            <label>CEP (Preencha para calcular a localização exata)</label>
            <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000" onblur="consultarCep(this.value)" required>
            <div id="status-geo" style="color: #d97706;">⚠️ Digite o CEP para detetar a morada e geolocalização.</div>
        </div>
        <div class="form-control">
            <label>Renda Bruta (R$)</label>
            <input type="number" step="0.01" name="renda_bruta" placeholder="1500.00" required>
        </div>
        <div class="form-control">
            <label>E-mail</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-control">
            <label>Senha</label>
            <input type="password" name="senha" required>
        </div>

        <input type="hidden" id="bairro" name="bairro">
        <input type="hidden" id="endereco" name="endereco">
        <input type="hidden" id="latitude_geografica" name="latitude_geografica">
        <input type="hidden" id="longitude_geografica" name="longitude_geografica">

        <button type="submit" id="btn-registar-cliente" class="botao-submit" disabled>Aguardando validação do CEP...</button>
    </form>

    <!-- 2. Formulário Provedor -->
    <form id="form-provedor" class="formulario-grupo" method="POST">
        <input type="hidden" name="tipo_perfil" value="provedor">
        <div class="form-control">
            <label>Nome Social / Razão Social</label>
            <input type="text" name="nome_social" placeholder="Ex: Telecom Conecta" required>
        </div>
        <div class="form-control">
            <label>CNPJ</label>
            <input type="text" name="cnpj" placeholder="00.000.000/0001-00" required>
        </div>
        <div class="form-control">
            <label>CEP</label>
            <input type="text" name="cep" placeholder="00000-000" required>
        </div>
        <div class="form-control">
            <label>Locais de Atendimento (Bairros separados por vírgula)</label>
            <input type="text" name="locais_atendimento" placeholder="Centro, Jardim América" required>
        </div>
        <div class="form-control">
            <label>E-mail de Acesso</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-control">
            <label>Senha</label>
            <input type="password" name="senha" required>
        </div>
        <button type="submit" class="botao-submit">Registar Provedor</button>
    </form>

    <!-- 3. Formulário Técnico -->
    <form id="form-tecnico" class="formulario-grupo" method="POST">
        <input type="hidden" name="tipo_perfil" value="tecnico">
        <div class="form-control">
            <label>Provedor Empregador</label>
            <select name="id_provedor" required>
                <option value="">Selecione o provedor...</option>
                <?php foreach ($provedoresDisponiveis as$prov): ?>
                    <option value="<?= $prov['id_provedor'] ?>"><?= htmlspecialchars($prov['razao_social']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-control">
            <label>E-mail Institucional</label>
            <input type="email" name="email_institucional" placeholder="tecnico@provedor.com" required>
        </div>
        <div class="form-control">
            <label>Senha</label>
            <input type="password" name="senha" required>
        </div>
        <button type="submit" class="botao-submit">Registar Técnico</button>
    </form>

    <a href="index.php" class="voltar-login">← Iniciar Sessão</a>
</div>

<script>
    function mudarAba(perfil, evento) {
        document.querySelectorAll('.formulario-grupo').forEach(f => f.classList.remove('ativo'));
        document.querySelectorAll('.aba-btn').forEach(b => b.classList.remove('ativo'));
        document.getElementById('form-' + perfil).classList.add('ativo');
        evento.currentTarget.classList.add('ativo');
    }

    async function consultarCep(cep) {
        cep = cep.replace(/\D/g, '');
        let statusDiv = document.getElementById('status-geo');
        let btnSubmit = document.getElementById('btn-registar-cliente');

        if (cep.length !== 8) {
            statusDiv.style.color = '#d97706';
            statusDiv.textContent = "Digite um CEP válido com 8 dígitos.";
            btnSubmit.disabled = true;
            btnSubmit.textContent = "Aguardando CEP válido...";
            return;
        }

        statusDiv.style.color = '#2563eb';
        statusDiv.textContent = " A consultar morada e geolocalização exata...";
        btnSubmit.disabled = true;

        try {
            let res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            let data = await res.json();
            if (data.erro) { 
                statusDiv.style.color = '#dc2626';
                statusDiv.textContent = "CEP não encontrado.";
                return; 
            }

            let bairro = data.bairro || 'Centro';
            let logradouro = data.logradouro || '';
            let localidade = data.localidade || 'São Paulo';
            let uf = data.uf || 'SP';

            document.getElementById('bairro').value = bairro;
            document.getElementById('endereco').value = `${logradouro ? logradouro + ', ' : ''}${bairro}, ${localidade} - ${uf}`;

            let query = logradouro ? `${logradouro}, ${bairro}, ${localidade} - ${uf}` : `${bairro}, ${localidade} - ${uf}`;
            let geoRes = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`);
            let geoData = await geoRes.json();

            if (geoData && geoData.length > 0) {
                document.getElementById('latitude_geografica').value = geoData[0].lat;
                document.getElementById('longitude_geografica').value = geoData[0].lon;
                statusDiv.style.color = '#16a34a';
                statusDiv.textContent = `Morada detetada (${bairro}): Pin pronto para o mapa!`;
                btnSubmit.disabled = false;
                btnSubmit.textContent = "Registar Cliente";
            } else {
                statusDiv.style.color = '#dc2626';
                statusDiv.textContent = " Não foi possível geolocalizar este endereço exato.";
            }
        } catch (e) { 
            statusDiv.style.color = '#dc2626';
            statusDiv.textContent = " Erro de rede ao consultar geolocalização.";
        }
    }
</script>
</body>
</html>