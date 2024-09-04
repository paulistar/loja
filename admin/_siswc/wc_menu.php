<?php if ($Admin['user_level'] >= LEVEL_WC_SHIPPING_QUOTE): ?>
    <li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'cotacao/') ? 'dashboard_nav_menu_active' : ''; ?>">
        <a class="icon-truck" title="Cotação de frete" href="dashboard.php?wc=cotacao/home">Cotação de frete</a>
    </li>
<?php endif; ?>