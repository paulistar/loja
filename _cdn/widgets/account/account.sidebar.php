<?php

echo "<aside class='workcontrol_account_sidebar'>";

echo "<header>";
echo "<h1><i class='fa fa-home'></i> Navegação</h1>";
echo "</header>";

echo "<nav class='workcontrol_account_sidebar_nav'>";
echo "<ul class='workcontrol_account_sidebar_nav'>";
echo "<li><a " . ($AccountAction == 'home' ? 'class="active"' : '') . " href='{$AccountBaseUI}/home#acc' title='Minha Conta'><i class='fa fa-user-circle-o'></i> Minha Conta</a></li>";

if (APP_PRODUCTS):
    echo "<li><a " . ($AccountAction == 'pedidos' || $AccountAction == 'pedido' ? 'class="active"' : '') . " href='{$AccountBaseUI}/pedidos#acc' title='Meus Pedidos'><i class='fa fa-shopping-cart'></i> Meus Pedidos</a></li>";
endif;

echo "<li><a class='logoff' href='{$AccountBaseUI}/sair' title='Desconectar'><i class='fa fa-sign-out'></i> Desconectar!</a></li>";
echo "</ul>";
echo "</nav>";
echo "</aside>";
