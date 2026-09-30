<?php

declare(strict_types=1);

$pageTitle = 'Chamados';
$activeNav = 'chamados';
$headerTitle = 'Chamados técnicos';
require_once __DIR__ . '/../includes/layout_head.php';
require_once __DIR__ . '/../sidebar.php';
?>
<main class="main-wrapper">
    <?php require __DIR__ . '/../includes/page_header.php'; ?>
    <section class="dashboard-content" id="pageRoot">
        <p class="muted">Carregando chamados...</p>
    </section>
</main>
<?php
$extraJs = [$BASE . 'assets/js/pages/chamados.js'];
require __DIR__ . '/../includes/layout_foot.php';
