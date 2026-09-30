<?php
// /public/contratar.php
require_once __DIR__ . '/../src/Cliente/ServicoCliente.php';

$planosDisponiveis = [];
$bairroBuscado = '';

if (isset($_GET['bairro'])) {
    $bairroBuscado = trim($_GET['bairro']);
    $planosDisponiveis = ServicoCliente::buscarPlanosPorBairro($bairroBuscado);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Assinar Internet</title>
    <link rel="stylesheet" href="css/estilo_base.css">
    <style>
        .container-busca { max-width: 600px; margin: 50px auto; text-align: center; background: #fff; padding: 30px; border-radius: 8px; }
        .card-plano { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; text-align: left; }
    </style>
</head>
<body>
    <div class="container-busca">
        <h2>Verificar Cobertura</h2>
        <form method="GET" action="contratar.php" style="display: flex; gap: 10px; margin-top: 20px;">
            <input type="text" name="bairro" placeholder="Digite seu Bairro" required style="flex: 1; padding: 10px;">
            <button type="submit" class="botao-primario" style="width: auto;">Buscar</button>
        </form>

        <?php if ($bairroBuscado): ?>
            <h3 style="margin-top: 30px;">Resultados para "<?= htmlspecialchars($bairroBuscado) ?>"</h3>
            
            <?php if (empty($planosDisponiveis)): ?>
                <p style="color: red; margin-top: 10px;">Desculpe, ainda não temos cobertura neste bairro.</p>
            <?php else: ?>
                <?php foreach ($planosDisponiveis as $plano): ?>
                    <div class="card-plano">
                        <h4><?= htmlspecialchars($plano['nome_plano']) ?> - <?= $plano['velocidade_megas'] ?> MB</h4>
                        <p><strong>Provedor:</strong> <?= htmlspecialchars($plano['razao_social']) ?></p>
                        <p><strong>Valor:</strong> R$ <?= number_format($plano['valor_mensal'], 2, ',', '.') ?></p>
                        <button class="botao-primario" style="margin-top: 10px; background: #28a745;">Contratar Plano</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>