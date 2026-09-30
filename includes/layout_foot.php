<?php

declare(strict_types=1);

require_once __DIR__ . '/paths.php';
?>
    <script src="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>assets/js/app-core.js"></script>
    <?php if (!empty($extraJs)): ?>
        <?php foreach ((array) $extraJs as $jsSrc): ?>
            <script src="<?= htmlspecialchars($jsSrc, ENT_QUOTES, 'UTF-8') ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
