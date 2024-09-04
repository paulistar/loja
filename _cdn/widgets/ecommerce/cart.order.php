<section class='workcontrol_order_details'>
    <h1 style="margin: 0 0 20px 0;"><span>&#10003 Pedido #<?= str_pad($order_id, 7, 0, 0); ?></span></h1>
    <article style="margin: 0 0 20px 0;">
        <?php
        $Read = new Read;
        $Read->FullRead("SELECT user_name, user_lastname FROM " . DB_USERS . " WHERE user_id = :oruser", "oruser={$user_id}");
        $User = $Read->getResult()[0];

        $Read->ExeRead(DB_USERS_ADDR, "WHERE addr_id = :oraddr", "oraddr={$order_addr}");
        $Addr = $Read->getResult()[0];
        ?>
        <h1 class="row">Por <?= "{$User['user_name']} {$User['user_lastname']} dia " . date("d/m/Y \a\s H\hi", strtotime($order_date)); ?></h1>
        <p class="row">Envio para: <?= "{$Addr['addr_street']}, {$Addr['addr_number']}, {$Addr['addr_city']}/{$Addr['addr_state']} - {$Addr['addr_zipcode']}"; ?></p>
    </article>
    <article>
        <h1 class="title">Itens do Pedido:</h1>
        <?php
        $SideTotalCart = 0;
        $SideTotalExtra = 0;
        $SideTotalPrice = 0;
        /* CUSTOM BY ALISSON */
        $Read->FullRead("SELECT o.*, p.pdt_offer_price, p.pdt_offer_start, p.pdt_offer_end, p.pdt_unity FROM " . DB_ORDERS_ITEMS . " o INNER JOIN " . DB_PDT . " p ON o.pdt_id = p.pdt_id WHERE order_id = :orid", "orid={$order_id}");
        //$Read->ExeRead(DB_ORDERS_ITEMS, "WHERE order_id = :orid", "orid={$order_id}");
        if ($Read->getResult()):
            foreach ($Read->getResult() as $SideProduct):
                /* CUSTOM BY ALISSON */
                $Read->FullRead("SELECT stock_inventory, (SELECT attr_size_code FROM " . DB_PDT_ATTR_SIZES . " WHERE size_id = attr_size_id) AS attr_size_code, (SELECT attr_size_title FROM " . DB_PDT_ATTR_SIZES . " WHERE size_id = attr_size_id) AS attr_size_title, (SELECT attr_color_code FROM " . DB_PDT_ATTR_COLORS . " WHERE color_id = attr_color_id) AS attr_color_code, (SELECT attr_color_title FROM " . DB_PDT_ATTR_COLORS . " WHERE color_id = attr_color_id) AS attr_color_title, (SELECT attr_print_code FROM " . DB_PDT_ATTR_PRINTS . " WHERE print_id = attr_print_id) AS attr_print_code, (SELECT attr_print_title FROM " . DB_PDT_ATTR_PRINTS . " WHERE print_id = attr_print_id) AS attr_print_title FROM " . DB_PDT_STOCK . " WHERE stock_id = :id", "id={$SideProduct['stock_id']}");
                //$Read->FullRead("SELECT stock_code FROM " . DB_PDT_STOCK . " WHERE stock_id = :stid", "stid={$SideProduct['stock_id']}");
                /* CUSTOM BY ALISSON */
                $PdtVariation = ($Read->getResult() && !empty($Read->getResult()[0]['attr_color_code']) && empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_print_code']) ? " <span class='wc_cart_tag'>Cor: {$Read->getResult()[0]['attr_color_title']}</span>" : ($Read->getResult() && empty($Read->getResult()[0]['attr_color_code']) && empty($Read->getResult()[0]['attr_print_code']) && !empty($Read->getResult()[0]['attr_size_code']) ? " <span class='wc_cart_tag'>Tamanho: {$Read->getResult()[0]['attr_size_title']}</span>" : ($Read->getResult() && !empty($Read->getResult()[0]['attr_color_code']) && !empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_print_code']) ? " <span class='wc_cart_tag'>Cor: {$Read->getResult()[0]['attr_color_title']}</span> <span class='wc_cart_tag'>Tamanho: {$Read->getResult()[0]['attr_size_title']}</span>" : ($Read->getResult() && !empty($Read->getResult()[0]['attr_print_code']) && empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_color_code']) ? " <span class='wc_cart_tag' title='Estampa: {$Read->getResult()[0]['attr_print_title']}' style='background-image: url(" . BASE . "/uploads/{$Read->getResult()[0]['attr_print_code']});'></span>" : ($Read->getResult() && !empty($Read->getResult()[0]['attr_print_code']) && !empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_color_code']) ? " <span class='wc_cart_tag' title='Estampa: {$Read->getResult()[0]['attr_print_title']}' style='background-image: url(" . BASE . "/uploads/{$Read->getResult()[0]['attr_print_code']});'></span> <span class='wc_cart_tag'>Tamanho: {$Read->getResult()[0]['attr_size_title']}</span>" : '')))));
                //$ProductSize = ($Read->getResult() && $Read->getResult()[0]['stock_code'] != 'default' ? " <b>{$Read->getResult()[0]['stock_code']}</b> " : null);

                echo "<p>";
                $Read->FullRead("SELECT pdt_cover FROM " . DB_PDT . " WHERE pdt_id = :pid", "pid={$SideProduct['pdt_id']}");
                echo "<img title='{$SideProduct['item_name']}' alt='{$SideProduct['item_name']}' src='" . BASE . "/tim.php?src=uploads/{$Read->getResult()[0]['pdt_cover']}&w=" . THUMB_W / 10 . "&h=" . THUMB_H / 10 . "'/>";
                /* CUSTOM BY ALISSON */
                echo "<span>" . Check::Chars($SideProduct['item_name'], 42) . "{$PdtVariation}<br>" . str_replace('.', ',', $SideProduct['item_amount']) . ($SideProduct['pdt_unity'] ? " {$SideProduct['pdt_unity']}" . ($SideProduct['item_amount'] >= 2 ? 's' : '') : '') . " * R$ " . number_format($SideProduct['item_price'], '2', ',', '.') . "</span>";
                //echo "<span>" . Check::Chars($SideProduct['item_name'], 42) . "{$ProductSize}<br>{$SideProduct['item_amount']} * R$ " . number_format($SideProduct['item_price'], '2', ',', '.') . "</span>";

                /* CUSTOM BY ALISSON */
                if ($SideProduct['pdt_offer_price'] && $SideProduct['pdt_offer_start'] <= date('Y-m-d H:i:s') && $SideProduct['pdt_offer_end'] >= date('Y-m-d H:i:s') && strtotime($SideProduct['pdt_offer_end']) - strtotime(date('Y-m-d H:i:s')) <= 259200):
                    echo "<div class='countdown' data-expire='{$SideProduct['pdt_offer_end']}'><div class='countdown_wrapper'><div><span>Oferta<br/><span class='countdown_legend'>Acaba em:</span></span></div><div><span><span class='days'>00</span>&nbsp;&nbsp;:&nbsp;&nbsp;<br/><span class='countdown_legend'>Dia</span></span><span><span class='hours'>00</span>&nbsp;&nbsp;:&nbsp;&nbsp;<br/><span class='countdown_legend'>Hrs</span></span><span><span class='minutes'>00</span>&nbsp;&nbsp;:&nbsp;&nbsp;<br/><span class='countdown_legend'>Min</span></span><span><span class='seconds'>00</span><br/><span class='countdown_legend'>Seg</span></span></div></div></div>";
                endif;

                $SideTotalCart += $SideProduct['item_price'] * $SideProduct['item_amount'];
                echo "</p>";
            endforeach;
        endif;

        $TotalCart = $SideTotalCart;
        ?>
        <div class="workcontrol_order_details_total">
            <div class="wc_cart_total">Sub-total: <b>R$ <span><?= number_format($TotalCart, '2', ',', '.'); ?></span></b></div>
            <?php if ($order_coupon): ?>
                <div class="wc_cart_discount">Desconto: <b><strike>R$ <span><?= number_format($SideTotalCart * ($order_coupon / 100), '2', ',', '.'); ?></span></strike></b></div>
            <?php endif; ?>
            <div class="wc_cart_shiping">Frete: <b>R$ <span><?= number_format($order_shipprice, '2', ',', '.'); ?></span></b></div>
            <div class="wc_cart_price">Total : <b>R$ <span><?= number_format($order_price, '2', ',', '.'); ?></span></b></div>
        </div>
    </article>
</section>