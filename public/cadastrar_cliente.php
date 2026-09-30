<?php
// /public/cadastrar_cliente.php
require_once __DIR__ . '/../src/Cliente/ServicoCliente.php';

$mensagem = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idProvedor = 1; // Provedor padrão para teste do fluxo
    $idPlano = 1;    // Plano padrão temporário

    $sucesso = ServicoCliente::cadastrarClienteVinculado($idProvedor, 
        $idPlano,$_POST['nome_completo'], 
        $_POST['cpf'],$_POST['renda_mensal_bruta'], // 5º Argumento adicionado
        $_POST['cep'],$_POST['email'], 
        $_POST['senha'],$_POST['telefone'], 
        $_POST['bairro'],$_POST['endereco_completo'], 
        $_POST['latitude_geografica'],$_POST['longitude_geografica']
    );

    if ($sucesso) {$mensagem = "Cadastro realizado com sucesso! <a href='index.php'>Faça login</a>";
    } else {
        $mensagem = "Erro ao cadastrar. Verifique os dados.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Cliente</title>
    <link rel="stylesheet" href="css/estilo_base.css">
    <style>
        .container-cad { max-width: 500px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .grupo-form { margin-bottom: 15px; }
        .grupo-form label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        .grupo-form input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container-cad">
        <h2>Criar Conta de Cliente</h2>
        <?php if($mensagem): ?><p style="margin-bottom:15px; color:green;"><?= $mensagem ?></p><?php endif; ?>
        
        <form method="POST">
            <div class="grupo-form">
                <label>Nome Completo</label>
                <input type="text" name="nome_completo" required>
            </div>
            <div class="grupo-form">
                <label>CPF</label>
                <input type="text" name="cpf" required>
            </div>
            <div class="grupo-form">
                <label>Renda Mensal Bruta Familiar (R$)</label>
                <input type="number" step="0.01" name="renda_mensal_bruta" placeholder="Ex: 2500.00" required>
            </div>
            <div class="grupo-form">
                <label>CEP (Busca Automática)</label>
                <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000" required onblur="consultarCep(this.value)">
            </div>
            <div class="grupo-form">
                <label>Endereço Completo (Rua, Número)</label>
                <input type="text" id="endereco_completo" name="endereco_completo" required>
            </div>
            <div class="grupo-form">
                <label>Bairro</label>
                <input type="text" id="bairro" name="bairro" required>
            </div>
            <div class="grupo-form">
                <label>E-mail de Acesso</label>
                <input type="email" name="email" required>
            </div>
            <div class="grupo-form">
                <label>Senha</label>
                <input type="password" name="senha" required>
            </div>
            <div class="grupo-form">
                <label>Telefone / Celular</label>
                <input type="text" name="telefone" required>
            </div>

            <input type="hidden" id="latitude_geografica" name="latitude_geografica">
            <input type="hidden" id="longitude_geografica" name="longitude_geografica">

            <button type="submit" class="botao-primario">Finalizar Cadastro</button>
        </form>
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
                document.getElementById('endereco_completo').value = `${dadosCep.logradouro || ''}, `;

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
                console.error("Erro ao buscar geolocalização:", erro);
                document.getElementById('latitude_geografica').value = "-23.550520";
                document.getElementById('longitude_geografica').value = "-46.633309";
            }
        }
    </script>
</body>
</html>