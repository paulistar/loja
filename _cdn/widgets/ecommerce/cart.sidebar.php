<?php
if (!empty($_SESSION['wc_order'])):
    $OderDetail = $_SESSION['wc_order'];
    $OderCupom = (!empty($_SESSION['wc_cupom']) ? $_SESSION['wc_cupom'] : null);
    ?><section class='workcontrol_order_details'>
        <h1><span>Resumo do pedido:</span></h1>
        <?php
        $SideTotalCart = 0;
        foreach ($OderDetail as $SideItemId => $SideItemAmount):
            $Read->ExeRead(DB_PDT, "WHERE pdt_id = (SELECT pdt_id FROM " . DB_PDT_STOCK . " WHERE stock_id = :id)", "id={$SideItemId}");
            if ($Read->getResult()[0]):
                $SideProduct = $Read->getResult()[0];
                $SideProductPrice = ($SideProduct['pdt_offer_price'] && $SideProduct['pdt_offer_start'] <= date('Y-m-d H:i:s') && $SideProduct['pdt_offer_end'] >= date('Y-m-d H:i:s') ? $SideProduct['pdt_offer_price'] : $SideProduct['pdt_price']);

                /* CUSTOM BY ALISSON */
                $Read->FullRead("SELECT (SELECT attr_size_code FROM " . DB_PDT_ATTR_SIZES . " WHERE size_id = attr_size_id) AS attr_size_code, (SELECT attr_size_title FROM " . DB_PDT_ATTR_SIZES . " WHERE size_id = attr_size_id) AS attr_size_title, (SELECT attr_color_code FROM " . DB_PDT_ATTR_COLORS . " WHERE color_id = attr_color_id) AS attr_color_code, (SELECT attr_color_title FROM " . DB_PDT_ATTR_COLORS . " WHERE color_id = attr_color_id) AS attr_color_title, (SELECT attr_print_code FROM " . DB_PDT_ATTR_PRINTS . " WHERE print_id = attr_print_id) AS attr_print_code, (SELECT attr_print_title FROM " . DB_PDT_ATTR_PRINTS . " WHERE print_id = attr_print_id) AS attr_print_title FROM " . DB_PDT_STOCK . " WHERE stock_id = :id", "id={$SideItemId}");
                //$Read->FullRead("SELECT stock_code FROM " . DB_PDT_STOCK . " WHERE stock_id = :stid", "stid={$SideItemId}");
                /* CUSTOM BY ALISSON */
                $PdtVariation = ($Read->getResult() && !empty($Read->getResult()[0]['attr_color_code']) && empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_print_code']) ? " <span class='wc_cart_tag'>Cor: {$Read->getResult()[0]['attr_color_title']}</span>" : ($Read->getResult() && empty($Read->getResult()[0]['attr_color_code']) && empty($Read->getResult()[0]['attr_print_code']) && !empty($Read->getResult()[0]['attr_size_code']) ? " <span class='wc_cart_tag'>Tamanho: {$Read->getResult()[0]['attr_size_title']}</span>" : ($Read->getResult() && !empty($Read->getResult()[0]['attr_color_code']) && !empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_print_code']) ? " <span class='wc_cart_tag'>Cor: {$Read->getResult()[0]['attr_color_title']}</span> <span class='wc_cart_tag'>Tamanho: {$Read->getResult()[0]['attr_size_title']}</span>" : ($Read->getResult() && !empty($Read->getResult()[0]['attr_print_code']) && empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_color_code']) ? " <span class='wc_cart_tag' title='Estampa: {$Read->getResult()[0]['attr_print_title']}' style='background-image: url(" . BASE . "/uploads/{$Read->getResult()[0]['attr_print_code']});'></span>" : ($Read->getResult() && !empty($Read->getResult()[0]['attr_print_code']) && !empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_color_code']) ? " <span class='wc_cart_tag' title='Estampa: {$Read->getResult()[0]['attr_print_title']}' style='background-image: url(" . BASE . "/uploads/{$Read->getResult()[0]['attr_print_code']});'></span> <span class='wc_cart_tag'>Tamanho: {$Read->getResult()[0]['attr_size_title']}</span>" : '')))));
                //$ProductSize = ($Read->getResult() && $Read->getResult()[0]['stock_code'] != 'default' ? " <b>{$Read->getResult()[0]['stock_code']}</b> " : null);

                echo "<p>";
                echo "<img title='{$SideProduct['pdt_title']}' alt='{$SideProduct['pdt_title']}' src='" . BASE . "/tim.php?src=uploads/{$SideProduct['pdt_cover']}&w=" . THUMB_W / 10 . "&h=" . THUMB_H / 10 . "'/>";
                /* CUSTOM BY ALISSON */
                echo "<span>" . Check::Chars($SideProduct['pdt_title'], 42) . "{$PdtVariation}<br>" . str_replace('.', ',', $SideItemAmount) . ($SideProduct['pdt_unity'] ? " {$SideProduct['pdt_unity']}" . ($SideItemAmount >= 2 ? 's' : '') : '') . " * R$ " . number_format($SideProductPrice, '2', ',', '.') . "</span>";
                //echo "<span>" . Check::Chars($SideProduct['pdt_title'], 42) . "{$ProductSize}<br>{$SideItemAmount} * R$ " . number_format($SideProductPrice, '2', ',', '.') . "</span>";

                /* CUSTOM BY ALISSON */
                if ($SideProduct['pdt_offer_price'] && $SideProduct['pdt_offer_start'] <= date('Y-m-d H:i:s') && $SideProduct['pdt_offer_end'] >= date('Y-m-d H:i:s') && strtotime($SideProduct['pdt_offer_end']) - strtotime(date('Y-m-d H:i:s')) <= 259200):
                    echo "<div class='countdown' data-expire='{$SideProduct['pdt_offer_end']}'><div class='countdown_wrapper'><div><span>Oferta<br/><span class='countdown_legend'>Acaba em:</span></span></div><div><span><span class='days'>00</span>&nbsp;&nbsp;:&nbsp;&nbsp;<br/><span class='countdown_legend'>Dia</span></span><span><span class='hours'>00</span>&nbsp;&nbsp;:&nbsp;&nbsp;<br/><span class='countdown_legend'>Hrs</span></span><span><span class='minutes'>00</span>&nbsp;&nbsp;:&nbsp;&nbsp;<br/><span class='countdown_legend'>Min</span></span><span><span class='seconds'>00</span><br/><span class='countdown_legend'>Seg</span></span></div></div></div>";
                endif;

                echo "</p>";
                $SideTotalCart += $SideProductPrice * $SideItemAmount;
            endif;
        endforeach;

        $SideTotalPrice = (!empty($_SESSION['wc_cupom']) ? $SideTotalCart * ((100 - $_SESSION['wc_cupom']) / 100) : $SideTotalCart);
        ?>
        <div class="workcontrol_order_details_total">
            <div class="wc_cart_total">Sub-total: <b>R$ <span><?= number_format($SideTotalCart, '2', ',', '.'); ?></span></b></div>
            <?php if ($OderCupom): ?>
                <div class="wc_cart_discount">Desconto: <b><strike>R$ <span><?= number_format($SideTotalCart * ($OderCupom / 100), '2', ',', '.'); ?></span></strike></b></div>
            <?php endif; ?>
            <div class="wc_cart_shiping">Frete: <b>R$ <span><?= number_format((!empty($_SESSION['wc_shipment']['wc_shipprice']) ? $_SESSION['wc_shipment']['wc_shipprice'] : 0), '2', ',', '.'); ?></span></b></div>
            <div class="wc_cart_price">Total: <b>R$ <span><?= number_format((!empty($_SESSION['wc_shipment']['wc_shipprice']) ? $SideTotalPrice + $_SESSION['wc_shipment']['wc_shipprice'] : $SideTotalPrice), '2', ',', '.'); ?></span></b></div>
        </div>
    </section><?php


endif;