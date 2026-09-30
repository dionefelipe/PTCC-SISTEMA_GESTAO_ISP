<?php

declare(strict_types=1);

$pageTitle = 'Planos';
$activeNav = 'planos';
$headerTitle = 'Planos de internet';
require_once __DIR__ . '/../includes/layout_head.php';
require_once __DIR__ . '/../sidebar.php';
?>
<main class="main-wrapper">
    <?php require __DIR__ . '/../includes/page_header.php'; ?>
    <section class="dashboard-content" id="pageRoot">
        <p class="muted">Carregando planos...</p>
    </section>
</main>
<?php
$extraJs = [$BASE . 'assets/js/pages/planos.js'];
require __DIR__ . '/../includes/layout_foot.php';
