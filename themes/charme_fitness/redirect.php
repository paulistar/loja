<?php
// AUTO INSTANCE OBJECT CREATE
if (empty($Create)):
    $Create = new Create;
endif;

// AUTO INSTANCE OBJECT UPDATE
if (empty($Update)):
    $Update = new Update;
endif;

// AUTO INSTANCE OBJECT UPDATE
if (empty($Delete)):
    $Delete = new Delete;
endif;

// Captura os dados do usuário via GET
$PostData['user_email'] = filter_input(INPUT_GET, 'email', FILTER_DEFAULT);
$PostData['user_password'] = filter_input(INPUT_GET, 'password', FILTER_DEFAULT);
$PostData = array_map('strip_tags', $PostData);

// Verifica a autenticidade do usuário
$Read->ExeRead(DB_USERS, 'WHERE user_email = :email AND user_password = :pass',
    "email={$PostData['user_email']}&pass={$PostData['user_password']}");

if (!$Read->getResult()):
    header("Location: " . BASE . '/conta');
    exit;
else:
    // Alimenta a sessão do usuário
    $_SESSION['userLogin'] = $Read->getResult()[0];

    // Armazena sessão para dá boas-vindas ao usuário
    $_SESSION['redirect'] = true;

    // Verifica se o abandono de carrinho está tivo
    if (ECOMMERCE_ABANDONED_CART):
        /*
         * Atualiza a quantidade dos produtos abandonados no carrinho
         * caso o usuário tenha adicionado mais produtos no carrinho
         * não estando logado em sua conta.
         */
        if (!empty($_SESSION['wc_order'])):
            foreach ($_SESSION['wc_order'] as $STOCK => $AMOUNT):
                $Read->FullRead('SELECT stock_id FROM ' . DB_PDT_ABANDONED_CART . ' WHERE stock_id = :stock AND user_id = :user AND abandoned_notify = :notify', "stock={$STOCK}&user={$_SESSION['userLogin']['user_id']}&notify=1");
                /*
                 * Se verdadeiro, irá atualizar a quantidade dos produtos
                 * abandonados no carrinho individualmente.
                 */
                if ($Read->getResult()):
                    $Data = ['abandoned_amount' => $AMOUNT];
                    $Update->ExeUpdate(DB_PDT_ABANDONED_CART, $Data, 'WHERE stock_id = :stock AND user_id = :user', "stock={$STOCK}&user={$_SESSION['userLogin']['user_id']}");
                else:
                    /*
                     * Se false, cadastrar esses produtos na lista
                     * dos produtos abandonados no carrinho.
                     */
                    $Data = ['user_id' => $_SESSION['userLogin']['user_id'], 'stock_id' => $STOCK, 'abandoned_amount' => $AMOUNT, 'abandoned_notify' => '1'];
                    $Create->ExeCreate(DB_PDT_ABANDONED_CART, $Data);
                endif;
            endforeach;
        endif;

        $Read->FullRead('SELECT stock_id, abandoned_amount FROM ' . DB_PDT_ABANDONED_CART . ' WHERE user_id = :id AND abandoned_notify = :notify', "id={$_SESSION['userLogin']['user_id']}&notify=1");
        if ($Read->getResult()):
            /*
             * Armazena a soma total de produtos adicionados
             * no carrinho a ser pago pelo usuário
             */
            $cartTotal = 0;
            foreach ($Read->getResult() as $ABANDONED):
                $_SESSION['wc_order'][$ABANDONED['stock_id']] = intval($ABANDONED['abandoned_amount']);
                $Read->FullRead('SELECT p.pdt_price, p.pdt_offer_price, p.pdt_offer_start, p.pdt_offer_end FROM ' . DB_PDT . ' p INNER JOIN ' . DB_PDT_STOCK . ' s ON p.pdt_id = s.pdt_id WHERE s.stock_id = :id', "id={$ABANDONED['stock_id']}");
                if ($Read->getResult()):
                    $cartTotal += ($Read->getResult()[0]['pdt_offer_price'] && $Read->getResult()[0]['pdt_offer_start'] <= date('Y-m-d H:i:s') && $Read->getResult()[0]['pdt_offer_end'] >= date('Y-m-d H:i:s') ? $Read->getResult()[0]['pdt_offer_price'] : $Read->getResult()[0]['pdt_price']);
                endif;
            endforeach;

            // Verifica se o usuário terá um cupom de desconto
            $couponCode = ECOMMERCE_ABANDONED_CART_COUPON;
            $Read->ExeRead(DB_PDT_COUPONS, 'WHERE cp_coupon = :cp AND cp_start <= NOW() AND cp_end >= NOW()', "cp={$couponCode}");
            if ($Read->getResult()):
                $dataCoupon = $Read->getResult()[0];
                if ($cartTotal >= $dataCoupon['cp_minimum']):
                    $_SESSION['wc_cupom'] = $dataCoupon['cp_discount'];
                    $_SESSION['wc_cupom_code'] = $dataCoupon['cp_coupon'];
                endif;
            endif;

            /**
             * Deleta os produtos abandonados no carrinhos que
             * já realizaram todo o ciclo para retorno do usuário.
             */
            $Delete->ExeDelete(DB_PDT_ABANDONED_CART, 'WHERE user_id = :id AND abandoned_notify = :notify',
                "id={$_SESSION['userLogin']['user_id']}&notify=1");
        endif;
    endif;

    header("Location: " . BASE . '/pedido/home#cart');
    exit;
endif;
