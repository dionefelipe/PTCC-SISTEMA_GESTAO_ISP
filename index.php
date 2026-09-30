<?php

declare(strict_types=1);

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
$headerTitle = 'Painel de desempenho';
require_once __DIR__ . '/includes/layout_head.php';
require_once __DIR__ . '/sidebar.php';
?>
<main class="main-wrapper">
    <?php require __DIR__ . '/includes/page_header.php'; ?>
    <section class="dashboard-content" id="dashboardRoot">
        <p class="muted">Carregando painel...</p>
    </section>
</main>
<?php
$extraJs = [($BASE ?? '') . 'assets/js/pages/dashboard.js'];
require __DIR__ . '/includes/layout_foot.php';
