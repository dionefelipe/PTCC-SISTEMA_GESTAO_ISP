<?php

declare(strict_types=1);

$headerTitle = $headerTitle ?? 'Gestão para provedores e inclusão digital';
?>
<header class="top-header">
    <button
        type="button"
        class="mobile-toggle"
        id="mobileToggleBtn"
        aria-label="Abrir menu"
        aria-expanded="false">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="page-title"><?= htmlspecialchars($headerTitle, ENT_QUOTES, 'UTF-8') ?></div>

    <div class="header-actions">
        <span id="perfilUsuario" class="perfil-usuario"></span>
        <button type="button" id="logoutBtn" class="btn-logout" title="Sair do sistema">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Sair</span>
        </button>
    </div>
</header>
