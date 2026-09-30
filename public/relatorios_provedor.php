<?php
// /public/relatorios_provedor.php
require_once __DIR__ . '/../src/Autenticacao/ServicoLogin.php';
require_once __DIR__ . '/../config/conexao_banco.php';
require_once __DIR__ . '/includes/layout.php';

ServicoLogin::verificarAcessoRestrito(1);
$idProvedor = $_SESSION['id_provedor'];
$pdo = ConexaoBanco::obterConexao();

// 1. Buscar clientes do provedor (Coordenadas Exatas)
$clientesMapa = [];
try {
    $sqlCli = "SELECT c.nome_completo, c.endereco_completo, c.bairro, c.latitude_geografica, c.longitude_geografica, p.nome_plano 
               FROM clientes c
               INNER JOIN usuarios_sistema u ON c.id_usuario = u.id_usuario
               LEFT JOIN planos_internet p ON c.id_plano_contratado = p.id_plano
               WHERE u.id_provedor = :provedor";
    $cmdCli = $pdo->prepare($sqlCli);
    $cmdCli->execute([':provedor' => $idProvedor]);
    $clientesMapa = $cmdCli->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $clientesMapa = [];
}

// 2. Buscar pontos de Wi-Fi Comunitario registados via comunitario.php
$wifiMapa = [];
try {
    // Tenta primeiro o padrao de nomes de colunas comum gerado pelo ServicoComunitario
    $sqlWifi = "SELECT nome_local, bairro, endereco, latitude_geografica as latitude, longitude_geografica as longitude 
                FROM pontos_wifi_comunitario 
                WHERE id_provedor = :provedor";
    $cmdWifi = $pdo->prepare($sqlWifi);
    $cmdWifi->execute([':provedor' => $idProvedor]);
    $wifiMapa = $cmdWifi->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e1) {
    try {
        // Tenta nomes alternativos caso a estrutura da tabela seja ligeiramente diferente
        $sqlWifi2 = "SELECT nome_local, bairro, endereco_completo as endereco, latitude, longitude 
                     FROM wifi_comunitario 
                     WHERE id_provedor = :provedor";
        $cmdWifi2 = $pdo->prepare($sqlWifi2);
        $cmdWifi2->execute([':provedor' => $idProvedor]);
        $wifiMapa = $cmdWifi2->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e2) {
        $wifiMapa = [];
    }
}

ob_start();
?>
<h2>Relatorios e Cobertura da Operadora</h2>
<p style="color: #64748b; margin-bottom: 20px;">Visualizacao geoespacial exata de clientes e hotspots comunitarios na sua rede.</p>

<!-- Mapa de Cobertura -->
<div class="bloco-secao" style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 20px;">
    <h3>Mapa de Cobertura Geografica</h3>
    <div id="mapa-cobertura" style="width: 100%; height: 600px; border-radius: 8px; border: 1px solid #cbd5e1; margin-top: 15px;"></div>
</div>

<!-- Leaflet CSS & JS CDN -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    var mapa = L.map('mapa-cobertura').setView([-23.550520, -46.633309], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapa);

    var bounds = [];

    // Adiciona marcadores para os Clientes (nas coordenadas exatas cadastradas)
    <?php foreach ($clientesMapa as $cli): 
        $lat = (!empty($cli['latitude_geografica']) && floatval($cli['latitude_geografica']) != 0) ? $cli['latitude_geografica'] : -23.550520;
        $lng = (!empty($cli['longitude_geografica']) && floatval($cli['longitude_geografica']) != 0) ? $cli['longitude_geografica'] : -46.633309;
    ?>
        var markerCli = L.marker([<?= $lat ?>, <?= $lng ?>]).addTo(mapa)
          .bindPopup("<b>Cliente:</b> <?= htmlspecialchars($cli['nome_completo'] ?? 'Cliente') ?><br><b>Morada:</b> <?= htmlspecialchars($cli['endereco_completo'] ?? 'N/D') ?><br><b>Plano:</b> <?= htmlspecialchars($cli['nome_plano'] ?? 'N/D') ?>");
        bounds.push([<?= $lat ?>, <?= $lng ?>]);
    <?php endforeach; ?>

    // Adiciona circulos verdes distintos para o Wi-Fi Comunitario
    <?php foreach ($wifiMapa as $wifi): 
        $wLat = (!empty($wifi['latitude']) && floatval($wifi['latitude']) != 0) ? $wifi['latitude'] : -23.550520;
        $wLng = (!empty($wifi['longitude']) && floatval($wifi['longitude']) != 0) ? $wifi['longitude'] : -46.633309;
        $wNome = !empty($wifi['nome_local']) ? $wifi['nome_local'] : 'Ponto Wi-Fi';
        $wBairro = !empty($wifi['bairro']) ? $wifi['bairro'] : 'N/D';
    ?>
        var markerWifi = L.circleMarker([<?= $wLat ?>, <?= $wLng ?>], {
            radius: 10,
            fillColor: "#10b981",
            color: "#059669",
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(mapa)
          .bindPopup("<b>Ponto Wi-Fi Comunitario:</b> <?= htmlspecialchars($wNome) ?><br><b>Bairro:</b> <?= htmlspecialchars($wBairro) ?>");
        bounds.push([<?= $wLat ?>, <?= $wLng ?>]);
    <?php endforeach; ?>

    // Enquadra a tela para mostrar todos os pontos perfeitamente
    if (bounds.length > 0) {
        mapa.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
    }
</script>

<?php
$conteudoHTML = ob_get_clean();
renderizarLayout("Relatorios e Cobertura", $conteudoHTML, 1, 'relatorios_provedor');
?>