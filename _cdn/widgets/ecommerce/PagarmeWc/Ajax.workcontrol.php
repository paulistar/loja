<?php
/**
 * Created by NetBeans.
 * User: ebrahimpleite
 * Date: 20/07/2018
 * Time: 15:54
 */
session_start();

$getPost = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (empty($getPost) || empty($getPost['workcontrol'])):
    die('Acesso Negado!');
endif;

$strPost = array_map('strip_tags', $getPost);
$POST = array_map('trim', $strPost);

$Action = $POST['workcontrol'];
$jSON = null;
unset($POST['workcontrol']);

require '../../../../_app/Config.inc.php';
$Read = new Read;
$Create = new Create;
$Update = new Update;

switch ($Action):
    //CARD DATA
    case 'creditCardData':
        if (empty($_SESSION['wc_payorder'])):
            $jSON['triggerError'] = '<p class="big">&#10008; Erro ao processar pagamento!</p><p class="min">Desculpe mas não foi possível verificar os dados do pedido. Por favor, experimente atualizar a página e tentar novamente!</p>';
        elseif (in_array("", $POST)):
            $jSON['error'] = "<p class='wc_order_error'>&#10008; Preencha esse campo!</p>";
        else:
            $jSON['success'] = true;
        endif;
        break;

    //CART ADD
    case 'creditCard':
        require 'Pagarme.php';

        $pagarme = new Payment\Pagarme;

        //PAYMER
        $Read->ExeRead(DB_USERS, "WHERE user_id = :usrid", "usrid={$_SESSION['wc_payorder']['user_id']}");
        $PayMer = $Read->getResult()[0];

        //ADDR
        $Read->ExeRead(DB_USERS_ADDR, "WHERE addr_id = :addid", "addid={$_SESSION['wc_payorder']['order_addr']}");
        $PayAddr = $Read->getResult()[0];

        //VERIFICA SE USUÁRIO É O MESMO DO PAGAR.ME
        if (!empty($PayMer['user_pagarme'])):
            $getCustomer = $pagarme->getCustomer($PayMer['user_pagarme']);
        else:
            $cardCpfClear = ['.', '-'];
            $Customer = $pagarme->createCustomer($PayMer['user_id'], "{$PayMer['user_name']} {$PayMer['user_lastname']}", $PayMer['user_email'], str_replace($cardCpfClear, "", $PayMer['user_document']), "+55" . substr($PayMer['user_cell'], 1, 2) . substr(str_replace('-', '', $PayMer['user_cell']), 5));
            $UpdateUser = ['user_pagarme' => $Customer->id];
            $Update->ExeUpdate(DB_USERS, $UpdateUser, "WHERE user_id = :uid", "uid={$PayMer['user_id']}");
        endif;

        //ENDEREÇO DE ENTREGA E FRETE
        $ShipmentCost = (!empty($_SESSION['wc_payorder']['order_shipprice']) ? str_replace(".", "", $_SESSION['wc_payorder']['order_shipprice']) : 0);
        $Shipping = $pagarme->shipping("{$PayMer['user_name']} {$PayMer['user_lastname']}", $ShipmentCost, $PayAddr['addr_street'], $PayAddr['addr_number'], str_replace('-', '', $PayAddr['addr_zipcode']), 'br', $PayAddr['addr_state'], $PayAddr['addr_city'], $PayAddr['addr_district'], $PayAddr['addr_complement']);

        //ORDER PRODUCTS
        $Read->ExeRead(DB_ORDERS_ITEMS, "WHERE order_id = :orid", "orid={$_SESSION['wc_payorder']['order_id']}");
        if ($Read->getResult()):
            foreach ($Read->getResult() as $SideProduct):
                $Itens[] = [
                    'id' => $SideProduct['pdt_id'],
                    'title' => $SideProduct['item_name'],
                    'unit_price' => str_replace(".", "", $SideProduct['item_price']),
                    'quantity' => $SideProduct['item_amount'],
                    'tangible' => 'true'
                ];
            endforeach;
        endif;
        $Items = $pagarme->items($Itens);

        //ENDEREÇO DE COBRANÇA
        $Billing = $pagarme->billing("{$PayMer['user_name']} {$PayMer['user_lastname']}", $PayAddr['addr_street'], $PayAddr['addr_number'], str_replace('-', '', $PayAddr['addr_zipcode']), 'br', $PayAddr['addr_state'], $PayAddr['addr_city'], $PayAddr['addr_district'], $PayAddr['addr_complement']);

        //CRIA CARTÃO DE CRÉDITO
        if (!empty($POST['select_creditcard'])):
            $getCard = $pagarme->getCreditCard($POST['select_creditcard']);
        else:
            $creditCard = $pagarme->createCreditCard(str_replace(" ", "", $POST['cardNumber']), $POST['cardName'], $POST['cardCVV'], $POST['expirationMonth'] . $POST['expirationYear']);
            $Read->ExeRead(DB_USERS_CARDS, "WHERE card_id = :cid", "cid={$creditCard->id}");
            if (!$Read->getResult()):
                $CreateCreditCart = [
                    'user_id' => $_SESSION['wc_payorder']['user_id'],
                    'card_id' => $creditCard->id
                ];
                $Create->ExeCreate(DB_USERS_CARDS, $CreateCreditCart);
            endif;
        endif;

        //ENVIA REQUISIÇÃO DE PGTO
        $pay = $pagarme->paymentRequest(explode(" ", $POST['cardInstallmentQuantity'])[2], explode(" ", $POST['cardInstallmentQuantity'])[0], false);
        
//        var_dump(number_format($pay->cost / 100, 2, '.', ''));
//        die();
        
        //ORDER SUBMIT
        $UpdateOrder = [
            'order_payment' => 101,
            'order_installments' => explode(" ", $POST['cardInstallmentQuantity'])[0],
            'order_installment' => number_format(explode(" ", $POST['cardInstallmentQuantity'])[1] / 100, 2, '.', ''),
            'order_status' => ($pay->status == 'processing' ? 6 : 4),
            'order_code' => $pay->id,
            'order_free' => number_format($pay->cost / 100, 2, '.', '')
        ];
        $Update->ExeUpdate(DB_ORDERS, $UpdateOrder, "WHERE order_id = :ord", "ord={$_SESSION['wc_payorder']['order_id']}");

        $jSON['resume'] = BASE . "/pedido/obrigado#cart";

        break;

    case 'billet':
        require 'Pagarme.php';

        $pagarme = new Payment\Pagarme;

        if (empty($_SESSION['wc_payorder'])):
            $jSON['triggerError'] = '<p class="big">&#10008; Erro ao processar pagamento!</p><p class="min">Desculpe mas não foi possível verificar os dados do pedido. Por favor, experimente atualizar a página e tentar novamente!</p>';
        else:

            //PAYMER
            $Read->ExeRead(DB_USERS, "WHERE user_id = :usrid", "usrid={$_SESSION['wc_payorder']['user_id']}");
            $PayMer = $Read->getResult()[0];

            //ADDR
            $Read->ExeRead(DB_USERS_ADDR, "WHERE addr_id = :addid", "addid={$_SESSION['wc_payorder']['order_addr']}");
            $PayAddr = $Read->getResult()[0];

            //VERIFICA SE USUÁRIO É O MESMO DO PAGAR.ME
            if (!empty($PayMer['user_pagarme'])):
                $getCustomer = $pagarme->getCustomer($PayMer['user_pagarme']);
            else:
                $cardCpfClear = ['.', '-'];
                $Customer = $pagarme->createCustomer($PayMer['user_id'], "{$PayMer['user_name']} {$PayMer['user_lastname']}", $PayMer['user_email'], str_replace($cardCpfClear, "", $PayMer['user_document']), "+55" . substr($PayMer['user_cell'], 1, 2) . substr(str_replace('-', '', $PayMer['user_cell']), 5));
                $UpdateUser = ['user_pagarme' => $Customer->id];
                $Update->ExeUpdate(DB_USERS, $UpdateUser, "WHERE user_id = :uid", "uid={$PayMer['user_id']}");
            endif;

            //ENDEREÇO DE ENTREGA E FRETE
            $ShipmentCost = (!empty($_SESSION['wc_payorder']['order_shipprice']) ? str_replace(".", "", $_SESSION['wc_payorder']['order_shipprice']) : 0);
            $Shipping = $pagarme->shipping("{$PayMer['user_name']} {$PayMer['user_lastname']}", $ShipmentCost, $PayAddr['addr_street'], $PayAddr['addr_number'], str_replace('-', '', $PayAddr['addr_zipcode']), 'br', $PayAddr['addr_state'], $PayAddr['addr_city'], $PayAddr['addr_district'], $PayAddr['addr_complement']);

            //ORDER PRODUCTS
            $Read->ExeRead(DB_ORDERS_ITEMS, "WHERE order_id = :orid", "orid={$_SESSION['wc_payorder']['order_id']}");
            if ($Read->getResult()):
                foreach ($Read->getResult() as $SideProduct):
                    $Itens[] = [
                        'id' => $SideProduct['pdt_id'],
                        'title' => $SideProduct['item_name'],
                        'unit_price' => str_replace(".", "", $SideProduct['item_price']),
                        'quantity' => $SideProduct['item_amount'],
                        'tangible' => 'true'
                    ];
                endforeach;
            endif;
            $Items = $pagarme->items($Itens);

            //ENDEREÇO DE COBRANÇA
            $Billing = $pagarme->billing("{$PayMer['user_name']} {$PayMer['user_lastname']}", $PayAddr['addr_street'], $PayAddr['addr_number'], str_replace('-', '', $PayAddr['addr_zipcode']), 'br', $PayAddr['addr_state'], $PayAddr['addr_city'], $PayAddr['addr_district'], $PayAddr['addr_complement']);

            //GERA BOLETO
            $pagarme->billet();

            //ENVIA REQUISIÇÃO DE PGTO
            $pay = $pagarme->paymentRequest(str_replace(".", "", $_SESSION['wc_payorder']['order_price']), 1, false);
            
//            var_dump($pay);
//            die();

            $UpdateOrder = [
                'order_payment' => 102,
                'order_status' => ($pay->status == 'processing' ? 6 : 4),
                'order_installments' => 1,
                'order_installment' => $_SESSION['wc_payorder']['order_price'],
                'order_code' => $pay->id,
                'order_free' => PAGARME_TX_BILLET,
                'order_billet' => $pay->boleto_url
            ];
            $Update->ExeUpdate(DB_ORDERS, $UpdateOrder, "WHERE order_id = :ord", "ord={$_SESSION['wc_payorder']['order_id']}");

            $jSON['billet'] = $pay->boleto_url;
            $jSON['resume'] = BASE . "/pedido/obrigado#cart";

        endif;
        break;
endswitch;

sleep(1);
echo json_encode($jSON);