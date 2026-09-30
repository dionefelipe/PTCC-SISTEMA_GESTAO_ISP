<?php
// /public/includes/layout.php
function renderizarLayout($tituloPagina, $conteudoHTML, $perfilTipo, $paginaAtiva) {
    $linksMenu = '';
    
    if ($perfilTipo == 1) { // Provedor (Gestor)
        $linksMenu = '
            <a href="dashboard_provedor.php" class="' . ($paginaAtiva == 'dashboard' ? 'ativo' : '') . '">Dashboard</a>
            <a href="planos.php" class="' . ($paginaAtiva == 'planos' ? 'ativo' : '') . '">Planos</a>
            <a href="tecnicos.php" class="' . ($paginaAtiva == 'tecnicos' ? 'ativo' : '') . '">Técnicos</a>
            <a href="comunitario.php" class="' . ($paginaAtiva == 'comunitario' ? 'ativo' : '') . '">Wi-Fi Comunitário</a>
            <a href="relatorios_provedor.php" class="' . ($paginaAtiva == 'relatorios' ? 'ativo' : '') . '">Relatórios & Mapa</a>
        ';
    } elseif ($perfilTipo == 2) { // Técnico
        $linksMenu = '
            <a href="dashboard_tecnico.php" class="' . ($paginaAtiva == 'rota' ? 'ativo' : '') . '">Rota do Dia</a>
        ';
    } elseif ($perfilTipo == 3) { // Cliente
        $linksMenu = '
            <a href="dashboard_cliente.php" class="' . ($paginaAtiva == 'chamados' ? 'ativo' : '') . '">Acompanhar Chamados</a>
            <a href="abrir_chamado.php" class="' . ($paginaAtiva == 'abrir_chamado' ? 'ativo' : '') . '">Abrir Chamado</a>
            <a href="alterar_plano.php" class="' . ($paginaAtiva == 'alterar_plano' ? 'ativo' : '') . '">Mudança de Planos</a>
        ';
    }
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?> - SocialConecta ISP</title>
    <link rel="stylesheet" href="css/estilo_base.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <div class="layout-app">
        <nav class="menu-lateral">
            <h3>SocialConecta</h3>
            <?= $linksMenu ?>
            <a href="logout.php" class="sair">Terminar Sessão</a>
        </nav>
        <main class="conteudo-principal">
            <?= $conteudoHTML ?>
        </main>
    </div>
</body>
</html>
<?php
}
?>