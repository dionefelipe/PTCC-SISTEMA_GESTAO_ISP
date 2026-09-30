<?php

declare(strict_types=1);

$pageTitle = 'Áreas atendidas';
$activeNav = 'areas';
$headerTitle = 'Acompanhar áreas atendidas';
$extraCss = ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'];
require_once __DIR__ . '/../includes/layout_head.php';
require_once __DIR__ . '/../sidebar.php';
?>
<main class="main-wrapper">
    <?php require __DIR__ . '/../includes/page_header.php'; ?>
    <section class="dashboard-content" id="pageRoot">
        <p class="muted">Carregando mapa...</p>
    </section>
</main>
<?php
$extraJs = [
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
    $BASE . 'assets/js/pages/areas.js',
];
require __DIR__ . '/../includes/layout_foot.php';
