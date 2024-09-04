<?php

session_start();

$getPost = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (empty($getPost) || empty($getPost['action'])):
    die('Acesso Negado!');
endif;

$strPost = array_map('strip_tags', $getPost);
$POST = array_map('trim', $strPost);

$Action = $POST['action'];
$jSON = null;
unset($POST['action']);

usleep(2000);

if (empty($_SESSION['wc_shipment_zip'])):
    unset($_SESSION['wc_shipment']);
endif;

require '../../../_app/Config.inc.php';
$Read = new Read;
$Create = new Create;
$Update = new Update;
$Delete = new Delete;

switch ($Action):
    /* GET PRODUCT SIZES */
    /* CUSTOM BY ALISSON */
    case 'get_sizes':
        $PdtId = $POST['id'];
        $PdtPrice = $POST['price'];

        if (isset($POST['color'])):
            $Color = $POST['color'];
            $Read->FullRead("SELECT s.stock_id, s.stock_inventory, a.attr_size_code, a.attr_size_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_SIZES . " a ON s.size_id = a.attr_size_id WHERE s.pdt_id = :id AND s.size_id IS NOT NULL AND s.color_id = :color AND s.stock_inventory >= :inventory GROUP BY s.size_id ORDER BY s.stock_id ASC", "id={$PdtId}&color={$Color}&inventory=1");
        else:
            $Print = $POST['print'];
            $Read->FullRead("SELECT s.stock_id, s.stock_inventory, a.attr_size_code, a.attr_size_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_SIZES . " a ON s.size_id = a.attr_size_id WHERE s.pdt_id = :id AND s.size_id IS NOT NULL AND s.print_id = :print AND s.stock_inventory >= :inventory GROUP BY s.size_id ORDER BY s.stock_id ASC", "id={$PdtId}&print={$Print}&inventory=1");
        endif;


        if ($Read->getResult()):
            $jSON['sizes'] = null;
            foreach ($Read->getResult() as $StockVar):
                $jSON['sizes'] .= "<label id='{$StockVar['stock_inventory']}' class='wc_select_size' title='{$StockVar['attr_size_title']}' data-pdt-price='" . number_format($PdtPrice, 2, ',', '.') . "'>{$StockVar['attr_size_code']} <input type='radio' name='stock_id' value='{$StockVar['stock_id']}'/><span><i class='fa fa-check'></i></span></label> ";
            endforeach;
        endif;
        break;

    /* GET TOTAL PURCHASE */
    /* CUSTOM BY ALISSON */
    case 'get_total':
        $PdtId = $POST['id'];
        $Amount = $POST['amount'];
        $PdtPrice = $POST['price'];
        $Total = $PdtPrice * $Amount;

        $Read->LinkResult(DB_PDT, 'pdt_id', $PdtId, 'pdt_unity');
        if (!empty($Read->getResult()[0]['pdt_unity'])):
            $Text = ($Amount >= 2 ? "{$Read->getResult()[0]['pdt_unity']}s" : $Read->getResult()[0]['pdt_unity']);
        else:
            $Text = ($Amount >= 2 ? 'itens' : 'item');
        endif;

        $jSON['total'] = "<p>Total de <span>" . str_replace('.', ',', $Amount) . "</span> {$Text}</p><p>Por <span>R$ " . number_format($Total, 2, ',', '.') . "</span></p>";

        if (ECOMMERCE_PAY_SPLIT):
            $MakeSplit = intval($Total / ECOMMERCE_PAY_SPLIT_MIN);
            $NumSplit = (!$MakeSplit ? 1 : ($MakeSplit && $MakeSplit <= ECOMMERCE_PAY_SPLIT_NUM ? $MakeSplit : ECOMMERCE_PAY_SPLIT_NUM));
            if ($NumSplit <= ECOMMERCE_PAY_SPLIT_ACN):
                $SplitPrice = number_format(($Total / $NumSplit), '2', ',', '.');
            elseif ($NumSplit - ECOMMERCE_PAY_SPLIT_ACN == 1):
                $SplitPrice = number_format(($Total * (pow(1 + (ECOMMERCE_PAY_SPLIT_ACM / 100), $NumSplit - ECOMMERCE_PAY_SPLIT_ACN)) / $NumSplit), '2', ',', '.');
            else:
                $ParcSj = round($Total / $NumSplit, 2); // Valor das parcelas sem juros
                $ParcRest = (ECOMMERCE_PAY_SPLIT_ACN > 1 ? $NumSplit - ECOMMERCE_PAY_SPLIT_ACN : $NumSplit);
                $DiffParc = round(($Total * getFactor($ParcRest) * $ParcRest) - $Total, 2);
                $SplitPrice = number_format($ParcSj + ($DiffParc / $NumSplit), '2', ',', '.');
            endif;
            $jSON['total'] .= "<p>ou <span>{$NumSplit}x de R$ {$SplitPrice}</span></p>";
        endif;
        break;

    //CART ADD ITEM ON CLICK +
    case 'wc_cart_add':
        if (empty($_SESSION['wc_order'])):
            $_SESSION['wc_order'] = array();
        endif;

        $Read->FullRead("SELECT pdt_title, pdt_inventory FROM " . DB_PDT . " WHERE pdt_id = :id", "id={$POST['pdt_id']}");
        $CartPDT = $Read->getResult();

        if (!$POST['item_amount']):
            $jSON['trigger'] = AjaxErro("<b>OPPSSS:</b> Desculpa, mas <b>{$POST['item_amount']}</b> não é uma quantidade válida para adiconar ao carrinho!", E_USER_NOTICE);
        elseif (!$Read->getResult()):
            $jSON['trigger'] = AjaxErro("<b>OPPSSS:</b> O produto solicitado não foi encontrado. Por favor, tente novamente!", E_USER_NOTICE);
        elseif ($CartPDT[0]['pdt_inventory'] < 1):
            $jSON['trigger'] = AjaxErro("<b>Desculpe:</b> No momento estamos sem estoque para o produto {$CartPDT[0]['pdt_title']}. Mas temos outras opções!", E_USER_NOTICE);
        else:
            if (empty($_SESSION['wc_order'][$POST['stock_id']])):
                $_SESSION['wc_order'][$POST['stock_id']] = $POST['item_amount'];
            else:
                $_SESSION['wc_order'][$POST['stock_id']] += $POST['item_amount'];
            endif;

            $CartTotal = 0;
            foreach ($_SESSION['wc_order'] as $ItemId => $ItemAmount):
                $Read->FullRead("SELECT pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_id = (SELECT pdt_id FROM " . DB_PDT_STOCK . " WHERE stock_id = :id)", "status=1&id={$ItemId}");
                if ($Read->getResult()):
                    $ItemPrice = ($Read->getResult()[0]['pdt_offer_price'] && $Read->getResult()[0]['pdt_offer_start'] <= date('Y-m-d H:i:s') && $Read->getResult()[0]['pdt_offer_end'] >= date('Y-m-d H:i:s') ? $Read->getResult()[0]['pdt_offer_price'] : $Read->getResult()[0]['pdt_price']);
                    $CartTotal += $ItemPrice * $ItemAmount;
                endif;
            endforeach;

            /* CUSTOM BY ALISSON */
            if (isset($_SESSION['wc_cupom'])):
                $couponCode = $_SESSION['wc_cupom_code'];
                $Read->ExeRead(DB_PDT_COUPONS, "WHERE cp_coupon = :cp", "cp={$couponCode}");
                if ($Read->getResult()):
                    $dataCoupon = $Read->getResult()[0];
                    if ($CartTotal > $dataCoupon['cp_minimum']):
                        $_SESSION['wc_cupom'] = $dataCoupon['cp_discount'];
                        $_SESSION['wc_cupom_code'] = $dataCoupon['cp_coupon'];
                    endif;
                endif;
            endif;

            /* CUSTOM BY ALISSON */
            if (ECOMMERCE_ABANDONED_CART && !empty($_SESSION['userLogin'])):
                $Read->FullRead('SELECT stock_id, abandoned_amount FROM ' . DB_PDT_ABANDONED_CART . ' WHERE stock_id = :stock AND user_id = :user', "stock={$POST['stock_id']}&user={$_SESSION['userLogin']['user_id']}");
                if ($Read->getResult()):
                    $Data = ['abandoned_amount' => $Read->getResult()[0]['abandoned_amount'] + $POST['item_amount'], 'abandoned_notify' => '0'];
                    $Update->ExeUpdate(DB_PDT_ABANDONED_CART, $Data, 'WHERE stock_id = :stock AND user_id = :user', "stock={$POST['stock_id']}&user={$_SESSION['userLogin']['user_id']}");
                else:
                    $Data = ['user_id' => $_SESSION['userLogin']['user_id'], 'stock_id' => $POST['stock_id'], 'abandoned_amount' => $POST['item_amount'], 'abandoned_notify' => '0'];
                    $Create->ExeCreate(DB_PDT_ABANDONED_CART, $Data);
                endif;
            endif;

            $CartCupom = (!empty($_SESSION['wc_cupom']) ? intval($_SESSION['wc_cupom']) : 0);
            $CartPrice = (empty($_SESSION['wc_cupom']) ? $CartTotal : $CartTotal * ((100 - $_SESSION['wc_cupom']) / 100));

            $jSON['cart_price'] = number_format($CartPrice, 2, ',', '.');

            //STOCK CONTROL
            $Read->FullRead("SELECT stock_inventory FROM " . DB_PDT_STOCK . " WHERE stock_id = :id", "id={$POST['stock_id']}");
            if ($Read->getResult()[0]['stock_inventory'] <= $_SESSION['wc_order'][$POST['stock_id']] && $Read->getResult()[0]['stock_inventory']):
                $_SESSION['wc_order'][$POST['stock_id']] = intval($Read->getResult()[0]['stock_inventory']);
            endif;

            $jSON['cart_product'] = $CartPDT[0]['pdt_title'];
        endif;

        $jSON['cart_amount'] = count($_SESSION['wc_order']);
        break;

    //CART REMOVE ON CLICK X
    case 'wc_cart_remove':
        unset($_SESSION['wc_order'][$POST['stock_id']]);

        $CartTotal = 0;
        foreach ($_SESSION['wc_order'] as $ItemId => $ItemAmount):
            $Read->FullRead("SELECT pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_id = (SELECT pdt_id FROM " . DB_PDT_STOCK . " WHERE stock_id = :id)", "id={$ItemId}");
            if (!$Read->getResult()):
                unset($_SESSION['wc_order'][$ItemId]);
            else:
                extract($Read->getResult()[0]);
                $CartTotal += ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s') ? $pdt_offer_price : $pdt_price) * $ItemAmount;
            endif;
        endforeach;

        /* CUSTOM BY ALISSON */
        if (isset($_SESSION['wc_cupom'])):
            $couponCode = $_SESSION['wc_cupom_code'];
            $Read->ExeRead(DB_PDT_COUPONS, "WHERE cp_coupon = :cp", "cp={$couponCode}");
            if ($Read->getResult()):
                $dataCoupon = $Read->getResult()[0];
                if ($CartTotal < $dataCoupon['cp_minimum']):
                    $_SESSION['wc_cupom'] = 0;
                    $jSON['trigger'] = AjaxErro("OPPSSS, para que o cupom <b>{$dataCoupon['cp_title']}</b> com <b>{$dataCoupon['cp_discount']}% de desconto</b> seja aplicado, o <b>SUB-TOTAL</b> do seu carrinho deve ter no mínimo <b>R$ " . number_format($dataCoupon['cp_minimum'], 2, ',', '.') . "</b> em compras :)", E_USER_WARNING);
                endif;
            endif;
        endif;

        /* CUSTOM BY ALISSON */
        if (ECOMMERCE_ABANDONED_CART && !empty($_SESSION['userLogin'])):
            $Read->ExeRead(DB_PDT_ABANDONED_CART, 'WHERE stock_id = :stock AND user_id = :user', "stock={$POST['stock_id']}&user={$_SESSION['userLogin']['user_id']}");
            if ($Read->getResult()):
                $Delete->ExeDelete(DB_PDT_ABANDONED_CART, 'WHERE stock_id = :stock AND user_id = :user', "stock={$POST['stock_id']}&user={$_SESSION['userLogin']['user_id']}");
            endif;
        endif;

        $CartPrice = (empty($_SESSION['wc_cupom']) ? $CartTotal : $CartTotal * ((100 - $_SESSION['wc_cupom']) / 100));
        $CartShipment = (!empty($_SESSION['wc_shipment']['wc_shipprice']) ? $_SESSION['wc_shipment']['wc_shipprice'] : 0);
        $jSON['cart_total'] = number_format($CartTotal, '2', ',', '.');
        $jSON['cart_price'] = number_format($CartPrice + $CartShipment, '2', ',', '.');
        $jSON['cart_amount'] = count($_SESSION['wc_order']);
        break;

    //CART CHANGE PLUS AND LESS BUTTONS
    case 'wc_cart_change':
        $_SESSION['wc_order'][$POST['stock_id']] = $POST['item_amount'];

        $Read->FullRead("SELECT pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_id = (SELECT pdt_id FROM " . DB_PDT_STOCK . " WHERE stock_id = :id)", "id={$POST['stock_id']}");
        if ($Read->getResult()):
            $ItemData = $Read->getResult()[0];
            $ItemDataPrice = ($ItemData['pdt_offer_price'] && $ItemData['pdt_offer_start'] <= date('Y-m-d H:i:s') && $ItemData['pdt_offer_end'] >= date('Y-m-d H:i:s') ? $ItemData['pdt_offer_price'] : $ItemData['pdt_price']);
            $jSON['cart_item'] = "R$ " . number_format($ItemDataPrice * $POST['item_amount'], '2', ',', '.');
        endif;

        $CartTotal = 0;
        foreach ($_SESSION['wc_order'] as $ItemId => $ItemAmount):
            $Read->FullRead("SELECT pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_id = (SELECT pdt_id FROM " . DB_PDT_STOCK . " WHERE stock_id = :id AND stock_inventory >= 1)", "id={$ItemId}");
            if (!$Read->getResult()):
                unset($_SESSION['wc_order'][$ItemId]);
            else:
                extract($Read->getResult()[0]);
                $CartTotal += ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s') ? $pdt_offer_price : $pdt_price) * $ItemAmount;
            endif;
        endforeach;

        /* CUSTOM BY ALISSON */
        if (ECOMMERCE_ABANDONED_CART && !empty($_SESSION['userLogin'])):
            $Read->FullRead('SELECT stock_id, abandoned_amount FROM ' . DB_PDT_ABANDONED_CART . ' WHERE stock_id = :stock AND user_id = :user', "stock={$POST['stock_id']}&user={$_SESSION['userLogin']['user_id']}");
            if ($Read->getResult()):
                $Data = ['abandoned_amount' => $POST['item_amount'], 'abandoned_notify' => '0'];
                $Update->ExeUpdate(DB_PDT_ABANDONED_CART, $Data, 'WHERE stock_id = :stock AND user_id = :user', "stock={$POST['stock_id']}&user={$_SESSION['userLogin']['user_id']}");
            endif;
        endif;

        /* CUSTOM BY ALISSON */
        if (isset($_SESSION['wc_cupom'])):
            $couponCode = $_SESSION['wc_cupom_code'];
            $Read->ExeRead(DB_PDT_COUPONS, "WHERE cp_coupon = :cp", "cp={$couponCode}");
            if ($Read->getResult()):
                $dataCoupon = $Read->getResult()[0];
                if ($CartTotal < $dataCoupon['cp_minimum']):
                    $_SESSION['wc_cupom'] = 0;
                    $jSON['trigger'] = AjaxErro("OPPSSS, para que o cupom <b>{$dataCoupon['cp_title']}</b> com <b>{$dataCoupon['cp_discount']}% de desconto</b> seja aplicado, o <b>SUB-TOTAL</b> do seu carrinho deve ter no mínimo <b>R$ " . number_format($dataCoupon['cp_minimum'], 2, ',', '.') . "</b> em compras :)", E_USER_WARNING);
                else:
                    if (!$_SESSION['wc_cupom']):
                        $jSON['trigger'] = AjaxErro("Parabéns, o seu cupom <b>{$dataCoupon['cp_title']}</b> com <b>{$dataCoupon['cp_discount']}% de desconto</b> foi aplicado com sucesso :)");
                    endif;

                    $_SESSION['wc_cupom'] = $dataCoupon['cp_discount'];
                    $_SESSION['wc_cupom_code'] = $dataCoupon['cp_coupon'];
                endif;
            endif;
        endif;

        $CartPrice = (empty($_SESSION['wc_cupom']) ? $CartTotal : $CartTotal * ((100 - $_SESSION['wc_cupom']) / 100));
        $CartShipment = (!empty($_SESSION['wc_shipment']['wc_shipprice']) ? $_SESSION['wc_shipment']['wc_shipprice'] : 0);
        $jSON['cart_total'] = number_format($CartTotal, '2', ',', '.');
        $jSON['cart_price'] = number_format($CartPrice + $CartShipment, '2', ',', '.');
        break;

    //ADD CUPOM
    case 'cart_cupom':
        /* CUSTOM BY ALISSON */
        $couponCode = $POST['cupom_id'];
        $Read->ExeRead(DB_PDT_COUPONS, "WHERE cp_coupon = :cp", "cp={$couponCode}");
        if (!$Read->getResult()):
            $jSON['trigger'] = AjaxErro("<b>OPPSSS:</b> O cupom <b>{$couponCode}</b> não existe!", E_USER_ERROR);
        else:
            $dataCoupon = $Read->getResult()[0];
            $date = date('d/m', strtotime($dataCoupon['cp_start']));
            $hours = date('H:i', strtotime($dataCoupon['cp_start']));

            if ($dataCoupon['cp_start'] >= date('Y-m-d H:i:s')):
                $jSON['trigger'] = AjaxErro("<b>OPPSSS:</b> O cupom <b>{$dataCoupon['cp_coupon']}</b> apenas será válido a partir de <b>{$date}</b> às <b>{$hours}</b> horário de Brasília!", E_USER_WARNING);
            elseif ($dataCoupon['cp_end'] <= date('Y-m-d H:i:s')):
                $jSON['trigger'] = AjaxErro("<b>OPPSSS:</b> O cupom <b>{$dataCoupon['cp_coupon']}</b> expirou na data <b>{$date}</b> às <b>{$hours}</b> horário de Brasília!", E_USER_WARNING);
            else:
                $CartTotal = 0;
                foreach ($_SESSION['wc_order'] as $ItemId => $ItemAmount):
                    $Read->FullRead("SELECT pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_id = (SELECT pdt_id FROM " . DB_PDT_STOCK . " WHERE stock_id = :id AND stock_inventory >= 1)", "id={$ItemId}");
                    if (!$Read->getResult()):
                        unset($_SESSION['wc_order'][$ItemId]);
                    else:
                        extract($Read->getResult()[0]);
                        $CartTotal += ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s') ? $pdt_offer_price : $pdt_price) * $ItemAmount;
                    endif;
                endforeach;

                if ($CartTotal < $dataCoupon['cp_minimum']):
                    $jSON['trigger'] = AjaxErro("OPPSSS, para que o cupom <b>{$dataCoupon['cp_title']}</b> com <b>{$dataCoupon['cp_discount']}% de desconto</b> seja aplicado, o <b>SUB-TOTAL</b> do seu carrinho deve ter no mínimo <b>R$ " . number_format($dataCoupon['cp_minimum'], 2, ',', '.') . "</b> em compras :)", E_USER_WARNING);
                else:
                    $_SESSION['wc_cupom'] = $dataCoupon['cp_discount'];
                    $_SESSION['wc_cupom_code'] = $dataCoupon['cp_coupon'];

                    $UpdateCupom = ['cp_hits' => $dataCoupon['cp_hits'] + 1];
                    $Update->ExeUpdate(DB_PDT_COUPONS, $UpdateCupom, "WHERE cp_id = :cp", "cp={$dataCoupon['cp_id']}");

                    $CartPrice = (empty($_SESSION['wc_cupom']) ? $CartTotal : $CartTotal * ((100 - $_SESSION['wc_cupom']) / 100));
                    $CartShipment = (!empty($_SESSION['wc_shipment']['wc_shipprice']) ? $_SESSION['wc_shipment']['wc_shipprice'] : 0);

                    $jSON['cart_cupom'] = (!empty($_SESSION['wc_cupom']) ? $_SESSION['wc_cupom'] : 0);
                    $jSON['cart_price'] = number_format($CartPrice + $CartShipment, '2', ',', '.');
                    $jSON['trigger'] = AjaxErro("Parabéns, o seu cupom <b>{$dataCoupon['cp_title']}</b> com <b>{$dataCoupon['cp_discount']}% de desconto</b> foi aplicado com sucesso :)");
                endif;
            endif;
        endif;
        break;

    //CART SHIPMENT CALCULATE TO HIT CEP
    case 'cart_shipment':
        $CartTotal = 0;
        $VolumeTotal = 0;
        $WeightTotal = 0;
        $AmountTotal = 0;
        $xl = 0;
        $size = 0;
        $handlingFee = 0;
        foreach ($_SESSION['wc_order'] as $ItemId => $ItemAmount):
            $Read->ExeRead(DB_PDT, "WHERE pdt_id = (SELECT pdt_id FROM " . DB_PDT_STOCK . " WHERE stock_id = :id)",
                "id={$ItemId}");
            if (!$Read->getResult()):
                unset($_SESSION['wc_order'][$ItemId]);
            else:
                extract($Read->getResult()[0]);
                $CartTotal += ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s') ? $pdt_offer_price : $pdt_price) * $ItemAmount;
                $VolumeTotal += ($pdt_dimension_width / 100) * ($pdt_dimension_depth / 100) * ($pdt_dimension_heigth / 100) * $ItemAmount;
                $WeightTotal += ($pdt_dimension_weight / 1000) * $ItemAmount;
                $AmountTotal += $ItemAmount;
                $size += (($pdt_dimension_width / 100) + ($pdt_dimension_depth / 100) + ($pdt_dimension_heigth / 100)) * $ItemAmount;
                if (!ECOMMERCE_SHIPMENT_CORREIOS_BY_WEIGHT && ($pdt_dimension_width > 105 || $pdt_dimension_depth > 105 || $pdt_dimension_heigth > 105)):
                    $xl = 1;
                endif;
                if ($pdt_dimension_width > 70 || $pdt_dimension_depth > 70 || $pdt_dimension_heigth > 70):
                    $handlingFee = 1;
                endif;
            endif;
        endforeach;

        /* Shipping Rule */
        /* CUSTOM BY ALISSON */
        $url = curl_init("https://viacep.com.br/ws/{$POST['zipcode']}/json/");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        $cep = json_decode(curl_exec($url));

        if (!empty($cep)) {
            $uf = null;
            $city = null;
            $district = null;
            $jSON['cart_shipment'] = null;

            /* state */
            $Read->ExeRead(
                DB_FREIGHTS,
                'WHERE uf = :uf AND city IS NULL AND district IS NULL',
                "uf={$cep->uf}"
            );

            if ($Read->getResult()):
                $uf = true;

                if ($Read->getResult()[0]['status'] == '0'):
                    $jSON['trigger'] = AjaxErro(
                        "<b>Desculpe</b>! Não realizamos entrega para o seu estado",
                        E_USER_WARNING
                    );

                    break;
                endif;

                $jSON['cart_shipment'] = "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='{$Read->getResult()[0]['price']}' type='radio' id='10003'/> De 01 a {$Read->getResult()[0]['days']} dias úteis - <b>R$ " . number_format($Read->getResult()[0]['price'], 2, ',', '.') . "</b></label>";
            endif;

            /* city */
            $Read->ExeRead(
                DB_FREIGHTS,
                'WHERE uf = :uf AND city = :city AND district IS NULL',
                "uf={$cep->uf}&city={$cep->localidade}"
            );

            if ($Read->getResult()):
                $city = true;

                if ($Read->getResult()[0]['status'] == '0'):
                    $jSON['trigger'] = AjaxErro(
                        "<b>Desculpe</b>! Não realizamos entrega para a sua cidade",
                        E_USER_WARNING
                    );

                    $jSON['cart_shipment'] = null;
                    break;
                endif;

                $jSON['cart_shipment'] = "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='{$Read->getResult()[0]['price']}' type='radio' id='10003'/> De 01 a {$Read->getResult()[0]['days']} dias úteis - <b>R$ " . number_format($Read->getResult()[0]['price'], 2, ',', '.') . "</b></label>";
            endif;

            /* district */
            $Read->ExeRead(
                DB_FREIGHTS,
                'WHERE uf = :uf AND city = :city AND district = :district',
                "uf={$cep->uf}&city={$cep->localidade}&district={$cep->bairro}"
            );

            if ($Read->getResult()):
                $district = true;

                if ($Read->getResult()[0]['status'] == '0'):
                    $jSON['trigger'] = AjaxErro(
                        "<b>Desculpe</b>! Não realizamos entrega para o seu bairro",
                        E_USER_WARNING
                    );

                    $jSON['cart_shipment'] = null;
                    break;
                endif;

                $jSON['cart_shipment'] = "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='{$Read->getResult()[0]['price']}' type='radio' id='10003'/> De 01 a {$Read->getResult()[0]['days']} dias úteis - <b>R$ " . number_format($Read->getResult()[0]['price'], 2, ',', '.') . "</b></label>";
            endif;

            if (ECOMMERCE_SHIPMENT_LOCAL_IN_PLACE):
                $jSON['cart_shipment'] .= "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='0' type='radio' id='10005'/> Retirar na Loja: <b>R$ 0,00</b></label>";
            endif;

            $CartPrice = (empty($_SESSION['wc_cupom']) ? $CartTotal : $CartTotal * ((100 - $_SESSION['wc_cupom']) / 100));
            if (ECOMMERCE_SHIPMENT_FREE && $CartPrice > ECOMMERCE_SHIPMENT_FREE):
                $jSON['cart_shipment'] .= "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='0' type='radio' id='10002'/> Envio Gratuito: De 01 a " . str_pad(ECOMMERCE_SHIPMENT_DELAY + ECOMMERCE_SHIPMENT_FREE_DAYS, 2, 0, 0) . " dias úteis - <b>R$ 0,00</b></label>";
            endif;

            if ($uf || $city || $district):
                $_SESSION['wc_shipment_zip'] = $POST['zipcode'];
                break;
            endif;
        }

        if (!ECOMMERCE_SHIPMENT_CORREIOS_BY_WEIGHT && $size > 2):
            $xl = 1;
        endif;

        $vlMercadoria = number_format($CartTotal, '2', '.', '');
        $totalweight = floatval($WeightTotal);
        $totalvolume = number_format($VolumeTotal, '4', '.', '');

        //AUTO INSTANCE OBJECT TNT
        if (empty($Tnt) && ECOMMERCE_SHIPMENT_TNT_QUOTE == 1):
            $Tnt = new Tnt(ECOMMERCE_SHIPMENT_TNT_LOGIN, ECOMMERCE_SHIPMENT_TNT_SENHA, ECOMMERCE_SHIPMENT_TNT_CDDIVISAOCLIENTE, ECOMMERCE_SHIPMENT_TNT_TPPESSOAREMETENTE, ECOMMERCE_SHIPMENT_TNT_TPSITUACAOTRIBUTARIAREMETENTE, SITE_ADDR_CNPJ, SITE_ADDR_IE, SITE_ADDR_ZIP);
        endif;

        //AUTO INSTANCE OBJECT JAMEF
        if (empty($Jamef) && ECOMMERCE_SHIPMENT_JAMEF_QUOTE == 1):
            $Jamef = new Jamef(SITE_ADDR_CNPJ, SITE_ADDR_CITY, SITE_ADDR_UF, ECOMMERCE_SHIPMENT_JAMEF_FILCOT, ECOMMERCE_SHIPMENT_JAMEF_USUARIO);
        endif;

        //AUTO INSTANCE OBJECT JADLOG
        if (empty($Jadlog) && ECOMMERCE_SHIPMENT_JADLOG_QUOTE == 1):
            $Jadlog = new Jadlog(ECOMMERCE_SHIPMENT_JADLOG_PASSWORD, SITE_ADDR_CNPJ, SITE_ADDR_ZIP);
        endif;

        //AUTO INSTANCE OBJECT CORREIOS
        if (empty($Correios) && ECOMMERCE_SHIPMENT_CORREIOS_QUOTE == 1):
            $Correios = new CorreiosCurl(SITE_ADDR_ZIP, ECOMMERCE_SHIPMENT_CORREIOS_CDEMPRESA, ECOMMERCE_SHIPMENT_CORREIOS_CDSENHA);
        endif;

        $jSON['cart_shipment'] = null;

        if (ECOMMERCE_SHIPMENT_TNT_QUOTE == 1):
            $Tnt->setQuoteData($POST['zipcode'], $totalweight, $vlMercadoria, $totalvolume, ECOMMERCE_SHIPMENT_TNT_TPFRETE, ECOMMERCE_SHIPMENT_TNT_TPSERVICO, 'F', 'NC', '12345', '', ECOMMERCE_SHIPMENT_TNT_BY_WEIGHT, ECOMMERCE_SHIPMENT_ADDITIONAL_PERCENT, ECOMMERCE_SHIPMENT_ADDITIONAL_CHARGE, ECOMMERCE_SHIPMENT_DELAY);
            $TntRetorno = $Tnt->getQuote();
            if ($TntRetorno['status'] === 'OK'):
                $jSON['cart_shipment'] .= "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='" . $TntRetorno['valorfrete'] . "' type='radio' id='{$TntRetorno['shipcode']}'/> " . getShipmentTag(intval($TntRetorno['shipcode'])) . " - R$ " . number_format($TntRetorno['valorfrete'], '2', ',', '') . " - {$TntRetorno['prazoentrega']} dias úteis</label>";
            endif;
        endif;

        if (ECOMMERCE_SHIPMENT_JAMEF_QUOTE == 1):
            $Jamef->setQuoteData($POST['zipcode'], $totalweight, $vlMercadoria, $totalvolume, ECOMMERCE_SHIPMENT_JAMEF_TIPTRA, ECOMMERCE_SHIPMENT_JAMEF_SEGPROD, ECOMMERCE_SHIPMENT_JAMEF_BY_WEIGHT, ECOMMERCE_SHIPMENT_ADDITIONAL_PERCENT, ECOMMERCE_SHIPMENT_ADDITIONAL_CHARGE, ECOMMERCE_SHIPMENT_DELAY);
            $JamefRetorno = $Jamef->getQuote();
            if ($JamefRetorno['status'] === 'OK'):
                $jSON['cart_shipment'] .= "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='" . $JamefRetorno['valorfrete'] . "' type='radio' id='{$JamefRetorno['shipcode']}'/> " . getShipmentTag(intval($JamefRetorno['shipcode'])) . " - R$ " . number_format($JamefRetorno['valorfrete'], '2', ',', '') . " - {$JamefRetorno['prazoentrega']} dias úteis</label>";
            endif;
        endif;

        if (ECOMMERCE_SHIPMENT_JADLOG_QUOTE == 1):
            $Jadlog->setQuoteData($POST['zipcode'], $totalweight, $vlMercadoria, $totalvolume, ECOMMERCE_SHIPMENT_JADLOG_FRAP, ECOMMERCE_SHIPMENT_JADLOG_TIPENTREGA, ECOMMERCE_SHIPMENT_JADLOG_VLCOLETA, ECOMMERCE_SHIPMENT_JADLOG_MODALIDADE, ECOMMERCE_SHIPMENT_JADLOG_SEGURO, ECOMMERCE_SHIPMENT_JADLOG_BY_WEIGHT, ECOMMERCE_SHIPMENT_ADDITIONAL_PERCENT, ECOMMERCE_SHIPMENT_ADDITIONAL_CHARGE);
            $JadlogRetorno = $Jadlog->getQuote();
            if ($JadlogRetorno['status'] === 'OK'):
                $jSON['cart_shipment'] .= "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='" . $JadlogRetorno['valorfrete'] . "' type='radio' id='{$JadlogRetorno['shipcode']}'/> " . getShipmentTag(intval($JadlogRetorno['shipcode'])) . " - R$ " . number_format($JadlogRetorno['valorfrete'], '2', ',', '') . " - " . ECOMMERCE_SHIPMENT_JADLOG_DAYS . " dias úteis</label>";
            endif;
        endif;

        if (ECOMMERCE_SHIPMENT_CORREIOS_QUOTE == 1 && $xl == 0):
            $additionalCharge = ECOMMERCE_SHIPMENT_ADDITIONAL_CHARGE;
            if ($handlingFee) {
                $additionalCharge += 79.00;
            }
            $Correios->setQuoteData($POST['zipcode'], $totalweight, $vlMercadoria, $totalvolume, ECOMMERCE_SHIPMENT_CORREIOS_SERVICE, ECOMMERCE_SHIPMENT_CORREIOS_FORMAT, ECOMMERCE_SHIPMENT_CORREIOS_OWN_HAND, ECOMMERCE_SHIPMENT_CORREIOS_ALERT, ECOMMERCE_SHIPMENT_CORREIOS_DECLARE, ECOMMERCE_SHIPMENT_CORREIOS_BY_WEIGHT, ECOMMERCE_SHIPMENT_ADDITIONAL_PERCENT, $additionalCharge, ECOMMERCE_SHIPMENT_DELAY);
            $CorreiosRetorno = $Correios->getQuote();
            if (!array_key_exists('shipcode', $CorreiosRetorno)):
                foreach ($CorreiosRetorno as $modalidade):
                    if ($modalidade['status'] === 'OK'):
                        $jSON['cart_shipment'] .= "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='" . $modalidade['valorfrete'] . "' type='radio' id='{$modalidade['shipcode']}'/> " . getShipmentTag(intval($modalidade['shipcode'])) . " - R$ " . number_format($modalidade['valorfrete'], '2', ',', '') . " - {$modalidade['prazoentrega']} dias úteis</label>";
                    endif;
                endforeach;
            else:
                if ($CorreiosRetorno['status'] === 'OK'):
                    $jSON['cart_shipment'] .= "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='" . $CorreiosRetorno['valorfrete'] . "' type='radio' id='{$CorreiosRetorno['shipcode']}'/> " . getShipmentTag(intval($CorreiosRetorno['shipcode'])) . " - R$ " . number_format($CorreiosRetorno['valorfrete'], '2', ',', '') . " - {$CorreiosRetorno['prazoentrega']} dias úteis</label>";
                endif;
            endif;
        endif;

        $CompanyPrice = $CartTotal * (ECOMMERCE_SHIPMENT_COMPANY_VAL / 100);
        if (ECOMMERCE_SHIPMENT_COMPANY && $CompanyPrice >= ECOMMERCE_SHIPMENT_COMPANY_PRICE && empty($ErroZip)):
            $jSON['cart_shipment'] .= "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='{$CompanyPrice}' type='radio' id='10001'/> Envio Padrão: 01 a " . str_pad(ECOMMERCE_SHIPMENT_DELAY + ECOMMERCE_SHIPMENT_COMPANY_DAYS, 2, 0, 0) . " dias úteis - R$ " . number_format($CompanyPrice, '2', ',', '.') . "</label>";
        endif;

        $CartPrice = (empty($_SESSION['wc_cupom']) ? $CartTotal : $CartTotal * ((100 - $_SESSION['wc_cupom']) / 100));
        if (ECOMMERCE_SHIPMENT_FREE && $CartPrice > ECOMMERCE_SHIPMENT_FREE && empty($ErroZip)):
            $jSON['cart_shipment'] .= "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='0' type='radio' id='10002'/> Envio Gratuito: 01 a " . str_pad(ECOMMERCE_SHIPMENT_DELAY + ECOMMERCE_SHIPMENT_FREE_DAYS, 2, 0, 0) . " dias úteis - R$ 0,00</label>";
        endif;

        if (ECOMMERCE_SHIPMENT_FIXED):
            $jSON['cart_shipment'] .= "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='" . ECOMMERCE_SHIPMENT_FIXED_PRICE . "' type='radio' id='10003'/> Frete Fixo: 01 a " . str_pad(ECOMMERCE_SHIPMENT_DELAY + ECOMMERCE_SHIPMENT_FIXED_DAYS, 2, 0, 0) . " dias úteis - R$ " . number_format(ECOMMERCE_SHIPMENT_FIXED_PRICE, 2, ',', '.') . "</label>";
        endif;

        if (ECOMMERCE_SHIPMENT_LOCAL):
            $City = json_decode(file_get_contents("https://viacep.com.br/ws/" . str_replace('-', '', $POST['zipcode']) . "/json/"));
            if (!empty($City) && !empty($City->localidade) && $City->localidade == ECOMMERCE_SHIPMENT_LOCAL):
                $jSON['cart_shipment'] = "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='" . ECOMMERCE_SHIPMENT_LOCAL_PRICE . "' type='radio' id='10004'/> Taxa de entrega: R$ " . number_format(ECOMMERCE_SHIPMENT_LOCAL_PRICE, 2, ',', '.') . "</label>";
            endif;

            if (ECOMMERCE_SHIPMENT_LOCAL_IN_PLACE):
                $jSON['cart_shipment'] .= "<label class='shiptag'><input required class='wc_shipment' name='shipment' value='0' type='radio' id='10005'/> Retirar na Loja: R$ 0,00</label>";
            endif;
        endif;

        if (empty($jSON['cart_shipment']) && empty($ErroZip)):
            $jSON['trigger'] = AjaxErro("<b>OPPSSS:</b> Não existem opções de entrega para o pedido autal. Você pode remover ou adicionar alguns produtos para tentar novamente!<p>Ou caso queira, entre em contato para que possamos te ajudar!</p><p>Fone: " . SITE_ADDR_PHONE_A . "<br>E-mail: " . SITE_ADDR_EMAIL . "</p>", E_USER_WARNING);
        elseif (empty($ErroZip)):
            $_SESSION['wc_shipment_zip'] = $POST['zipcode'];
        endif;
        break;

    //SHIPMENT CALCULATE TO SELECT SHIP
    case 'cart_shipment_select':
        $_SESSION['wc_shipment'] = $POST;

        $CartTotal = 0;
        foreach ($_SESSION['wc_order'] as $ItemId => $ItemAmount):
            $Read->FullRead("SELECT pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_id = (SELECT pdt_id FROM " . DB_PDT_STOCK . " WHERE stock_id = :id)", "id={$ItemId}");
            if (!$Read->getResult()):
                unset($_SESSION['wc_order'][$ItemId]);
            else:
                extract($Read->getResult()[0]);
                $CartTotal += ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s') ? $pdt_offer_price : $pdt_price) * $ItemAmount;
            endif;
        endforeach;

        $CartPrice = (empty($_SESSION['wc_cupom']) ? $CartTotal : $CartTotal * ((100 - $_SESSION['wc_cupom']) / 100));
        $CartShipment = (!empty($_SESSION['wc_shipment']['wc_shipprice']) ? $_SESSION['wc_shipment']['wc_shipprice'] : 0);
        $jSON['cart_total'] = number_format($CartTotal, '2', ',', '.');
        $jSON['cart_ship'] = number_format($CartShipment, '2', ',', '.');
        $jSON['cart_price'] = number_format($CartPrice + $CartShipment, '2', ',', '.');
        break;

    //LOOK USER E-MAIL
    case 'wc_order_email':
        if (empty($POST['user_email'])):
            $jSON['error'] = "<p class='wc_order_error'>&#10008; Informe seu e-mail!</p>";
        elseif (!Check::Email($POST['user_email']) || !filter_var($POST['user_email'])):
            $jSON['error'] = "<p class='wc_order_error'>&#10008; Este não é um e-mail válido!</p>";
        else:
            $Read->FullRead("SELECT user_name, user_lastname, user_document, user_cell FROM " . DB_USERS . " WHERE user_email = :mm", "mm={$POST['user_email']}");
            if ($Read->getResult()):
                $jSON = $Read->getResult()[0];
                $jSON['user'] = true;
            else:
                $jSON['user'] = null;
            endif;
        endif;
        break;

    //USER AUTENTICATION
    case 'wc_order_user':
        if (in_array('', $POST)):
            $jSON['error'] = "<p class='wc_order_error'>&#10008; Preencha esse campo!</p>";
        elseif (!Check::Email($POST['user_email']) || !filter_var($POST['user_email'], FILTER_VALIDATE_EMAIL)):
            $jSON['field'] = 'user_email';
            $jSON['error'] = "<p class='wc_order_error'>&#10008; Este não é um e-mail válido!</p>";
        elseif (!empty($POST['user_document']) && !Check::CPF($POST['user_document'])):
            $jSON['field'] = 'user_document';
            $jSON['error'] = "<p class='wc_order_error'>&#10008; Este não é um CPF válido!</p>";
        elseif (strlen($POST['user_password']) < 5 || strlen($POST['user_password']) > 11):
            $jSON['field'] = 'user_password';
            $jSON['error'] = "<p class='wc_order_error'>&#10008; A senha deve ter entre 5 e 11 caracteres!</p>";
        else:
            $Read->FullRead("SELECT user_id FROM " . DB_USERS . " WHERE user_email = :mm", "mm={$POST['user_email']}");
            if (!$Read->getResult()):
                $Read->FullRead("SELECT user_email FROM " . DB_USERS . " WHERE user_document = :dc", "dc={$POST['user_document']}");
                if ($Read->getResult()):
                    $jSON['field'] = 'user_document';
                    $jSON['error'] = "<p class='wc_order_error'>&#10008; CPF já cadastrado em <b>{$Read->getResult()[0]['user_email']}</b>!</p>";
                else:
                    //CREATE NEW USER
                    $UserPassBook = str_repeat("*", strlen($POST['user_password']) - 4) . substr($POST['user_password'], strlen($POST['user_password']) - 4);
                    $POST['user_password'] = hash('sha512', $POST['user_password']);
                    $POST['user_channel'] = 'Novo pedido';
                    $POST['user_registration'] = date('Y-m-d H:i:s');
                    $POST['user_level'] = 1;

                    $Create->ExeCreate(DB_USERS, $POST);
                    $POST['user_id'] = $Create->getResult();
                    $_SESSION['userLogin'] = $POST;

                    //SEND CREATE ACCOUNT
                    require_once 'cart.email.php';
                    $BodyMail = "
                        <p style='font-size: 1.3em'>Caro(a) {$POST['user_name']},</p>
                        <p>Este e-mail é para dar a você as boas vindas a nosso site!</p>
                        <p>Uma nova conta foi criada para que você possa ter mais comodidade e agilidade ao interagir conosco. Ao logar-se em sua conta você pode:</p>
                        <p>
                        ✓ Atualizar seus dados pessoais!<br>
                        ✓ Acompanhar o andamento dos seus pedidos!<br>
                        ✓ Realizar novos pedidos com mais agilidade!<br>
                        ✓ Ter acesso a ofertas exclusivas do site por e-mail!
                        </p>
                        <p>Confira abaixo os dados de acesso a sua conta:</p>
                        <p style='font-size: 1.1em'>
                            Login: {$POST['user_email']}<br>
                            Senha: {$UserPassBook}<br>
                        </p>
                        <p><a title='Minha Conta' target='_blank' href='" . BASE . "/conta/login'>Acessar Minha Conta!</a></p>
                        <p>Ao acessar nosso site você pode usar esses dados para identificar-se, e assim ter acesso ao melhor do nosso conteúdo...</p>
                        <p><b>Seja muito bem-vindo(a) {$POST['user_name']}...</b></p>
                        <p><i>Atenciosamente, " . SITE_NAME . "!</i></p>
                    ";
                    $Mensagem = str_replace('#mail_body#', $BodyMail, $MailContent);
                    $SendEmail = new Email;
                    $SendEmail->EnviarMontando("Seja bem-vindo(a) {$POST['user_name']}", $Mensagem, SITE_NAME, MAIL_USER, "{$POST['user_name']} {$POST['user_lastname']}", $POST['user_email']);
                    $jSON['success'] = BASE . '/pedido/endereco#cart';
                endif;
            else:
                //LOGIN USER
                $UserEmail = $POST['user_email'];
                $UserPass = hash("sha512", $POST['user_password']);
                $Read->ExeRead(DB_USERS, "WHERE user_email = :em AND user_password = :ps", "em={$UserEmail}&ps={$UserPass}");
                if ($Read->getResult()):
                    unset($POST['user_email'], $POST['user_password']);
                    $Update->ExeUpdate(DB_USERS, $POST, "WHERE user_id = :id", "id={$Read->getResult()[0]['user_id']}");
                    $_SESSION['userLogin'] = $Read->getResult()[0];
                    $jSON['success'] = BASE . '/pedido/endereco#cart';
                else:
                    $jSON['field'] = 'user_password';
                    $jSON['error'] = "<p class='wc_order_error'>&#10008; A senha informada não confere! <a title='Recuperar Senha!' href='" . BASE . "/conta/recuperar'>[ Esqueceu sua senha? ]</a></p>";
                endif;
            endif;
        endif;
        break;

    //WORK CONTROL ADDR SELECT
    case 'wc_addr_select':
        $_SESSION['wc_order_addr'] = $POST['addr_id'];
        $jSON['addr'] = $POST['addr_id'];
        break;

    //WORK CONTROL ORDER CREATE
    case 'wc_order_create':
        //ERROR KEY
        $CartError = null;
        if (empty($_SESSION['userLogin'])):
            $jSON['trigger'] = AjaxErro("<b>Erro:</b> Desculpe! Mas não foi possível obter seus dados pessoais para o pedido!<p><b>Atualize a página para tentar novamente!</b></p>", E_USER_ERROR);
            break;
        endif;

        //SHIPMENT CHECK
        if (empty($_SESSION['wc_shipment'])):
            $jSON['trigger'] = AjaxErro("<b class='icon-info'>FORMA DE ENVIO:</b> Por favor selecione uma opção de frete para prosseguir para o pagamento!</p>", E_USER_WARNING);
            break;
        endif;

        //NEW ADDR
        if (!empty($POST['addr_name'])):
            $UpdateAddr = ['addr_key' => null];
            $Update->ExeUpdate(DB_USERS_ADDR, $UpdateAddr, "WHERE user_id = :id", "id={$_SESSION['userLogin']['user_id']}");

            $AddrCheck = $POST;
            unset($AddrCheck['addr_complement']);
            if (in_array('', $AddrCheck)):
                $jSON['form_error'] = "<p class='wc_order_error'>&#10008; Preencha esse campo!</p>";
                $CartError = true;
            else:
                $NewAddr = [
                    'user_id' => $_SESSION['userLogin']['user_id'],
                    'addr_key' => 1,
                    'addr_name' => $POST['addr_name'],
                    'addr_zipcode' => $POST['addr_zipcode'],
                    'addr_street' => $POST['addr_street'],
                    'addr_number' => $POST['addr_number'],
                    'addr_complement' => (!empty($POST['addr_complement']) ? $POST['addr_complement'] : null),
                    'addr_district' => $POST['addr_district'],
                    'addr_city' => $POST['addr_city'],
                    'addr_state' => $POST['addr_state'],
                    'addr_country' => SITE_ADDR_COUNTRY
                ];
                $Create->ExeCreate(DB_USERS_ADDR, $NewAddr);
                $_SESSION['wc_order_addr'] = $Create->getResult();
            endif;
        endif;

        //ADDR CHECK
        if (empty($_SESSION['wc_order_addr'])):
            $jSON['trigger'] = AjaxErro("<b class='icon-info'>ENDEREÇO:</b> É preciso cadastrar ou selecionar um endereço para finalizar seu pedido!</p>", E_USER_NOTICE);
            break;
        endif;

        if (!$CartError):
            //ORDER AMOUNT
            $CartTotal = 0;
            foreach ($_SESSION['wc_order'] as $ItemId => $ItemAmount):
                $Read->FullRead("SELECT pdt_title, pdt_id, pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_id = (SELECT pdt_id FROM " . DB_PDT_STOCK . " WHERE stock_id = :id)", "id={$ItemId}");
                if ($Read->getResult()):
                    extract($Read->getResult()[0]);
                    $CartTotal += ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s') ? $pdt_offer_price : $pdt_price) * $ItemAmount;
                    $CartOrdeItens[] = [
                        'pdt_id' => $pdt_id,
                        'stock_id' => $ItemId,
                        'item_name' => $pdt_title,
                        'item_price' => ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s') ? $pdt_offer_price : $pdt_price),
                        'item_amount' => $ItemAmount
                    ];
                endif;
            endforeach;
            $CartPrice = (empty($_SESSION['wc_cupom']) ? $CartTotal : $CartTotal * ((100 - $_SESSION['wc_cupom']) / 100));
            $CartTotalPrice = (empty($_SESSION['wc_shipment']['wc_shipprice']) ? $CartPrice : $CartPrice + $_SESSION['wc_shipment']['wc_shipprice']);

            //ORDER CREATE
            $NewOrder = [
                'user_id' => $_SESSION['userLogin']['user_id'],
                'order_status' => 3,
                'order_coupon' => (!empty($_SESSION['wc_cupom']) ? $_SESSION['wc_cupom'] : null),
                'order_price' => $CartTotalPrice,
                'order_payment' => 1,
                'order_addr' => $_SESSION['wc_order_addr'],
                'order_shipcode' => (!empty($_SESSION['wc_shipment']['wc_shipcode']) ? $_SESSION['wc_shipment']['wc_shipcode'] : null),
                'order_shipprice' => (!empty($_SESSION['wc_shipment']['wc_shipprice']) ? $_SESSION['wc_shipment']['wc_shipprice'] : null),
                'order_date' => date('Y-m-d H:i:s')
            ];
            $Create->ExeCreate(DB_ORDERS, $NewOrder);
            $OrderCreateId = $Create->getResult();

            //ORDER ITENS CREATE
            foreach ($CartOrdeItens as $Key => $Value):
                $CartOrdeItens[$Key]['order_id'] = $OrderCreateId;
            endforeach;
            $Create->ExeCreateMulti(DB_ORDERS_ITEMS, $CartOrdeItens);

            //SEND MAIL :: ORDER CREATED
            $BodyMail = "<p style='font-size: 1.2em;'>Caro(a) {$_SESSION['userLogin']['user_name']},</p>";
            $BodyMail .= "<p>Obrigado pela preferência. informamos que seu pedido #" . str_pad($OrderCreateId, 7, 0, 0) . " foi registrado com sucesso em nosso site.</p>";
            $BodyMail .= "<p>Neste momento estamos apenas esperando a confirmação do pagamento para envia-lo a você...</p>";
            $BodyMail .= "<p>Ainda não pagou? <a href='" . BASE . "/pedido/pagamento/" . base64_encode($OrderCreateId) . "#cart' title=''>PAGAR AGORA!</p></p>";
            $BodyMail .= "<p style='font-size: 1.4em;'>Confira os detalhes do seu pedido:</p>";
            $BodyMail .= "<p>Pedido: <a href='" . BASE . "/conta/pedido/{$OrderCreateId}' title='Ver pedido' target=''>" . str_pad($OrderCreateId, 7, 0, STR_PAD_LEFT) . "</a><br>Data: " . date('d/m/Y H\hi', strtotime($NewOrder['order_date'])) . "<br>Valor: R$ " . number_format($NewOrder['order_price'], '2', ',', '.') . "</p>";
            $BodyMail .= "<hr><table style='width: 100%'><tr><td>STATUS:</td><td style='color: #00AD8E; text-align: center;'>✓ Aguardando Pagamento</td><td style='color: #888888;  text-align: center;'>✓ Processando</td><td style='color: #888888; text-align: right;'>✓ Concluído</td></tr></table><hr>";
            $Read->ExeRead(DB_ORDERS_ITEMS, "WHERE order_id = :order", "order={$OrderCreateId}");
            if ($Read->getResult()):
                $i = 0;
                $ItemsPrice = 0;
                $ItemsAmount = 0;
                $BodyMail .= "<p style='font-size: 1.4em;'>Produtos:</p>";
                $BodyMail .= "<p>Abaixo você pode conferir os detalhes, quantidades e valores de cada produto adquirido em seu pedido. Confira:</p>";
                $BodyMail .= "<table style='width: 100%' border='0' cellspacing='0' cellpadding='0'>";
                foreach ($Read->getResult() as $Item):
                    /* CUSTOM BY ALISSON */
                    $Read->FullRead("SELECT (SELECT attr_size_code FROM " . DB_PDT_ATTR_SIZES . " WHERE size_id = attr_size_id) AS attr_size_code, (SELECT attr_size_title FROM " . DB_PDT_ATTR_SIZES . " WHERE size_id = attr_size_id) AS attr_size_title, (SELECT attr_color_code FROM " . DB_PDT_ATTR_COLORS . " WHERE color_id = attr_color_id) AS attr_color_code, (SELECT attr_color_title FROM " . DB_PDT_ATTR_COLORS . " WHERE color_id = attr_color_id) AS attr_color_title, (SELECT attr_print_code FROM " . DB_PDT_ATTR_PRINTS . " WHERE print_id = attr_print_id) AS attr_print_code, (SELECT attr_print_title FROM " . DB_PDT_ATTR_PRINTS . " WHERE print_id = attr_print_id) AS attr_print_title FROM " . DB_PDT_STOCK . " WHERE stock_id = :id", "id={$Item['stock_id']}");
                    //$Read->FullRead("SELECT stock_code FROM " . DB_PDT_STOCK . " WHERE stock_id = :stid", "stid={$Item['stock_id']}");
                    $PdtVariation = ($Read->getResult() && !empty($Read->getResult()[0]['attr_color_code']) && empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_print_code']) ? " <span class='wc_cart_tag'>Cor: {$Read->getResult()[0]['attr_color_title']}</span>" : ($Read->getResult() && empty($Read->getResult()[0]['attr_color_code']) && empty($Read->getResult()[0]['attr_print_code']) && !empty($Read->getResult()[0]['attr_size_code']) ? " <span class='wc_cart_tag'>Tamanho: {$Read->getResult()[0]['attr_size_title']}</span>" : ($Read->getResult() && !empty($Read->getResult()[0]['attr_color_code']) && !empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_print_code']) ? " <span class='wc_cart_tag'>Cor: {$Read->getResult()[0]['attr_color_title']}</span> <span class='wc_cart_tag'>Tamanho: {$Read->getResult()[0]['attr_size_title']}</span>" : ($Read->getResult() && !empty($Read->getResult()[0]['attr_print_code']) && empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_color_code']) ? " <span class='wc_cart_tag' title='Estampa: {$Read->getResult()[0]['attr_print_title']}' style='background-image: url(" . BASE . "/uploads/{$Read->getResult()[0]['attr_print_code']});'></span>" : ($Read->getResult() && !empty($Read->getResult()[0]['attr_print_code']) && !empty($Read->getResult()[0]['attr_size_code']) && empty($Read->getResult()[0]['attr_color_code']) ? " <span class='wc_cart_tag' title='Estampa: {$Read->getResult()[0]['attr_print_title']}' style='background-image: url(" . BASE . "/uploads/{$Read->getResult()[0]['attr_print_code']});'></span> <span class='wc_cart_tag'>Tamanho: {$Read->getResult()[0]['attr_size_title']}</span>" : '')))));
                    //$ProductSize = ($Read->getResult() && $Read->getResult()[0]['stock_code'] != 'default' ? " ({$Read->getResult()[0]['stock_code']})" : null);
                    $i++;
                    $ItemsAmount += $Item['item_amount'];
                    $ItemsPrice += $Item['item_amount'] * $Item['item_price'];

                    $pdtUnity = '';
                    $Read->LinkResult(DB_PDT, 'pdt_id', $Item['pdt_id'], 'pdt_unity');
                    if (!empty($Read->getResult()[0]['pdt_unity'])):
                        $pdtUnity = ($Item['item_amount'] >= 2 ? "{$Read->getResult()[0]['pdt_unity']}s" : $Read->getResult()[0]['pdt_unity']);
                    endif;

                    /* CUSTOM BY ALISSON */
                    $BodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>" . str_pad($i, 5, 0, STR_PAD_LEFT) . " - " . Check::Words($Item['item_name'], 5) . "{$PdtVariation}</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($Item['item_price'], '2', ',', '.') . " * <b>" . str_replace('.', ',', (float) $Item['item_amount']) . " {$pdtUnity}</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($Item['item_amount'] * $Item['item_price'], '2', ',', '.') . "</td></tr>";
                    //$BodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>" . str_pad($i, 5, 0, STR_PAD_LEFT) . " - " . Check::Words($Item['item_name'], 5) . "{$ProductSize}</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($Item['item_price'], '2', ',', '.') . " * <b>{$Item['item_amount']}</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($Item['item_amount'] * $Item['item_price'], '2', ',', '.') . "</td></tr>";
                endforeach;
                if (!empty($NewOrder['order_coupon'])):
                    $BodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Cupom:</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>{$NewOrder['order_coupon']}% de desconto</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>- <strike>R$ " . number_format($ItemsPrice * ($NewOrder['order_coupon'] / 100), '2', ',', '.') . "</strike></td></tr>";
                endif;
                $jSON['teste'] = $NewOrder['order_shipcode'] . "/" . getShipmentTag(intval($NewOrder['order_shipcode']));
                if (!empty($NewOrder['order_shipcode'])):
                    $BodyMail .= "<tr><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0;'>Frete via " . getShipmentTag(intval($NewOrder['order_shipcode'])) . "</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($NewOrder['order_shipprice'], '2', ',', '.') . " <b>* 1</b></td><td style='border-bottom: 1px solid #cccccc; padding: 10px 0 10px 0; text-align: right;'>R$ " . number_format($NewOrder['order_shipprice'], '2', ',', '.') . "</td></tr>";
                endif;
                $BodyMail .= "<tr style='background: #cccccc;'><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px;'>{$i} produto(s) no pedido</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>{$ItemsAmount} Itens</td><td style='border-bottom: 1px solid #cccccc; padding: 10px 10px 10px 10px; text-align: right;'>R$ " . number_format($NewOrder['order_price'], '2', ',', '.') . "</td></tr>";
                $BodyMail .= "</table>";
            endif;
            $BodyMail .= "<p>Qualquer dúvida não deixe de entrar em contato {$_SESSION['userLogin']['user_name']}. Obrigado por sua preferência mais uma vez...</p>";
            $BodyMail .= "<p><i>Atenciosamente " . SITE_NAME . "!</i></p>";

            require 'cart.email.php';
            $Mensagem = str_replace('#mail_body#', $BodyMail, $MailContent);
            $Email = new Email;
            $Email->EnviarMontando("Recebemos seu pedido #" . str_pad($OrderCreateId, 7, 0, 0) . "!", $Mensagem, SITE_NAME, MAIL_USER, "{$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}", $_SESSION['userLogin']['user_email']);

            //PAYMENT REDIRECT
            $jSON['redirect'] = BASE . "/pedido/pagamento/" . base64_encode($OrderCreateId) . "#cart";
        endif;
        break;
endswitch;

echo json_encode($jSON);
