<?php

if (empty($_SESSION['userLogin']) || !APP_PRODUCTS):
    die('<h1 style="padding: 50px 0; text-align: center; font-size: 3em; font-weight: 300; color: #C63D3A">Acesso Negado!</h1>');
endif;


$Read = new Read;
$OrId = filter_var($URL[2], FILTER_VALIDATE_INT);
if (!$OrId):
    echo "<div style='margin: 20px 20px 0 20px'>";
    echo "<div class='trigger trigger_alert' style='margin: 0;'>Olá {$user_name}, favor selecione um pedido para ver os detalhes!</div>";
    echo "</div>";
    require 'pedidos.wc.php';
else:
    if (isset($URL[3]) && !empty($URL[3])):
        $Correios = new TrackerParser();
        $Correios->setCode($URL[3]);
        echo "<h3>Rastreamento do Pedido #" . str_pad($OrId, 7, 0, 0) . "</h3>";
        if (!$Correios->getEventsList()):
            echo Erro("Nenhum resultado, tente novamente mais tarde!", E_USER_NOTICE);
        else:
            foreach ($Correios->getEventsList() AS $item):
                ?>
                <div class="track_item">
                    <div class="track_item_data">
                        <p><?= $item->date; ?></p>
                        <p><?= $item->hour; ?></p>
                        <p><?= $item->location; ?></p>
                    </div>
                    <div class="track_item_result">
                        <p><strong><?= $item->label; ?></strong></p>
                        <p><?= $item->description; ?></p>
                    </div>
                </div>
            <?php
            endforeach;
        endif;
    else:
        $Read->ExeRead(DB_ORDERS, "WHERE order_id = :or AND user_id = :us", "or={$OrId}&us={$user_id}");
        echo "<div class='workcontrol_account_view'>";
        if (!$Read->getResult()):
            echo "<div class='trigger trigger_alert' style='margin: 0;'><b>Caro(a) {$user_name},</b><p>Você tentou acessar um pedido que não existe ou não está disponível para ser acessado por sua conta {$user_email}.</p><p><a href='{$AccountBaseUI}/pedidos#acc' title='Meus Pedidos'>Clique aqui para acessar seus pedidos!</a></p></div>";
        else:
            extract($Read->getResult()[0]);
            $order_installments = (empty($order_installments) ? 1 : $order_installments);
            $order_installment = (empty($order_installment) ? $order_price : $order_installment);

            $ShipmentUrl = ($order_shipcode > 4000 ? 'http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI=' : ECOMMERCE_SHIPMENT_COMPANY_LINK);
            echo "<p class='wc_account_title'><span>Detalhes do pedido:</span><p>";
            echo "<div class='workcontrol_account_home'>";
            echo "<p><b>Pedido: </b>" . str_pad($order_id, 7, 0, 0) . "</p>";
            echo "<p><b>Data: </b>" . date("d/m/Y H\hi", strtotime($order_date)) . "</p>";
            echo "<p><b>Valor: </b>R$ " . number_format($order_installments * $order_installment, '2', ',',
                    '.') . "</p>";
            echo "<p><b>Desconto: </b>" . ($order_coupon ? $order_coupon : 0) . "%</p>";
            echo "<p><b>Pagamento: </b>" . getOrderPayment($order_payment) . "</p>";
            echo "<p><b>Status: </b>" . getOrderStatus($order_status) . "</p>";
            echo "<p><b>Postado dia: </b>" . ($order_shipment ? date('d/m/Y',
                    strtotime($order_shipment)) : 'Aguardando envio!') . " " . ($order_tracking && $order_tracking != 1 ? "- <a href='" . BASE . "/conta/pedido/" . $OrId . "/" . $order_tracking . "'>Acompanhar Envio</a>" : "") . "</p>";
            echo "<p><b>Nota fiscal: </b>" . ($order_nfepdf ? "<a class='font_blue' target='_blank' href='" . BASE . "/uploads/{$order_nfepdf}' title='Nota Fiscal'>NFE</a>" : 'Aguardando Emissão') . ($order_nfexml ? ", <a class='font_blue' target='_blank' href='" . BASE . "/uploads/{$order_nfexml}' title='XML da nota'>XML</a>" : null) . "</p>";

            $Read->ExeRead(DB_USERS_ADDR, "WHERE user_id = :usr AND addr_id = :addr",
                "usr={$user_id}&addr={$order_addr}");
            if ($Read->getResult()):
                extract($Read->getResult()[0]);
                echo "<p style='width: 100%;'><b>Endereço: </b>{$addr_name}<br>{$addr_street}, {$addr_number}<br>B. {$addr_district}, {$addr_city}/{$addr_state}<br>{$addr_zipcode} - {$addr_country}</p>";
            endif;
            echo "</div>";

            if ($order_status == '3'):
                echo "<div style='display: block; text-align: right; margin: 20px 0 40px 0;'><a class='btn btn_blue' title='Pagar agora' href='" . BASE . "/pedido/pagamento/" . base64_encode($order_id) . "#cart' target='_blanck'>PAGAR AGORA!</a></div>";
            elseif ($order_status != 2 && $order_status != 1 && $order_status != 6 && date('Y-m-d H:i:s',
                    strtotime($order_date . "+" . E_ORDER_DAYS . "days")) > date('Y-m-d H:i:s')):
                echo "<div style='text-align: right;'>";
                if ($order_billet):
                    echo "<div style='display: inline-block; text-align: right; margin: 20px 0 0 0;'><a class='btn btn_blue' title='Imprimir boleto' href='{$order_billet}' target='_blank'>IMPRIMIR BOLETO!</a></div>";
                endif;
                echo "<div style='display: inline-block; text-align: right; margin: 20px 0 40px 20px;'><a class='btn btn_blue' title='Pagar agora' href='" . BASE . "/pedido/pagamento/" . base64_encode($order_id) . "#cart' target='_blanck'>PAGAR COM CARTÃO!</a></div>";
                echo "</div>";
            endif;

            echo "<div class='workcontrol_order_completed_card m_top'><p class='product'>Produto</p><p>Preço</p><p>Quant.</p><p>Total</p></div>";
            $SideTotalCart = 0;
            $SideTotalExtra = 0;
            $SideTotalPrice = 0;
            $Read->ExeRead(DB_ORDERS_ITEMS, "WHERE order_id = :orid", "orid={$order_id}");
            if ($Read->getResult()):
                foreach ($Read->getResult() as $SideProduct):
                    if ($SideProduct['pdt_id']):
                        $Read->FullRead("SELECT (SELECT attr_size_code FROM " . DB_PDT_ATTR_SIZES . " WHERE size_id = attr_size_id) AS attr_size_code, (SELECT attr_size_title FROM " . DB_PDT_ATTR_SIZES . " WHERE size_id = attr_size_id) AS attr_size_title, (SELECT attr_color_code FROM " . DB_PDT_ATTR_COLORS . " WHERE color_id = attr_color_id) AS attr_color_code, (SELECT attr_color_title FROM " . DB_PDT_ATTR_COLORS . " WHERE color_id = attr_color_id) AS attr_color_title, (SELECT attr_print_code FROM " . DB_PDT_ATTR_PRINTS . " WHERE print_id = attr_print_id) AS attr_print_code, (SELECT attr_print_title FROM " . DB_PDT_ATTR_PRINTS . " WHERE print_id = attr_print_id) AS attr_print_title FROM " . DB_PDT_STOCK . " WHERE stock_id = :id",
                            "id={$SideProduct['stock_id']}");
                        $PdtVariation = ($Read->getResult() && !empty($Read->getResult()[0]['attr_color_code']) && empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_print_code']) ? " <span class='wc_cart_tag'>Cor: {$Read->getResult()[0]['attr_color_title']}</span>" : ($Read->getResult() && empty($Read->getResult()[0]['attr_color_code']) && empty($Read->getResult()[0]['attr_print_code']) && !empty($Read->getResult()[0]['attr_size_code']) ? " <span class='wc_cart_tag'>Tamanho: {$Read->getResult()[0]['attr_size_title']}</span>" : ($Read->getResult() && !empty($Read->getResult()[0]['attr_color_code']) && !empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_print_code']) ? " <span class='wc_cart_tag'>Cor: {$Read->getResult()[0]['attr_color_title']}</span> <span class='wc_cart_tag'>Tamanho: {$Read->getResult()[0]['attr_size_title']}</span>" : ($Read->getResult() && !empty($Read->getResult()[0]['attr_print_code']) && empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_color_code']) ? " <span class='wc_cart_tag' title='Estampa: {$Read->getResult()[0]['attr_print_title']}' style='background-image: url(" . BASE . "/uploads/{$Read->getResult()[0]['attr_print_code']});'></span>" : ($Read->getResult() && !empty($Read->getResult()[0]['attr_print_code']) && !empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_color_code']) ? " <span class='wc_cart_tag' title='Estampa: {$Read->getResult()[0]['attr_print_title']}' style='background-image: url(" . BASE . "/uploads/{$Read->getResult()[0]['attr_print_code']});'></span> <span class='wc_cart_tag'>Tamanho: {$Read->getResult()[0]['attr_size_title']}</span>" : '')))));
                        $Read->FullRead("SELECT pdt_name, pdt_cover FROM " . DB_PDT . " WHERE pdt_id = :pid",
                            "pid={$SideProduct['pdt_id']}");

                        echo "<div class='workcontrol_order_completed_card items'>";
                        echo "<p class='product'><img title='{$SideProduct['item_name']}' alt='{$SideProduct['item_name']}' src='" . BASE . "/tim.php?src=uploads/{$Read->getResult()[0]['pdt_cover']}&w=" . THUMB_W / 5 . "&h=" . THUMB_H / 5 . "'/><span><a target='_blank' href='" . BASE . "/produto/{$Read->getResult()[0]['pdt_name']}' title='Ver {$SideProduct['item_name']} no site'>" . Check::Chars($SideProduct['item_name'],
                                42) . "</a>{$PdtVariation}</span></p>";
                        echo "<p>R$ " . number_format($SideProduct['item_price'], '2', ',', '.') . "</p>";
                        echo "<p>{$SideProduct['item_amount']}</p>";
                        echo "<p>R$ " . number_format($SideProduct['item_price'] * $SideProduct['item_amount'], '2',
                                ',', '.') . "</p>";
                        $SideTotalCart += $SideProduct['item_price'] * $SideProduct['item_amount'];
                        echo "</div>";
                    else:
                        $SideTotalExtra += $SideProduct['item_price'] * $SideProduct['item_amount'];
                    endif;
                endforeach;
            endif;

            $TotalCart = $SideTotalCart;
            $TotalExtra = $SideTotalExtra;
            echo "<div class='workcontrol_order_completed_card total'>";
            echo "<div class='wc_cart_total'>Sub-total: <b>R$ <span>" . number_format($TotalCart, '2', ',',
                    '.') . "</span></b></div>";
            if ($order_coupon):
                echo "<div class='wc_cart_discount'>Desconto: <b><strike>R$ <span>" . number_format($SideTotalCart * ($order_coupon / 100),
                        '2', ',', '.') . "</span></strike></b></div>";
            endif;
            echo "<div>Frete: <b>R$ <span>" . number_format($order_shipprice, '2', ',', '.') . "</span></b></div>";
            if ($order_installments > 1):
                echo "<div>Total : <b>R$ <span>" . number_format($order_price, '2', ',', '.') . "</span></b></div>";
                echo "<div class='wc_cart_price'><small><sup>{$order_installments}x</sup> R$ {$order_installment}</small>:<b>R$ <span>" . number_format($order_installments * $order_installment,
                        '2', ',', '.') . "</span></b></div>";
            else:
                echo "<div class='wc_cart_price'>Total : <b>R$ <span>" . number_format($order_price, '2', ',',
                        '.') . "</span></b></div>";
            endif;
            echo "</div>";
        endif;
        echo "</div>";
    endif;
endif;

