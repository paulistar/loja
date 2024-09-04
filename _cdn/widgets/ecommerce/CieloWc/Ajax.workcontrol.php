<?php

/**
 * Created by NetBeans.
 * User: ebrahimpleite
 * Date: 03/10/2018
 * Time: 23:21
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
        require 'Cielo.php';

        $cielo = new Payment\Cielo;

        //PAYMER
        $Read->ExeRead(DB_USERS, "WHERE user_id = :usrid", "usrid={$_SESSION['wc_payorder']['user_id']}");
        $PayMer = $Read->getResult()[0];

        //ADDR
        $Read->ExeRead(DB_USERS_ADDR, "WHERE addr_id = :addid", "addid={$_SESSION['wc_payorder']['order_addr']}");
        $PayAddr = $Read->getResult()[0];

        //CRIA CARTÃO DE CRÉDITO
        if (empty($POST['select_creditcard'])):
            $creditCard = $cielo->createCreditCard($PayMer['user_name'] . ' ' . $PayMer['user_lastname'], str_replace(" ", "", $POST['cardNumber']), $POST['cardName'], $POST['expirationMonth'] . '/' . $POST['expirationYear'], $POST['cardCVV']);
            if (!empty($creditCard->CardToken)):
                $brand = $cielo->getCreditCardData(str_replace(" ", "", $POST['cardNumber']));
                $Read->ExeRead(DB_USERS_CARDS, "WHERE card_id = :cid", "cid={$creditCard->CardToken}");
                if (!$Read->getResult()):
                    $CreateCreditCart = [
                        'user_id' => $_SESSION['wc_payorder']['user_id'],
                        'card_id' => $creditCard->CardToken,
                        'card_brand' => $brand->Provider
                    ];
                    $Create->ExeCreate(DB_USERS_CARDS, $CreateCreditCart);
                endif;

                //ENVIA REQUISIÇÃO DE PGTO
                $cardCpfClear = ['.', '-'];
                $cardToken = $creditCard->CardToken;

                $orderSubTotal = explode(" ", $POST['cardInstallmentQuantity'])[2];
                $orderTotal = number_format($orderSubTotal, 2, '.', '');

                $pay = $cielo->paymentRequestCard($_SESSION['wc_payorder']['order_id'], $PayMer['user_name'] . ' ' . $PayMer['user_lastname'], $PayMer['user_email'], str_replace($cardCpfClear, "", $PayMer['user_document']), $PayAddr['addr_street'], $PayAddr['addr_number'], $PayAddr['addr_complement'], str_replace('-', '', $PayAddr['addr_zipcode']), $PayAddr['addr_city'], $PayAddr['addr_state'], str_replace('.', '', $orderTotal), explode(" ", $POST['cardInstallmentQuantity'])[0], $cardToken, true);

//        var_dump($pay);
//        die();
                //ORDER SUBMIT
                $UpdateOrder = [
                    'order_payment' => 101,
                    'order_installments' => explode(" ", $POST['cardInstallmentQuantity'])[0],
                    'order_installment' => number_format(explode(" ", $POST['cardInstallmentQuantity'])[1], 2, '.', ''),
                    'order_status' => ($pay->Payment->Status == 1 || $pay->Payment->Status == 2 ? 6 : 4),
                    'order_code' => $pay->Payment->PaymentId,
                    'order_free' => 0,
                    'order_mail_completed' => '0'
                ];
                $Update->ExeUpdate(DB_ORDERS, $UpdateOrder, "WHERE order_id = :ord", "ord={$_SESSION['wc_payorder']['order_id']}");

                $jSON['resume'] = BASE . "/pedido/obrigado#cart";
            else:
                $jSON['triggerError'] = '<p class="big">&#10008; Erro ao processar pagamento!</p><p class="min">Desculpe mas não foi possível verificar os dados do cartão!</p>';
            endif;
        else:
            //ENVIA REQUISIÇÃO DE PGTO
            $cardCpfClear = ['.', '-'];
            $cardToken = $POST['select_creditcard'];

            $orderSubTotal = explode(" ", $POST['cardInstallmentQuantity'])[2];
            $orderTotal = number_format($orderSubTotal, 2, '.', '');

            $pay = $cielo->paymentRequestCard($_SESSION['wc_payorder']['order_id'], $PayMer['user_name'] . ' ' . $PayMer['user_lastname'], $PayMer['user_email'], str_replace($cardCpfClear, "", $PayMer['user_document']), $PayAddr['addr_street'], $PayAddr['addr_number'], $PayAddr['addr_complement'], str_replace('-', '', $PayAddr['addr_zipcode']), $PayAddr['addr_city'], $PayAddr['addr_state'], str_replace('.', '', $orderTotal), explode(" ", $POST['cardInstallmentQuantity'])[0], $cardToken, true);

//        var_dump($pay);
//        die();
            //ORDER SUBMIT
            $UpdateOrder = [
                'order_payment' => 101,
                'order_installments' => explode(" ", $POST['cardInstallmentQuantity'])[0],
                'order_installment' => number_format(explode(" ", $POST['cardInstallmentQuantity'])[1], 2, '.', ''),
                'order_status' => ($pay->Payment->Status == 1 || $pay->Payment->Status == 2 ? 6 : 4),
                'order_code' => $pay->Payment->PaymentId,
                'order_free' => 0,
                'order_mail_completed' => '0'
            ];
            $Update->ExeUpdate(DB_ORDERS, $UpdateOrder, "WHERE order_id = :ord", "ord={$_SESSION['wc_payorder']['order_id']}");

            $jSON['resume'] = BASE . "/pedido/obrigado#cart";
        endif;

        break;

    case 'billet':
        require 'Cielo.php';

        $cielo = new Payment\Cielo;

        if (empty($_SESSION['wc_payorder'])):
            $jSON['triggerError'] = '<p class="big">&#10008; Erro ao processar pagamento!</p><p class="min">Desculpe mas não foi possível verificar os dados do pedido. Por favor, experimente atualizar a página e tentar novamente!</p>';
        else:

            //PAYMER
            $Read->ExeRead(DB_USERS, "WHERE user_id = :usrid", "usrid={$_SESSION['wc_payorder']['user_id']}");
            $PayMer = $Read->getResult()[0];

            //ADDR
            $Read->ExeRead(DB_USERS_ADDR, "WHERE addr_id = :addid", "addid={$_SESSION['wc_payorder']['order_addr']}");
            $PayAddr = $Read->getResult()[0];

            //ENVIA REQUISIÇÃO DE PGTO
            $cardCpfClear = ['.', '-'];
            $pay = $cielo->paymentRequestBillet($_SESSION['wc_payorder']['order_id'], $PayMer['user_name'] . ' ' . $PayMer['user_lastname'], $PayMer['user_email'], str_replace($cardCpfClear, "", $PayMer['user_document']), $PayAddr['addr_street'], $PayAddr['addr_number'], $PayAddr['addr_complement'], str_replace('-', '', $PayAddr['addr_zipcode']), $PayAddr['addr_city'], $PayAddr['addr_state'], str_replace(".", "", $_SESSION['wc_payorder']['order_price']));

//            var_dump($pay);
//            die();

            $UpdateOrder = [
                'order_payment' => 102,
                'order_status' => ($pay->Payment->Status == 1 || $pay->Payment->Status == 2 ? 6 : 4),
                'order_installments' => 1,
                'order_installment' => $_SESSION['wc_payorder']['order_price'],
                'order_code' => $pay->Payment->PaymentId,
                'order_free' => CIELO_TX_BILLET,
                'order_billet' => $pay->Payment->Url,
                'order_mail_completed' => '0'
            ];
            $Update->ExeUpdate(DB_ORDERS, $UpdateOrder, "WHERE order_id = :ord", "ord={$_SESSION['wc_payorder']['order_id']}");

            $jSON['billet'] = $pay->Payment->Url;
            $jSON['resume'] = BASE . "/pedido/obrigado#cart";

        endif;
        break;
endswitch;

sleep(1);
echo json_encode($jSON);
