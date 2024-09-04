<?php
// AUTO INSTANCE CONFIG
require '../../../_app/Config.inc.php';

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

// AUTO INSTANCE OBJECT UPDATE
if (empty($Update)):
    $Update = new Update;
endif;

// AUTO INSTANCE OBJECT E-MAIL
if (empty($Email)):
    $Email = new Email;
endif;

// Verifica se o abandono de carrinho está tivo
if (ECOMMERCE_ABANDONED_CART):
    $Read->FullRead('SELECT user_id FROM ' . DB_PDT_ABANDONED_CART . ' GROUP BY user_id');
    if ($Read->getResult()):
        foreach ($Read->getResult() as $USER):
            /*
             * Verifica a existência do usuário
             */
            $Read->FullRead('SELECT user_id, user_name, user_lastname, user_email, user_password FROM ' . DB_USERS . ' WHERE user_id = :id', "id={$USER['user_id']}");
            if ($Read->getResult()):
                $dataUser = $Read->getResult()[0];

                $Read->FullRead('SELECT online_endview FROM ' . DB_VIEWS_ONLINE . ' WHERE online_user = :id', "id={$dataUser['user_id']}");
                if (!$Read->getResult() || strtotime($Read->getResult()[0]['online_endview']) < strtotime(date('Y-m-d H:i:s'))):
                    /*
                     * Conta quantos produtos o usuário
                     * possui abandonados no carrinho.
                     */
                    $Read->FullRead('SELECT COUNT(abandoned_id) AS total FROM ' . DB_PDT_ABANDONED_CART . ' WHERE user_id = :user AND abandoned_notify = :notify', "user={$dataUser['user_id']}&notify=0");
                    $countPdt = $Read->getResult()[0]['total'];
                    /*
                     * Verifica se o usuário ainda não recebeu o e-mail
                     * de notificação de abandono de carrinho.
                     */
                    $Read->FullRead('SELECT stock_id, abandoned_amount FROM ' . DB_PDT_ABANDONED_CART . ' WHERE user_id = :user AND abandoned_notify = :notify', "user={$dataUser['user_id']}&notify=0");
                    if ($Read->getResult()):
                        $dataAbandoned = $Read->getResult();
                        /*
                         * Armazena a soma total de produtos adicionados
                         * no carrinho a ser pago pelo usuário
                         */
                        $totalCart = 0;
                        foreach ($dataAbandoned as $ABANDONED):
                            $Read->FullRead('SELECT p.pdt_price, p.pdt_offer_price, p.pdt_offer_start, p.pdt_offer_end FROM ' . DB_PDT . ' p INNER JOIN ' . DB_PDT_STOCK . ' s ON p.pdt_id = s.pdt_id WHERE s.stock_id = :id', "id={$ABANDONED['stock_id']}");
                            if ($Read->getResult()):
                                $totalCart += ($Read->getResult()[0]['pdt_offer_price'] && $Read->getResult()[0]['pdt_offer_start'] <= date('Y-m-d H:i:s') && $Read->getResult()[0]['pdt_offer_end'] >= date('Y-m-d H:i:s') ? $Read->getResult()[0]['pdt_offer_price'] * $ABANDONED['abandoned_amount'] : $Read->getResult()[0]['pdt_price'] * $ABANDONED['abandoned_amount']);
                            endif;
                        endforeach;
                        /*
                         * Prepara a mensagem de e-mail que será
                         * enviada para o usuário $dataUser['user_id']
                         */
                        $BodyMail = "<p style='font-size: 1.2em;'>Caro(a) {$dataUser['user_name']},</p>";
                        $BodyMail .= "<p>Ficamos preocupado! Pois no seu último acesso detectamos que você adicionou {$countPdt} produtos em seu carrinho mas não finalizou a compra.</p>";
                        $BodyMail .= "<p>Finalize sua compra em nosso site, neste momento estamos com super descontos para você...</p>";
                        /*
                         * Verifica se há um cupom de desconto
                         * e faz o redirecionamento para o site
                         */
                        $couponCode = ECOMMERCE_ABANDONED_CART_COUPON;
                        $Read->ExeRead(DB_PDT_COUPONS, 'WHERE cp_coupon = :cp AND cp_start <= NOW() AND cp_end >= NOW()', "cp={$couponCode}");
                        if ($Read->getResult()):
                            $dataCoupon = $Read->getResult()[0];
                            $BodyMail .= "<a style='color: #333333; text-decoration: none;' href='" . BASE . "/redirect&email={$dataUser['user_email']}&password={$dataUser['user_password']}' title='VOCÊ GANHOU {$dataCoupon['cp_discount']}% DE DESCONTO!'>CUPOM <span style='display: inline-block; font-size: 2em; color: #ffffff; padding: 5px 10px; background-color: #000000; border-right: 2px dotted #ffffff;'>{$dataCoupon['cp_coupon']}</span><span style='display: inline-block; font-size: 2em; color: #ffffff; padding: 5px 10px; background-color: #1bd2b1; border-left: 2px dotted #ffffff;'>-{$dataCoupon['cp_discount']}%</span></a>";
                            $Subject = "VOCÊ GANHOU {$dataCoupon['cp_discount']}% DE DESCONTO!";
                        else:
                            $Subject = 'Finalize sua compra em nosso site!';
                        endif;

                        $i = 0;
                        $ItemsPrice = 0;
                        $ItemsAmount = 0;

                        $BodyMail .= "<p style='font-size: 1.4em;'>Produtos:</p>";
                        $BodyMail .= "<p>Abaixo você pode conferir os detalhes, quantidades e valores de cada produto adicionado em seu carrinho. Confira:</p>";
                        $BodyMail .= "<table style='width: 100%' border='0' cellspacing='0' cellpadding='0'>";

                        foreach ($dataAbandoned as $ABANDONED):
                            $Read->FullRead("SELECT pdt_id, (SELECT attr_size_code FROM " . DB_PDT_ATTR_SIZES . " WHERE size_id = attr_size_id) AS attr_size_code, (SELECT attr_size_title FROM " . DB_PDT_ATTR_SIZES . " WHERE size_id = attr_size_id) AS attr_size_title, (SELECT attr_color_code FROM " . DB_PDT_ATTR_COLORS . " WHERE color_id = attr_color_id) AS attr_color_code, (SELECT attr_color_title FROM " . DB_PDT_ATTR_COLORS . " WHERE color_id = attr_color_id) AS attr_color_title, (SELECT attr_print_code FROM " . DB_PDT_ATTR_PRINTS . " WHERE print_id = attr_print_id) AS attr_print_code, (SELECT attr_print_title FROM " . DB_PDT_ATTR_PRINTS . " WHERE print_id = attr_print_id) AS attr_print_title FROM " . DB_PDT_STOCK . " WHERE stock_id = :id", "id={$ABANDONED['stock_id']}");
                            if ($Read->getResult()):
                                $dataStock = $Read->getResult()[0];
                                $PdtVariation = ($dataStock && !empty($dataStock['attr_color_code']) && empty($dataStock['attr_size_code']) && empty($dataStock['attr_print_code']) ? " <span class='wc_cart_tag'>Cor: {$dataStock['attr_color_title']}</span>" : ($dataStock && empty($dataStock['attr_color_code']) && empty($dataStock['attr_print_code']) && !empty($dataStock['attr_size_code']) ? " <span class='wc_cart_tag'>Tamanho: {$dataStock['attr_size_title']}</span>" : ($dataStock && !empty($dataStock['attr_color_code']) && !empty($dataStock['attr_size_code']) && empty($dataStock['attr_print_code']) ? " <span class='wc_cart_tag'>Cor: {$dataStock['attr_color_title']}</span> <span class='wc_cart_tag'>Tamanho: {$dataStock['attr_size_title']}</span>" : ($dataStock && !empty($dataStock['attr_print_code']) && empty($dataStock['attr_size_code']) && empty($dataStock['attr_color_code']) ? " <span class='wc_cart_tag' title='Estampa: {$dataStock['attr_print_title']}' style='background-image: url(" . BASE . "/uploads/{$dataStock['attr_print_code']});'></span>" : ($dataStock && !empty($dataStock['attr_print_code']) && !empty($dataStock['attr_size_code']) && empty($dataStock['attr_color_code']) ? " <span class='wc_cart_tag' title='Estampa: {$dataStock['attr_print_title']}' style='background-image: url(" . BASE . "/uploads/{$dataStock['attr_print_code']});'></span> <span class='wc_cart_tag'>Tamanho: {$dataStock['attr_size_title']}</span>" : '')))));

                                $Read->FullRead('SELECT pdt_title, pdt_cover, pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM ' . DB_PDT . ' WHERE pdt_id = :id', "id={$dataStock['pdt_id']}");
                                if ($Read->getResult()):
                                    $dataPdt = $Read->getResult()[0];
                                    $pdtPrice = ($dataPdt['pdt_offer_price'] && $dataPdt['pdt_offer_start'] <= date('Y-m-d H:i:s') && $dataPdt['pdt_offer_end'] >= date('Y-m-d H:i:s') ? $dataPdt['pdt_offer_price'] : $dataPdt['pdt_price']);
                                    $ItemsPrice += $pdtPrice * $ABANDONED['abandoned_amount'];

                                    $i++;
                                    $ItemsAmount += $ABANDONED['abandoned_amount'];
                                    $BodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 5px 10px 5px 0;'><img style='width: 50px;' src='" . BASE . "/uploads/{$dataPdt['pdt_cover']}' alt='{$dataPdt['pdt_title']}' title='{$dataPdt['pdt_title']}'/></td><td style='border-bottom: 1px solid #cccccc; padding: 5px 0;'>" . str_pad($i, 3, 0, STR_PAD_LEFT) . " - " . Check::Chars($dataPdt['pdt_title'], 20) . "</td><td style='border-bottom: 1px solid #cccccc; padding: 5px 10px; text-align: right;'>{$PdtVariation}</td><td style='border-bottom: 1px solid #cccccc; padding: 5px 0; text-align: right;'>R$ " . number_format($pdtPrice, '2', ',', '.') . " * <b>{$ABANDONED['abandoned_amount']}</b></td><td style='border-bottom: 1px solid #cccccc; padding: 5px 0; text-align: right;'>R$ " . number_format($ABANDONED['abandoned_amount'] * $pdtPrice, '2', ',', '.') . "</td></tr>";
                                endif;
                            endif;
                        endforeach;

                        $BodyMail .= "<tr style='background-color: #cccccc;'><td></td><td colspan='2' style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 0;'>{$i} produto(s) no carrinho</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>{$ItemsAmount} Itens</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>R$ " . number_format($ItemsPrice, '2', ',', '.') . "</td></tr>";
                        $BodyMail .= "</table>";

                        $BodyMail .= "<p style='margin: 30px 0;'><a style='text-decoration: none;' href='" . BASE . "/redirect&email={$dataUser['user_email']}&password={$dataUser['user_password']}' title='" . SITE_NAME . "'><span style='font-size: 0.9em; color: #444444;'>Acesse nosso site: </span><span style='font-size: 1.1em; color: #00B494; border-bottom: 1px solid #00B494;'>" . SITE_NAME . "</span></a></p>";

                        $BodyMail .= "<p>Qualquer dúvida não deixe de entrar em contato {$dataUser['user_name']}. Obrigado por sua preferência mais uma vez...</p>";
                        $BodyMail .= "<p><i>Atenciosamente " . SITE_NAME . "!</i></p>";

                        /*
                         * Envia e-mail para o usuário $dataUser['user_id']
                         * indicando-o para retornar ao site e fechar a compra
                         */
                        require 'cart.email.php';
                        $Message = str_replace('#mail_body#', $BodyMail, $MailContent);
                        $Email->EnviarMontando($Subject, $Message, SITE_NAME, MAIL_USER, "{$dataUser['user_name']} {$dataUser['user_lastname']}", "{$dataUser['user_email']}");
                        /*
                         * Após o cliente $dataUser['user_id'] receber a notificação
                         * de email atualiza a coluna abandoned_notify para '1'
                         */
                        $dataNotify = ['abandoned_notify' => '1'];
                        $Update->ExeUpdate(DB_PDT_ABANDONED_CART, $dataNotify, 'WHERE user_id = :user AND abandoned_notify = :notify', "user={$dataUser['user_id']}&notify=0");
                    endif;
                endif;
            endif;
        endforeach;
    endif;
endif;
