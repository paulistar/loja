<?php

header('Content-Type: text/html; charset=UTF-8');

$json = file_get_contents('php://input');
$response = json_decode($json, true);

if (!empty($response)):
    require '../../../../_app/Config.inc.php';

    $Read = new Read;
    $Update = new Update;
    $Email = new Email;

    $orderCode = $response['resource']['order']['id'];
    $paymentStatus = $response['resource']['order']['status'];
    $transactionFees = number_format($response['resource']['order']['amount']['fees'] / 100, 2, '.', ',');

    $Read->ExeRead(DB_ORDERS, 'WHERE order_code = :code', "code={$orderCode}");
    if ($Read->getResult()):
        extract($Read->getResult()[0]);

        $Read->ExeRead(DB_USERS, 'WHERE user_id = :id', "id={$user_id}");
        $client = $Read->getResult()[0];

        if ($paymentStatus == 'WAITING'):
            /*
             * AGUARDANDO PAGAMENTO
             */
            $dataOrder = ['order_status' => 4, 'order_free' => $transactionFees, 'order_update' => date('Y-m-d H:i:s')];
            $Update->ExeUpdate(DB_ORDERS, $dataOrder, 'WHERE order_id = :id', "id={$order_id}");

            $bodyMail = "<p style='font-size: 1.2em;'>Caro(a) {$client['user_name']},</p>";
            $bodyMail .= "<p>Primeiramente gostaríamos de agradecer por você escolher a nossa loja para adquirir seus produtos.</p>";
            $bodyMail .= "<p>Seu pedido #" . str_pad($order_id, 7, 0, 0) . " foi concluído com sucesso!</p>";
            $bodyMail .= "<p>E neste momento estamos apenas <b>aguardando a confirmação do pagamento</b> para envia-lo a você!</p>";
            $bodyMail .= "<p style='font-size: 1.4em;'>Detalhes do Pedido:</p>";
            $bodyMail .= "<p>Pedido: <a href='" . BASE . "/conta/pedido/{$order_id}' title='Ver pedido' target='_blank'>#" . str_pad($order_id, 7, 0, STR_PAD_LEFT) . "</a><br>Data: " . date('d/m/Y H\hi', strtotime($order_date)) . "<br>Valor: R$ " . number_format($order_price, '2', ',', '.') . "<br>Método de Pagamento: " . getOrderPayment($order_payment) . ($order_billet ? " - <a title='Imprimir Boleto!' href='{$order_billet}'>Imprimir Boleto!</a>" : "") . "</p>";
            $bodyMail .= "<hr><table style='width: 100%'><tr><td>STATUS:</td><td style='color: #00AD8E; text-align: center;'>✓ Aguardando Pagamento</td><td style='color: #888888; text-align: center;'>» Processando</td><td style='color: #888888; text-align: right;'>✓ Concluído</td></tr></table><hr>";

            $Read->ExeRead(DB_ORDERS_ITEMS, 'WHERE order_id = :id', "id={$order_id}");
            if ($Read->getResult()):
                $i = 0;
                $itemsPrice = 0;
                $itemsAmount = 0;

                $bodyMail .= "<p style='font-size: 1.4em;'>Produtos:</p>";
                $bodyMail .= "<p>Abaixo você pode conferir os detalhes, quantidades e valores de cada produto adquirido em seu pedido. Confira:</p>";
                $bodyMail .= "<table style='width: 100%' border='0' cellspacing='0' cellpadding='0'>";

                foreach ($Read->getResult() as $item):
                    $Read->FullRead("SELECT stock_code, stock_code_title, stock_color, stock_color_title FROM " . DB_PDT_STOCK . " WHERE stock_id = :stid", "stid={$item['stock_id']}");
                    $pdtVariation = ($Read->getResult() && $Read->getResult()[0]['stock_color'] != 'default' && $Read->getResult()[0]['stock_code'] == 'default' ? " ({$Read->getResult()[0]['stock_color_title']})" : ($Read->getResult() && $Read->getResult()[0]['stock_color'] == 'default' && $Read->getResult()[0]['stock_code'] != 'default' ? " ({$Read->getResult()[0]['stock_code_title']})" : ($Read->getResult() && $Read->getResult()[0]['stock_color'] != 'default' && $Read->getResult()[0]['stock_code'] != 'default' ? " ({$Read->getResult()[0]['stock_color_title']} ({$Read->getResult()[0]['stock_code_title']}))" : '')));

                    $i++;
                    $itemsAmount += $item['item_amount'];
                    $itemsPrice += $item['item_amount'] * $item['item_price'];

                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>" . str_pad($i, 5, 0, STR_PAD_LEFT) . " - " . Check::Words($item['item_name'], 5) . "{$pdtVariation}</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($item['item_price'], '2', ',', '.') . " * <b>{$item['item_amount']}</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($item['item_amount'] * $item['item_price'], '2', ',', '.') . "</td></tr>";
                endforeach;

                if (!empty($order_coupon)):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Cupom:</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>{$order_coupon}% de desconto</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>- <strike>R$ " . number_format($itemsPrice * ($order_coupon / 100), '2', ',', '.') . "</strike></td></tr>";
                endif;

                if (!empty($order_shipcode)):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Frete via " . getShipmentTag($order_shipcode) . "</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_shipprice, '2', ',', '.') . " <b>* 1</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_shipprice, '2', ',', '.') . "</td></tr>";
                endif;

                $bodyMail .= "<tr style='background: #cccccc;'><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px;'>{$i} produto(s) no pedido</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>{$itemsAmount} Itens</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>R$ " . number_format($order_price, '2', ',', '.') . "</td></tr>";

                if (!empty($order_installments) && $order_installments > 1):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Pago em {$order_installments}x de R$ " . number_format($order_installment, '2', ',', '.') . "</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>Total: </td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_installments * $order_installment, '2', ',', '.') . "</td></tr>";
                endif;

                $bodyMail .= "</table>";
            endif;

            $bodyMail .= "<p>Qualquer dúvida não deixe de entrar em contato {$client['user_name']}. Obrigado por sua preferência mais uma vez...</p>";
            $bodyMail .= "<p><i>Atenciosamente " . SITE_NAME . "!</i></p>";

            require '../cart.email.php';
            $mensagem = str_replace('#mail_body#', $bodyMail, $MailContent);
            $Email->EnviarMontando("Aguardando pagamento #" . str_pad($order_id, 7, 0, 0), $mensagem, SITE_NAME, MAIL_USER, "{$client['user_name']} {$client['user_lastname']}", $client['user_email']);
        elseif ($paymentStatus == 'IN_ANALYSIS'):
            /*
             * EM ANÁLISE
             */
            $dataOrder = ['order_status' => 4, 'order_free' => $transactionFees, 'order_update' => date('Y-m-d H:i:s'), 'order_mail_processing' => 1];
            $Update->ExeUpdate(DB_ORDERS, $dataOrder, 'WHERE order_id = :id', "id={$order_id}");

            $bodyMail = "<p style='font-size: 1.2em;'>Caro(a) {$Client['user_name']},</p>";
            $bodyMail .= "<p>Primeiramente gostaríamos de agradecer por você escolher a nossa loja para adquirir seus produtos.</p>";
            $bodyMail .= "<p>Seu pedido #" . str_pad($order_id, 7, 0, 0) . " foi concluído com sucesso!</p>";
            $bodyMail .= "<p>Informamos que seu <b>pagamento está em análise pela operadora</b>. E assim que aprovado, enviaremos seu pedido!</p>";
            $bodyMail .= "<p style='font-size: 1.4em;'>Detalhes do Pedido:</p>";
            $bodyMail .= "<p>Pedido: <a href='" . BASE . "/conta/pedido/{$order_id}' title='Ver pedido' target='_blank'>#" . str_pad($order_id, 7, 0, STR_PAD_LEFT) . "</a><br>Data: " . date('d/m/Y H\hi', strtotime($order_date)) . "<br>Valor: R$ " . number_format($order_price, '2', ',', '.') . "<br>Método de Pagamento: " . getOrderPayment($order_payment) . ($order_billet ? " - <a title='Imprimir Boleto!' href='{$order_billet}'>Imprimir Boleto!</a>" : "") . "</p>";
            $bodyMail .= "<hr><table style='width: 100%'><tr><td>STATUS:</td><td style='color: #00AD8E; text-align: center;'>✓ Aguardando Pagamento</td><td style='color: #888888; text-align: center;'>✓ Processando</td><td style='color: #888888; text-align: right;'>✓ Concluído</td></tr></table><hr>";

            $Read->ExeRead(DB_ORDERS_ITEMS, "WHERE order_id = :order", "order={$order_id}");
            if ($Read->getResult()):
                $i = 0;
                $itemsPrice = 0;
                $itemsAmount = 0;

                $bodyMail .= "<p style='font-size: 1.4em;'>Produtos:</p>";
                $bodyMail .= "<p>Abaixo você pode conferir os detalhes, quantidades e valores de cada produto adquirido em seu pedido. Confira:</p>";
                $bodyMail .= "<table style='width: 100%' border='0' cellspacing='0' cellpadding='0'>";

                foreach ($Read->getResult() as $Item):
                    $Read->FullRead("SELECT stock_code, stock_code_title, stock_color, stock_color_title FROM " . DB_PDT_STOCK . " WHERE stock_id = :stid", "stid={$Item['stock_id']}");
                    $pdtVariation = ($Read->getResult() && $Read->getResult()[0]['stock_color'] != 'default' && $Read->getResult()[0]['stock_code'] == 'default' ? " ({$Read->getResult()[0]['stock_color_title']})" : ($Read->getResult() && $Read->getResult()[0]['stock_color'] == 'default' && $Read->getResult()[0]['stock_code'] != 'default' ? " ({$Read->getResult()[0]['stock_code_title']})" : ($Read->getResult() && $Read->getResult()[0]['stock_color'] != 'default' && $Read->getResult()[0]['stock_code'] != 'default' ? " ({$Read->getResult()[0]['stock_color_title']} ({$Read->getResult()[0]['stock_code_title']}))" : '')));

                    $i++;
                    $itemsAmount += $Item['item_amount'];
                    $itemsPrice += $Item['item_amount'] * $Item['item_price'];

                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>" . str_pad($i, 5, 0, STR_PAD_LEFT) . " - " . Check::Words($Item['item_name'], 5) . "{$pdtVariation}</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($Item['item_price'], '2', ',', '.') . " * <b>{$Item['item_amount']}</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($Item['item_amount'] * $Item['item_price'], '2', ',', '.') . "</td></tr>";
                endforeach;

                if (!empty($order_coupon)):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Cupom:</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>{$order_coupon}% de desconto</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>- <strike>R$ " . number_format($itemsPrice * ($order_coupon / 100), '2', ',', '.') . "</strike></td></tr>";
                endif;

                if (!empty($order_shipcode)):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Frete via " . getShipmentTag($order_shipcode) . "</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_shipprice, '2', ',', '.') . " <b>* 1</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_shipprice, '2', ',', '.') . "</td></tr>";
                endif;

                $bodyMail .= "<tr style='background: #cccccc;'><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px;'>{$i} produto(s) no pedido</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>{$itemsAmount} Itens</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>R$ " . number_format($order_price, '2', ',', '.') . "</td></tr>";

                if (!empty($order_installments) && $order_installments > 1):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Pago em {$order_installments}x de R$ " . number_format($order_installment, '2', ',', '.') . "</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>Total: </td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_installments * $order_installment, '2', ',', '.') . "</td></tr>";
                endif;

                $bodyMail .= "</table>";
            endif;
            $bodyMail .= "<p>Qualquer dúvida não deixe de entrar em contato {$Client['user_name']}. Obrigado por sua preferência mais uma vez...</p>";
            $bodyMail .= "<p><i>Atenciosamente " . SITE_NAME . "!</i></p>";

            require '../cart.email.php';
            $mensagem = str_replace('#mail_body#', $bodyMail, $MailContent);
            $Email->EnviarMontando("Pagamento em análise #" . str_pad($order_id, 7, 0, 0), $mensagem, SITE_NAME, MAIL_USER, "{$Client['user_name']} {$Client['user_lastname']}", $Client['user_email']);
        elseif ($paymentStatus == 'AUTHORIZED'):
            /*
             * PAGO
             */
            $dataOrder = ['order_status' => 6, 'order_free' => $transactionFees, 'order_update' => date('Y-m-d H:i:s'), 'order_mail_completed' => 1];
            $Update->ExeUpdate(DB_ORDERS, $dataOrder, 'WHERE order_id = :id', "id={$order_id}");

            if (ECOMMERCE_STOCK):
                $Read->FullRead("SELECT pdt_id, stock_id, item_amount FROM " . DB_ORDERS_ITEMS . " WHERE order_id = :id", "id={$order_id}");
                foreach ($Read->getResult() as $OrderStockManage):
                    //STOCK UPDATE
                    $Read->FullRead("SELECT stock_inventory, stock_sold FROM " . DB_PDT_STOCK . " WHERE stock_id = :id", "id={$OrderStockManage['stock_id']}");
                    $updatePdtStock = ['stock_inventory' => $Read->getResult()[0]['stock_inventory'] - $OrderStockManage['item_amount'], 'stock_sold' => $Read->getResult()[0]['stock_sold'] + $OrderStockManage['item_amount']];
                    $Update->ExeUpdate(DB_PDT_STOCK, $updatePdtStock, "WHERE stock_id = :id", "id={$OrderStockManage['stock_id']}");

                    //INVENTORY UPDATE
                    $Read->FullRead("SELECT pdt_inventory, pdt_delivered FROM " . DB_PDT . " WHERE pdt_id = :id", "id={$OrderStockManage['pdt_id']}");
                    $updatePdtInventory = ['pdt_inventory' => $Read->getResult()[0]['pdt_inventory'] - $OrderStockManage['item_amount'], 'pdt_delivered' => $Read->getResult()[0]['pdt_delivered'] + $OrderStockManage['item_amount']];
                    $Update->ExeUpdate(DB_PDT, $updatePdtInventory, "WHERE pdt_id = :id", "id={$OrderStockManage['pdt_id']}");
                endforeach;
            endif;

            $bodyMail = "<p style='font-size: 1.2em;'>Caro(a) {$Client['user_name']},</p>";
            $bodyMail .= "<p>Seu pagamento para o pedido #" . str_pad($order_id, 7, 0, 0) . " foi aprovado, e já estamos preparando tudo por aqui!</p>";
            $bodyMail .= "<p>Assim que o pedido for postado, <b>enviaremos outro e-mail com os detalhes do envio</b> para que você possa acompanhar sua encomenda!</p>";
            $bodyMail .= "<p style='font-size: 1.4em;'>Detalhes do Pedido:</p>";
            $bodyMail .= "<p>Pedido: <a href='" . BASE . "/conta/pedido/{$order_id}' title='Ver pedido' target='_blank'>#" . str_pad($order_id, 7, 0, STR_PAD_LEFT) . "</a><br>Data: " . date('d/m/Y H\hi', strtotime($order_date)) . "<br>Valor: R$ " . number_format($order_price, '2', ',', '.') . "<br>Método de Pagamento: " . getOrderPayment($order_payment) . "</p>";
            $bodyMail .= "<hr><table style='width: 100%'><tr><td>STATUS:</td><td style='color: #00AD8E; text-align: center;'>✓ Aguardando Pagamento</td><td style='color: #00AD8E; text-align: center;'>✓ Processando</td><td style='color: #888888; text-align: right;'>✓ Concluído</td></tr></table><hr>";

            $Read->ExeRead(DB_ORDERS_ITEMS, "WHERE order_id = :order", "order={$order_id}");
            if ($Read->getResult()):
                $i = 0;
                $itemsPrice = 0;
                $itemsAmount = 0;

                $bodyMail .= "<p style='font-size: 1.4em;'>Produtos:</p>";
                $bodyMail .= "<p>Abaixo você pode conferir os detalhes, quantidades e valores de cada produto adquirido em seu pedido. Confira:</p>";
                $bodyMail .= "<table style='width: 100%' border='0' cellspacing='0' cellpadding='0'>";

                foreach ($Read->getResult() as $Item):
                    $Read->FullRead("SELECT stock_code, stock_code_title, stock_color, stock_color_title FROM " . DB_PDT_STOCK . " WHERE stock_id = :stid", "stid={$Item['stock_id']}");
                    $pdtVariation = ($Read->getResult() && $Read->getResult()[0]['stock_color'] != 'default' && $Read->getResult()[0]['stock_code'] == 'default' ? " ({$Read->getResult()[0]['stock_color_title']})" : ($Read->getResult() && $Read->getResult()[0]['stock_color'] == 'default' && $Read->getResult()[0]['stock_code'] != 'default' ? " ({$Read->getResult()[0]['stock_code_title']})" : ($Read->getResult() && $Read->getResult()[0]['stock_color'] != 'default' && $Read->getResult()[0]['stock_code'] != 'default' ? " ({$Read->getResult()[0]['stock_color_title']} ({$Read->getResult()[0]['stock_code_title']}))" : '')));

                    $i++;
                    $itemsAmount += $Item['item_amount'];
                    $itemsPrice += $Item['item_amount'] * $Item['item_price'];

                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>" . str_pad($i, 5, 0, STR_PAD_LEFT) . " - " . Check::Words($Item['item_name'], 5) . "{$pdtVariation}</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($Item['item_price'], '2', ',', '.') . " * <b>{$Item['item_amount']}</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($Item['item_amount'] * $Item['item_price'], '2', ',', '.') . "</td></tr>";
                endforeach;

                if (!empty($order_coupon)):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Cupom:</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>{$order_coupon}% de desconto</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>- <strike>R$ " . number_format($itemsPrice * ($order_coupon / 100), '2', ',', '.') . "</strike></td></tr>";
                endif;

                if (!empty($order_shipcode)):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Frete via " . getShipmentTag($order_shipcode) . "</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_shipprice, '2', ',', '.') . " <b>* 1</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_shipprice, '2', ',', '.') . "</td></tr>";
                endif;

                $bodyMail .= "<tr style='background: #cccccc;'><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px;'>{$i} produto(s) no pedido</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>{$itemsAmount} Itens</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>R$ " . number_format($order_price, '2', ',', '.') . "</td></tr>";

                if (!empty($order_installments) && $order_installments > 1):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Pago em {$order_installments}x de R$ " . number_format($order_installment, '2', ',', '.') . "</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>Total: </td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_installments * $order_installment, '2', ',', '.') . "</td></tr>";
                endif;

                $bodyMail .= "</table>";
            endif;

            $bodyMail .= "<p>Qualquer dúvida não deixe de entrar em contato {$Client['user_name']}. Obrigado por sua preferência mais uma vez...</p>";
            $bodyMail .= "<p><i>Atenciosamente " . SITE_NAME . "!</i></p>";

            require '../cart.email.php';
            $mensagem = str_replace('#mail_body#', $bodyMail, $MailContent);
            $Email->EnviarMontando("Seu pagamento foi aprovado #" . str_pad($order_id, 7, 0, 0), $mensagem, SITE_NAME, MAIL_USER, "{$Client['user_name']} {$Client['user_lastname']}", $Client['user_email']);

            //NOTIFICAÇÃO DE ENVIO
            $notifyMail = "<p style='font-size: 1.4em;'>Pagamento aprovado em " . date('d/m/Y H\hi') . "!</p>";
            $notifyMail .= "<p>";
            $notifyMail .= "Pedido: <a href='" . BASE . "/admin/dashboard.php?wc=orders/order&id={$order_id}' title='Detalhes do pedido'>#" . str_pad($order_id, 7, 0, 0) . "</a><br>";
            $notifyMail .= "Data: " . date("d/m/Y \a\s H\hi", strtotime($order_date)) . "<br>";
            $notifyMail .= "Valor: R$ " . number_format($order_price, '2', ',', '.') . "<br><br>";
            $notifyMail .= "Código PagSeguro: {$order_code}<br><br>";
            $notifyMail .= "Cliente: <a href='" . BASE . "/admin/dashboard.php?wc=users/create&id={$Client['user_id']}' title='{$Client['user_name']} {$Client['user_lastname']}'>{$Client['user_name']} {$Client['user_lastname']}</a><br>";
            $notifyMail .= "Telefone: {$Client['user_cell']}<br>";
            $notifyMail .= "E-mail: {$Client['user_email']}<br>";
            $notifyMail .= "</p>";
            $notifyMail .= "<p><b>Dica:</b> O pedido já pode ser enviado ao cliente. E o quanto mais rápido postar, maior será a satisfação do mesmo!</p>";
            $notifyMail .= "<p style='font-size: 1.2em;'>√ Concluir Pedido:</p>";
            $notifyMail .= "<ol><li>Realize o envio e anote o rastreio!</li><li>Acesse o pedido em seu painel!</li><li>Marque o <b>STATUS DO PEDIDO</b> como concluído!</li><li>Informe o código de rastreio!</li><li>Clique em atualizar pedido!</li></ol>";
            $notifyMail .= "<p>Ao concluir esse processo seu cliente receberá um e-mail com o link de rastreio junto aos detalhes do pedido!</p>";
            $notifyMail .= "<p><i>Atenciosamente " . ADMIN_NAME . "!</i></p>";

            require '../../../../admin/_tpl/Mail.email.php';
            $notify = str_replace('#mail_body#', $notifyMail, $MailContent);
            $Email->EnviarMontando("[#{$order_id}] Pagamento Aprovado!", $notify, SITE_NAME, MAIL_USER, MAIL_SENDER, PAGSEGURO_NOTIFICATION_EMAIL);
        elseif ($paymentStatus == 'REFUNDED'):
            /**
             * REEMBOLSO
             */
            $notifyMail = "<p style='font-size: 1.4em;'>O pedido #" . str_pad($order_id, 7, 0, 0) . " requer sua atenção!</p>";
            $notifyMail .= "<p>Uma disputa é aberta quando o cliente entende que não recebeu o produto. É importante resolver a questão o quanto antes!</p>";
            $notifyMail .= "<p>";
            $notifyMail .= "Abertura da disputa em " . date('d/m/Y H\hi') . "<br><br>";
            $notifyMail .= "Pedido: <a href='" . BASE . "/admin/dashboard.php?wc=orders/order&id={$order_id}' title='Detalhes do pedido'>#" . str_pad($order_id, 7, 0, 0) . "</a><br>";
            $notifyMail .= "Data: " . date("d/m/Y \a\s H\hi", strtotime($order_date)) . "<br>";
            $notifyMail .= "Valor: R$ " . number_format($order_price, '2', ',', '.') . "<br><br>";
            $notifyMail .= "Código PagSeguro: {$order_code}<br><br>";
            $notifyMail .= "Cliente: <a href='" . BASE . "/admin/dashboard.php?wc=users/create&id={$Client['user_id']}' title='{$Client['user_name']} {$Client['user_lastname']}'>{$Client['user_name']} {$Client['user_lastname']}</a><br>";
            $notifyMail .= "Telefone: {$Client['user_cell']}<br>";
            $notifyMail .= "E-mail: {$Client['user_email']}<br>";
            $notifyMail .= "</p>";
            $notifyMail .= "<p><b>Dica:</b> Ligue para o cliente para resolver o caso. Se não conseguir, acesse sua conta PagSeguro e envie os comprovantes de entrega do mesmo!</p>";
            $notifyMail .= "<p><b>Importante:</b> Pedidos com disputas que não são resolvidas, são devolvidos pela PagSeguro. Não deixe de resolver para não perder o pagamento!</p>";
            $notifyMail .= "<p><i>Atenciosamente " . ADMIN_NAME . "!</i></p>";

            require '../../../../admin/_tpl/Mail.email.php';
            $notify = str_replace('#mail_body#', $notifyMail, $MailContent);
            $Email->EnviarMontando("[#{$order_id}] Pagamento em disputa!", $notify, SITE_NAME, MAIL_USER, MAIL_SENDER, PAGSEGURO_NOTIFICATION_EMAIL);
        elseif ($paymentStatus == 'REVERSED'):
            /**
             * DEVOLVIDA
             */
            $bodyMail = "<p style='font-size: 1.2em;'>Caro(a) {$Client['user_name']},</p>";
            $bodyMail .= "<p>Este e-mail é para informar que o seu pedido #" . str_pad($order_id, 7, 0, 0) . " foi devolvido.</p>";

            $bodyMail .= "<p><b>Não desanime {$Client['user_name']}...</b></p>";
            $bodyMail .= "<p>...você ainda pode acessar nosso site e fazer um novo pedido. E assim que confirmado vamos processar e enviar o mais breve possível!</p>";
            $bodyMail .= "<p><a href='" . BASE . "' title='Conferir Produtos' target='_blank'>Confira aqui nossas novidades!</a></p>";

            $bodyMail .= "<p>Caso tenha qualquer dúvida por favor, entre em contato respondendo este e-mail ou pelo telefone " . SITE_ADDR_PHONE_A . ".</p>";
            $bodyMail .= "<p>Fique a vontade para escolher novos produtos e realizar um novo pedido em nossa loja! <a href='" . BASE . "' title='Produtos " . SITE_NAME . "'>Confira aqui nossos produtos!</a></p>";
            $bodyMail .= "<p style='font-size: 1.4em;'>Detalhes do Pedido:</p>";
            $bodyMail .= "<p>Pedido: <a href='" . BASE . "/conta/pedido/{$order_id}' title='Ver pedido' target='_blank'>#" . str_pad($order_id, 7, 0, STR_PAD_LEFT) . "</a><br>Data: " . date('d/m/Y H\hi', strtotime($order_date)) . "<br>Valor: R$ " . number_format($order_price, '2', ',', '.') . "<br>Método de Pagamento: " . getOrderPayment($order_payment) . "</p>";
            $bodyMail .= "<hr><table style='width: 100%'><tr><td>STATUS:</td><td style='color: #00AD8E; text-align: center;'>✓ Aguardando Pagamento</td><td style='color: #888888; text-align: center;'>✓ Processando</td><td style='color: #CC4E4F; text-align: right;'>✓ Devolvido</td></tr></table><hr>";

            $Read->ExeRead(DB_ORDERS_ITEMS, "WHERE order_id = :order", "order={$order_id}");
            if ($Read->getResult()):
                $i = 0;
                $itemsPrice = 0;
                $itemsAmount = 0;

                $bodyMail .= "<p style='font-size: 1.4em;'>Produtos:</p>";
                $bodyMail .= "<p>Abaixo você pode conferir os detalhes, quantidades e valores de cada produto adquirido em seu pedido. Confira:</p>";
                $bodyMail .= "<table style='width: 100%' border='0' cellspacing='0' cellpadding='0'>";

                foreach ($Read->getResult() as $Item):
                    $Read->FullRead("SELECT stock_code, stock_code_title, stock_color, stock_color_title FROM " . DB_PDT_STOCK . " WHERE stock_id = :stid", "stid={$Item['stock_id']}");
                    $pdtVariation = ($Read->getResult() && $Read->getResult()[0]['stock_color'] != 'default' && $Read->getResult()[0]['stock_code'] == 'default' ? " ({$Read->getResult()[0]['stock_color_title']})" : ($Read->getResult() && $Read->getResult()[0]['stock_color'] == 'default' && $Read->getResult()[0]['stock_code'] != 'default' ? " ({$Read->getResult()[0]['stock_code_title']})" : ($Read->getResult() && $Read->getResult()[0]['stock_color'] != 'default' && $Read->getResult()[0]['stock_code'] != 'default' ? " ({$Read->getResult()[0]['stock_color_title']} ({$Read->getResult()[0]['stock_code_title']}))" : '')));

                    $i++;
                    $itemsAmount += $Item['item_amount'];
                    $itemsPrice += $Item['item_amount'] * $Item['item_price'];

                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>" . str_pad($i, 5, 0, STR_PAD_LEFT) . " - " . Check::Words($Item['item_name'], 5) . "{$pdtVariation}</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($Item['item_price'], '2', ',', '.') . " * <b>{$Item['item_amount']}</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($Item['item_amount'] * $Item['item_price'], '2', ',', '.') . "</td></tr>";
                endforeach;

                if (!empty($order_coupon)):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Cupom:</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>{$order_coupon}% de desconto</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>- <strike>R$ " . number_format($itemsPrice * ($order_coupon / 100), '2', ',', '.') . "</strike></td></tr>";
                endif;

                if (!empty($order_shipcode)):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Frete via " . getShipmentTag($order_shipcode) . "</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_shipprice, '2', ',', '.') . " <b>* 1</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_shipprice, '2', ',', '.') . "</td></tr>";
                endif;

                $bodyMail .= "<tr style='background: #cccccc;'><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px;'>{$i} produto(s) no pedido</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>{$itemsAmount} Itens</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>R$ " . number_format($order_price, '2', ',', '.') . "</td></tr>";

                if (!empty($order_installments) && $order_installments > 1):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Pago em {$order_installments}x de R$ " . number_format($order_installment, '2', ',', '.') . "</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>Total: </td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_installments * $order_installment, '2', ',', '.') . "</td></tr>";
                endif;

                $bodyMail .= "</table>";
            endif;

            $bodyMail .= "<p>Qualquer dúvida não deixe de entrar em contato {$Client['user_name']}. Obrigado por sua preferência mais uma vez...</p>";
            $bodyMail .= "<p><i>Atenciosamente " . SITE_NAME . "!</i></p>";

            require '../cart.email.php';
            $mensagem = str_replace('#mail_body#', $bodyMail, $MailContent);
            $Email->EnviarMontando("Pedido cancelado #" . str_pad($order_id, 7, 0, 0), $mensagem, SITE_NAME, MAIL_USER, "{$Client['user_name']} {$Client['user_lastname']}", $Client['user_email']);

            //ORDER CANCEL
            if ($order_status != 2):
                $UpdateOrder = ['order_status' => 2, 'order_update' => date('Y-m-d H:i:s')];
                $Update->ExeUpdate(DB_ORDERS, $UpdateOrder, 'WHERE order_id = :id', "id={$order_id}");

                //STOCK MANAGER
                if (ECOMMERCE_STOCK):
                    $Read->FullRead("SELECT pdt_id, stock_id, item_amount FROM " . DB_ORDERS_ITEMS . ' WHERE order_id = :id', "id={$order_id}");
                    foreach ($Read->getResult() as $OrderStockManage):
                        //STOCK UPDATE
                        $Read->FullRead("SELECT stock_inventory, stock_sold FROM " . DB_PDT_STOCK . ' WHERE stock_id = :id', "id={$OrderStockManage['stock_id']}");
                        $updatePdtStock = ['stock_inventory' => $Read->getResult()[0]['stock_inventory'] + $OrderStockManage['item_amount'], 'stock_sold' => $Read->getResult()[0]['stock_sold'] - $OrderStockManage['item_amount']];
                        $Update->ExeUpdate(DB_PDT_STOCK, $updatePdtStock, "WHERE stock_id = :id", "id={$OrderStockManage['stock_id']}");

                        //INVENTORY UPDATE
                        $Read->FullRead("SELECT pdt_inventory, pdt_delivered FROM " . DB_PDT . ' WHERE pdt_id = :id', "id={$OrderStockManage['pdt_id']}");
                        $updatePdtInventory = ['pdt_inventory' => $Read->getResult()[0]['pdt_inventory'] + $OrderStockManage['item_amount'], 'pdt_delivered' => $Read->getResult()[0]['pdt_delivered'] - $OrderStockManage['item_amount']];
                        $Update->ExeUpdate(DB_PDT, $updatePdtInventory, "WHERE pdt_id = :id", "id={$OrderStockManage['pdt_id']}");
                    endforeach;
                endif;
            endif;

            //NOTIFY
            $notifyMail = "<p style='font-size: 1.4em;'>O pedido #" . str_pad($order_id, 7, 0, 0) . " foi devolvido!</p>";
            $notifyMail .= "<p>O devolução ocorre quando o cliente abre uma disputa e você deve devolver o valor pago! Seu cliente também foi notificado!</p>";
            $notifyMail .= "<p>";
            $notifyMail .= "Devolvido em " . date('d/m/Y H\hi') . "<br><br>";
            $notifyMail .= "Pedido: <a href='" . BASE . "/admin/dashboard.php?wc=orders/order&id={$order_id}' title='Detalhes do pedido'>#" . str_pad($order_id, 7, 0, 0) . "</a><br>";
            $notifyMail .= "Data: " . date("d/m/Y \a\s H\hi", strtotime($order_date)) . "<br>";
            $notifyMail .= "Valor: R$ " . number_format($order_price, '2', ',', '.') . "<br><br>";
            $notifyMail .= "Código PagSeguro: {$order_code}<br><br>";
            $notifyMail .= "Cliente: <a href='" . BASE . "/admin/dashboard.php?wc=users/create&id={$Client['user_id']}' title='{$Client['user_name']} {$Client['user_lastname']}'>{$Client['user_name']} {$Client['user_lastname']}</a><br>";
            $notifyMail .= "Telefone: {$Client['user_cell']}<br>";
            $notifyMail .= "E-mail: {$Client['user_email']}<br>";
            $notifyMail .= "</p>";
            $notifyMail .= "<p><b>Dica:</b> Experimente ligar para o cliente para falar sobre o pedido ainda hoje. Com isso você aumenta as chances de um novo pedido com sucesso!</p>";
            $notifyMail .= "<p><b>Estoque:</b> Os produtos deste pedido tiveram seu estoque reposto automaticamente, e se ativos estão a venda em sua loja online!</p>";
            $notifyMail .= "<p><i>Atenciosamente " . ADMIN_NAME . "!</i></p>";

            require '../../../../admin/_tpl/Mail.email.php';
            $notify = str_replace('#mail_body#', $notifyMail, $MailContent);
            $Email->EnviarMontando("[#{$order_id}] Pedido cancelado!", $notify, SITE_NAME, MAIL_USER, MAIL_SENDER, PAGSEGURO_NOTIFICATION_EMAIL);
        elseif ($paymentStatus == 'CANCELLED'):
            /**
             * CANCELADO
             */
            $bodyMail = "<p style='font-size: 1.2em;'>Caro(a) {$Client['user_name']},</p>";
            $bodyMail .= "<p>Este e-mail é para informar que o seu pedido #" . str_pad($order_id, 7, 0, 0) . " foi cancelado.</p>";
            $bodyMail .= "<p>Isso ocorre quando o pagamento não é identificado no prazo, ou quando a operadora (em compras com cartão) nega o pagamento!</p>";

            $bodyMail .= "<p><b>Não desanime {$Client['user_name']}...</b></p>";
            $bodyMail .= "<p>...você ainda pode acessar nosso site e fazer um novo pedido. E assim que confirmado vamos processar e enviar o mais breve possível!</p>";
            $bodyMail .= "<p><a href='" . BASE . "' title='Conferir Produtos' target='_blank'>Confira aqui nossas novidades!</a></p>";

            $bodyMail .= "<p>Caso tenha qualquer dúvida por favor, entre em contato respondendo este e-mail ou pelo telefone " . SITE_ADDR_PHONE_A . ".</p>";
            $bodyMail .= "<p>Fique a vontade para escolher novos produtos e realizar um novo pedido em nossa loja! <a href='" . BASE . "' title='Produtos " . SITE_NAME . "'>Confira aqui nossos produtos!</a></p>";
            $bodyMail .= "<p style='font-size: 1.4em;'>Detalhes do Pedido:</p>";
            $bodyMail .= "<p>Pedido: <a href='" . BASE . "/conta/pedido/{$order_id}' title='Ver pedido' target='_blank'>#" . str_pad($order_id, 7, 0, STR_PAD_LEFT) . "</a><br>Data: " . date('d/m/Y H\hi', strtotime($order_date)) . "<br>Valor: R$ " . number_format($order_price, '2', ',', '.') . "<br>Método de Pagamento: " . getOrderPayment($order_payment) . "</p>";
            $bodyMail .= "<hr><table style='width: 100%'><tr><td>STATUS:</td><td style='color: #00AD8E; text-align: center;'>✓ Aguardando Pagamento</td><td style='color: #888888; text-align: center;'>✓ Processando</td><td style='color: #CC4E4F; text-align: right;'>✓ Cancelado</td></tr></table><hr>";

            $Read->ExeRead(DB_ORDERS_ITEMS, "WHERE order_id = :order", "order={$order_id}");
            if ($Read->getResult()):
                $i = 0;
                $itemsPrice = 0;
                $itemsAmount = 0;

                $bodyMail .= "<p style='font-size: 1.4em;'>Produtos:</p>";
                $bodyMail .= "<p>Abaixo você pode conferir os detalhes, quantidades e valores de cada produto adquirido em seu pedido. Confira:</p>";
                $bodyMail .= "<table style='width: 100%' border='0' cellspacing='0' cellpadding='0'>";

                foreach ($Read->getResult() as $Item):
                    $Read->FullRead("SELECT stock_code, stock_code_title, stock_color, stock_color_title FROM " . DB_PDT_STOCK . " WHERE stock_id = :stid", "stid={$Item['stock_id']}");
                    $pdtVariation = ($Read->getResult() && $Read->getResult()[0]['stock_color'] != 'default' && $Read->getResult()[0]['stock_code'] == 'default' ? " ({$Read->getResult()[0]['stock_color_title']})" : ($Read->getResult() && $Read->getResult()[0]['stock_color'] == 'default' && $Read->getResult()[0]['stock_code'] != 'default' ? " ({$Read->getResult()[0]['stock_code_title']})" : ($Read->getResult() && $Read->getResult()[0]['stock_color'] != 'default' && $Read->getResult()[0]['stock_code'] != 'default' ? " ({$Read->getResult()[0]['stock_color_title']} ({$Read->getResult()[0]['stock_code_title']}))" : '')));

                    $i++;
                    $itemsAmount += $Item['item_amount'];
                    $itemsPrice += $Item['item_amount'] * $Item['item_price'];

                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>" . str_pad($i, 5, 0, STR_PAD_LEFT) . " - " . Check::Words($Item['item_name'], 5) . "{$pdtVariation}</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($Item['item_price'], '2', ',', '.') . " * <b>{$Item['item_amount']}</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($Item['item_amount'] * $Item['item_price'], '2', ',', '.') . "</td></tr>";
                endforeach;

                if (!empty($order_coupon)):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Cupom:</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>{$order_coupon}% de desconto</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>- <strike>R$ " . number_format($itemsPrice * ($order_coupon / 100), '2', ',', '.') . "</strike></td></tr>";
                endif;

                if (!empty($order_shipcode)):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Frete via " . getShipmentTag($order_shipcode) . "</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_shipprice, '2', ',', '.') . " <b>* 1</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_shipprice, '2', ',', '.') . "</td></tr>";
                endif;

                $bodyMail .= "<tr style='background: #cccccc;'><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px;'>{$i} produto(s) no pedido</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>{$itemsAmount} Itens</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>R$ " . number_format($order_price, '2', ',', '.') . "</td></tr>";

                if (!empty($order_installments) && $order_installments > 1):
                    $bodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Pago em {$order_installments}x de R$ " . number_format($order_installment, '2', ',', '.') . "</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>Total: </td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($order_installments * $order_installment, '2', ',', '.') . "</td></tr>";
                endif;

                $bodyMail .= "</table>";
            endif;

            $bodyMail .= "<p>Qualquer dúvida não deixe de entrar em contato {$Client['user_name']}. Obrigado por sua preferência mais uma vez...</p>";
            $bodyMail .= "<p><i>Atenciosamente " . SITE_NAME . "!</i></p>";

            require '../cart.email.php';
            $Mensagem = str_replace('#mail_body#', $bodyMail, $MailContent);
            $Email->EnviarMontando("Pedido cancelado #" . str_pad($order_id, 7, 0, 0), $Mensagem, SITE_NAME, MAIL_USER, "{$Client['user_name']} {$Client['user_lastname']}", $Client['user_email']);

            //ORDER CANCEL
            if ($order_status != 2):
                $UpdateOrder = ['order_status' => 2, 'order_update' => date('Y-m-d H:i:s')];
                $Update->ExeUpdate(DB_ORDERS, $UpdateOrder, 'WHERE order_id = :orid', "orid={$order_id}");
            endif;

            //NOTIFY
            $NotifyMail = "<p style='font-size: 1.4em;'>O pedido #" . str_pad($order_id, 7, 0, 0) . " foi cancelado!</p>";
            $NotifyMail .= "<p>O cancelamento ocorre quando o prazo de pagamento não é atendido na operadora. Seu cliente também foi notificado!</p>";
            $NotifyMail .= "<p>";
            $NotifyMail .= "Cancelamento em " . date('d/m/Y H\hi') . "<br><br>";
            $NotifyMail .= "Pedido: <a href='" . BASE . "/admin/dashboard.php?wc=orders/order&id={$order_id}' title='Detalhes do pedido'>#" . str_pad($order_id, 7, 0, 0) . "</a><br>";
            $NotifyMail .= "Data: " . date("d/m/Y \a\s H\hi", strtotime($order_date)) . "<br>";
            $NotifyMail .= "Valor: R$ " . number_format($order_price, '2', ',', '.') . "<br><br>";
            $NotifyMail .= "Código PagSeguro: {$order_code}<br><br>";
            $NotifyMail .= "Cliente: <a href='" . BASE . "/admin/dashboard.php?wc=users/create&id={$Client['user_id']}' title='{$Client['user_name']} {$Client['user_lastname']}'>{$Client['user_name']} {$Client['user_lastname']}</a><br>";
            $NotifyMail .= "Telefone: {$Client['user_cell']}<br>";
            $NotifyMail .= "E-mail: {$Client['user_email']}<br>";
            $NotifyMail .= "</p>";
            $NotifyMail .= "<p><b>Dica:</b> Experimente ligar para o cliente para falar sobre o pedido ainda hoje. Com isso você aumenta as chances de um novo pedido com sucesso!</p>";
            $NotifyMail .= "<p><b>Estoque:</b> Os produtos deste pedido não tiveram seu estoque alterados nessa compra!</p>";
            $NotifyMail .= "<p><i>Atenciosamente " . ADMIN_NAME . "!</i></p>";

            require '../../../../admin/_tpl/Mail.email.php';
            $Notify = str_replace('#mail_body#', $NotifyMail, $MailContent);
            $Email->EnviarMontando("[#{$order_id}] Pedido cancelado!", $Notify, SITE_NAME, MAIL_USER, MAIL_SENDER, PAGSEGURO_NOTIFICATION_EMAIL);
        endif;
    endif;
endif;