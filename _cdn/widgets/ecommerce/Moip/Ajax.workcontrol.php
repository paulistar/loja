<?php

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
        elseif (strlen(str_replace(" ", "", $POST['cardNumber'])) != 16 && strlen(str_replace(" ", "", $POST['cardNumber'])) != 15):
            $jSON['field'] = 'cardNumber';
            $jSON['error'] = "<p class='wc_order_error'>&#10008; O número do cartão não é válido!</p>";
        elseif ($POST['cardExpirationMonth'] < 1 || $POST['cardExpirationMonth'] > 12):
            $jSON['field'] = 'cardExpirationMonth';
            $jSON['error'] = "<p class='wc_order_error'>&#10008; O mês {$POST['cardExpirationMonth']} não existe!</p>";
        elseif ($POST['cardExpirationYear'] < date("y")):
            $jSON['field'] = 'cardExpirationYear';
            $jSON['error'] = "<p class='wc_order_error'>&#10008; O ano deve ser maior que " . date('Y') . "!</p>";
        elseif ($POST['cardExpirationYear'] == date("y") && $POST['cardExpirationMonth'] < date("m")):
            $jSON['triggerError'] = '<p class="big">&#10008; Cartão expirado!</p><p class="min">A data de validade informada para o cartão de crédito é menor que a data atual. <b>Você informou ' . str_pad($POST['cardExpirationMonth'], 2, 0, 0) . '/' . $POST['cardExpirationYear'] . '.</b> Por favor confira esta informação!</p>';
        elseif (mb_strlen($POST['cardName']) < 5 || strpos($POST['cardName'], " ") === false):
            $jSON['field'] = 'cardName';
            $jSON['error'] = "<p class='wc_order_error'>&#10008; Favor informe o nome impresso no cartão!</p>";
        elseif (strlen($POST['cardCVV']) < 3 || strlen($POST['cardCVV']) > 4):
            $jSON['field'] = 'cardCVV';
            $jSON['error'] = "<p class='wc_order_error'>&#10008; O CVV deve ter 3 ou 4 números!</p>";
        elseif (!Check::CPF($POST['holderDocument'])):
            $jSON['field'] = 'holderDocument';
            $jSON['error'] = "<p class='wc_order_error' style='font-size: 1em'>&#10008; O CPF informado não é válido!</p>";
        elseif (!checkdate(explode("/", $POST['holderBirthDate'])[1], explode("/", $POST['holderBirthDate'])[0], explode("/", $POST['holderBirthDate'])[2])):
            $jSON['field'] = 'holderBirthDate';
            $jSON['error'] = "<p class='wc_order_error' style='font-size: 1em'>&#10008; Informe uma data de nascimento válida!</p>";
        else:
            $jSON['success'] = true;
        endif;
        break;

    //INSTALLMENT
    case 'installments':
        $total = $_SESSION['wc_payorder']['order_price'];
        $installments = $POST['installments'];
        $jSON['installment'] = null;

        if (ECOMMERCE_PAY_SPLIT):
            $fees = [
                1 => 0,
                2 => 4.50,
                3 => 5.00,
                4 => 5.50,
                5 => 6.50,
                6 => 7.50,
                7 => 8.50,
                8 => 9.50,
                9 => 10.50,
                10 => 11.50,
                11 => 12.00,
                12 => 12.50
            ];

            if ($installments <= ECOMMERCE_PAY_SPLIT_NUM):
                if ($installments <= ECOMMERCE_PAY_SPLIT_ACN):
                    $jSON['installment'] = $total / $installments;
                else:
                    $jSON['installment'] = ($total * ((100 + $fees[$installments]) / 100)) / $installments;
                endif;
            else:
                $jSON['installment'] = null;
            endif;
        else:
            $jSON['installment'] = $total;
        endif;
        break;

    //PUBLIC KEY
    case 'publicKey':
        //AUTOLOAD :: COMPOSER
        require 'vendor/autoload.php';

        //OBJECT :: MOIP
        if (MOIP_ENV == 'sandbox') {
            $moip = new \Moip\Moip(new \Moip\Auth\BasicAuth(MOIP_TOKEN_SANDBOX, MOIP_KEY_SANDBOX), \Moip\Moip::ENDPOINT_SANDBOX);
        } else {
            $moip = new \Moip\Moip(new \Moip\Auth\BasicAuth(MOIP_TOKEN_PRODUCTION, MOIP_KEY_PRODUCTION), \Moip\Moip::ENDPOINT_PRODUCTION);
        }

        //PUBLIC KEY
        $jSON['publicKey'] = $moip->keys()->get()->getEncryption();
        break;

    //CART ADD
    case 'creditCard':
        //AUTOLOAD :: COMPOSER
        require 'vendor/autoload.php';

        //OBJECT :: MOIP
        if (MOIP_ENV == 'sandbox') {
            $moip = new \Moip\Moip(new \Moip\Auth\BasicAuth(MOIP_TOKEN_SANDBOX, MOIP_KEY_SANDBOX), \Moip\Moip::ENDPOINT_SANDBOX);
        } else {
            $moip = new \Moip\Moip(new \Moip\Auth\BasicAuth(MOIP_TOKEN_PRODUCTION, MOIP_KEY_PRODUCTION), \Moip\Moip::ENDPOINT_PRODUCTION);
        }

        //CUSTOMER :: DATA
        $Read->FullRead('SELECT user_name, user_lastname, user_document, user_datebirth, user_cell, user_email FROM ' . DB_USERS, ' WHERE user_id = :usrid', "usrid={$_SESSION['wc_payorder']['user_id']}");
        $customerData = [
            'customer_id' => uniqid(),
            'customer_name' => $Read->getResult()[0]['user_name'],
            'customer_lastname' => $Read->getResult()[0]['user_lastname'],
            'customer_email' => $Read->getResult()[0]['user_email'],
            'customer_datebirth' => $Read->getResult()[0]['user_datebirth'],
            'customer_document' => str_replace(['.', '-'], '', $Read->getResult()[0]['user_document']),
            'customer_cell' => str_replace(['(', ')', ' ', '-'], '', $Read->getResult()[0]['user_cell'])
        ];

        //CUSTOMER :: ADDRESS
        $Read->FullRead('SELECT addr_zipcode, addr_street, addr_number, addr_complement, addr_district, addr_city, addr_state FROM ' . DB_USERS_ADDR, ' WHERE addr_id = :addid', "addid={$_SESSION['wc_payorder']['order_addr']}");
        $customerAddress = [
            'address_street' => $Read->getResult()[0]['addr_street'],
            'address_number' => $Read->getResult()[0]['addr_number'],
            'address_district' => $Read->getResult()[0]['addr_district'],
            'address_city' => $Read->getResult()[0]['addr_city'],
            'address_state' => $Read->getResult()[0]['addr_state'],
            'address_zipcode' => $Read->getResult()[0]['addr_zipcode'],
            'address_complement' => $Read->getResult()[0]['addr_complement']
        ];

        //CUSTOMER :: CREATE
        $createCustomer = $moip->customers();
        $createCustomer->setOwnId($customerData['customer_id']);
        $createCustomer->setFullname("{$customerData['customer_name']} {$customerData['customer_lastname']}");
        $createCustomer->setEmail($customerData['customer_email']);
        $createCustomer->setBirthDate($customerData['customer_datebirth']);
        $createCustomer->setTaxDocument($customerData['customer_document'], 'CPF');
        $createCustomer->setPhone(substr($customerData['customer_cell'], 0, 2), substr($customerData['customer_cell'], 2), 55);
        $createCustomer->addAddress('SHIPPING', $customerAddress['address_street'], $customerAddress['address_number'], $customerAddress['address_district'], $customerAddress['address_city'], $customerAddress['address_state'], $customerAddress['address_zipcode'], $customerAddress['address_complement'], 'BRA');

        //ORDER :: READ
        $Read->FullRead('SELECT stock_id, item_name, item_price, item_amount FROM ' . DB_ORDERS_ITEMS . ' WHERE order_id = :orid', "orid={$_SESSION['wc_payorder']['order_id']}");
        if ($Read->getResult()):
            //ORDER :: ID
            $orderId = uniqid();

            //ORDER :: SHIPPING
            $shippingAmount = (!empty($_SESSION['wc_payorder']['order_shipprice']) ? str_replace('.', '', $_SESSION['wc_payorder']['order_shipprice']) : 0);

            //ORDER :: ADDITION
            $addition = 0;

            //ORDER :: DISCOUNT
            $discount = (!empty($_SESSION['wc_cupom']) ? str_replace('.', '', $_SESSION['wc_cupom']) : 0);

            //ORDER :: CREATE
            $createOrder = $moip->orders();
            $createOrder->setOwnId($orderId);

            foreach ($Read->getResult() as $item):
                $item['item_amount'] = intval($item['item_amount']);
                $item['item_price'] = intval(str_replace('.', '', number_format(str_replace(',', '.', $item['item_price']), 2)));

                $Read->FullRead('SELECT stock_code_title, stock_color_title FROM ' . DB_PDT_STOCK . ' WHERE stock_id = :stock', "stock={$item['stock_id']}");
                $item['item_detail'] = "Cor: {$Read->getResult()[0]['stock_color_title']}, Tamanho: {$Read->getResult()[0]['stock_code_title']}";

                $createOrder->addItem($item['item_name'], $item['item_amount'], $item['item_detail'], $item['item_price'], 'OTHER_CATEGORIES');
            endforeach;

            $createOrder->setShippingAmount($shippingAmount);
            $createOrder->setAddition($addition);
            $createOrder->setDiscount($discount);
            $createOrder->setCustomer($createCustomer);
            $createOrder->create();
        endif;

        //HOLDER :: CREATE
        $POST['holderBirthDate'] = Check::Nascimento($POST['holderBirthDate']);
        $POST['holderDocument'] = str_replace(['.', '-'], '', $POST['holderDocument']);

        $createHolder = $moip->holders();
        $createHolder->setFullname($POST['cardName']);
        $createHolder->setBirthDate($POST['holderBirthDate']);
        $createHolder->setTaxDocument($POST['holderDocument'], 'CPF');
        $createHolder->setPhone(substr($POST['holderPhone'], 0, 2), substr($POST['holderPhone'], 2), 55);
        //$createHolder->setAddress('$type', '$street', '$number', '$district', '$city', '$state', '$zip', '$complement', '$country');
        //SET NOTIFICATION
        $notification = $moip->notifications();
        $notification->addEvent('PAYMENT.*');
        $notification->setTarget(BASE . '/_cdn/widgets/ecommerce/Moip/Notify.workcontrol.php');
        $notification->create();

        //PAYMENT :: REQUEST
        $payment = $createOrder->payments();
        $payment->setCreditCardHash($POST['cardHash'], $createHolder);
        $payment->setInstallmentCount($POST['cardInstallmentQuantity']);
        $payment->setStatementDescriptor(substr(SITE_NAME, 0, 13));
        $payment->execute();

        //ORDER :: UPDATE
        $code = $createOrder->getId();

        $UpdateOrder = [
            'order_status' => 4,
            'order_payment' => 101,
            'order_installments' => $POST['cardInstallmentQuantity'],
            'order_installment' => $POST['cardInstallmentValue'],
            'order_free' => 0,
            'order_code' => $code
        ];

        $Update->ExeUpdate(DB_ORDERS, $UpdateOrder, 'WHERE order_id = :id', "id={$_SESSION['wc_payorder']['order_id']}");

        //REDIRECT
        $jSON['resume'] = BASE . "/pedido/obrigado#cart";
        break;

    case 'billet':
        //AUTOLOAD :: COMPOSER
        require 'vendor/autoload.php';

        //OBJECT :: MOIP
        if (MOIP_ENV == 'sandbox') {
            $moip = new \Moip\Moip(new \Moip\Auth\BasicAuth(MOIP_TOKEN_SANDBOX, MOIP_KEY_SANDBOX), \Moip\Moip::ENDPOINT_SANDBOX);
        } else {
            $moip = new \Moip\Moip(new \Moip\Auth\BasicAuth(MOIP_TOKEN_PRODUCTION, MOIP_KEY_PRODUCTION), \Moip\Moip::ENDPOINT_PRODUCTION);
        }

        //CUSTOMER :: DATA
        $Read->FullRead('SELECT user_name, user_lastname, user_document, user_datebirth, user_cell, user_email FROM ' . DB_USERS, ' WHERE user_id = :usrid', "usrid={$_SESSION['wc_payorder']['user_id']}");
        $customerData = [
            'customer_id' => uniqid(),
            'customer_name' => $Read->getResult()[0]['user_name'],
            'customer_lastname' => $Read->getResult()[0]['user_lastname'],
            'customer_email' => $Read->getResult()[0]['user_email'],
            'customer_datebirth' => $Read->getResult()[0]['user_datebirth'],
            'customer_document' => str_replace(['.', '-'], '', $Read->getResult()[0]['user_document']),
            'customer_cell' => str_replace(['(', ')', ' ', '-'], '', $Read->getResult()[0]['user_cell'])
        ];

        //CUSTOMER :: ADDRESS
        $Read->FullRead('SELECT addr_zipcode, addr_street, addr_number, addr_complement, addr_district, addr_city, addr_state FROM ' . DB_USERS_ADDR, ' WHERE addr_id = :addid', "addid={$_SESSION['wc_payorder']['order_addr']}");
        $customerAddress = [
            'address_street' => $Read->getResult()[0]['addr_street'],
            'address_number' => $Read->getResult()[0]['addr_number'],
            'address_district' => $Read->getResult()[0]['addr_district'],
            'address_city' => $Read->getResult()[0]['addr_city'],
            'address_state' => $Read->getResult()[0]['addr_state'],
            'address_zipcode' => $Read->getResult()[0]['addr_zipcode'],
            'address_complement' => $Read->getResult()[0]['addr_complement']
        ];

        //CUSTOMER :: CREATE
        $createCustomer = $moip->customers();
        $createCustomer->setOwnId($customerData['customer_id']);
        $createCustomer->setFullname("{$customerData['customer_name']} {$customerData['customer_lastname']}");
        $createCustomer->setEmail($customerData['customer_email']);
        $createCustomer->setBirthDate($customerData['customer_datebirth']);
        $createCustomer->setTaxDocument($customerData['customer_document'], 'CPF');
        $createCustomer->setPhone(substr($customerData['customer_cell'], 0, 2), substr($customerData['customer_cell'], 2), 55);
        $createCustomer->addAddress('SHIPPING', $customerAddress['address_street'], $customerAddress['address_number'], $customerAddress['address_district'], $customerAddress['address_city'], $customerAddress['address_state'], $customerAddress['address_zipcode'], $customerAddress['address_complement'], 'BRA');

        //ORDER :: READ
        $Read->FullRead('SELECT stock_id, item_name, item_price, item_amount FROM ' . DB_ORDERS_ITEMS . ' WHERE order_id = :orid', "orid={$_SESSION['wc_payorder']['order_id']}");
        if ($Read->getResult()):
            //ORDER :: ID
            $orderId = uniqid();

            //ORDER :: SHIPPING
            $shippingAmount = (!empty($_SESSION['wc_payorder']['order_shipprice']) ? str_replace('.', '', $_SESSION['wc_payorder']['order_shipprice']) : 0);

            //ORDER :: ADDITION
            $addition = 0;

            //ORDER :: DISCOUNT
            $discount = (!empty($_SESSION['wc_cupom']) ? str_replace('.', '', $_SESSION['wc_cupom']) : 0);

            //ORDER :: CREATE
            $createOrder = $moip->orders();
            $createOrder->setOwnId($orderId);

            foreach ($Read->getResult() as $item):
                $item['item_amount'] = intval($item['item_amount']);
                $item['item_price'] = intval(str_replace('.', '', number_format(str_replace(',', '.', $item['item_price']), 2)));

                $Read->FullRead('SELECT stock_code_title, stock_color_title FROM ' . DB_PDT_STOCK . ' WHERE stock_id = :stock', "stock={$item['stock_id']}");
                $item['item_detail'] = "Cor: {$Read->getResult()[0]['stock_color_title']}, Tamanho: {$Read->getResult()[0]['stock_code_title']}";

                $createOrder->addItem("{$item['item_name']} - Cor: {$Read->getResult()[0]['stock_color_title']}, Tamanho: {$Read->getResult()[0]['stock_code_title']} - R$ " . number_format($item['item_price'], 2, ',', '.'), $item['item_amount'], $item['item_detail'], $item['item_price'], 'OTHER_CATEGORIES');
            endforeach;

            $createOrder->setShippingAmount($shippingAmount);
            $createOrder->setAddition($addition);
            $createOrder->setDiscount($discount);
            $createOrder->setCustomer($createCustomer);
            $createOrder->create();
        endif;

        //SET NOTIFICATION
        $notification = $moip->notifications();
        $notification->addEvent('PAYMENT.*');
        $notification->setTarget(BASE . '/_cdn/widgets/ecommerce/Moip/Notify.workcontrol.php');
        $notification->create();

        //PAYMENT :: REQUEST
        $payment = $createOrder->payments();

        $logoUri = INCLUDE_PATH . '/images/logo.png';
        $expirationDate = new DateTime();
        $instructionLines = array('', '', '');

        $Read->FullRead('SELECT stock_id, item_name, item_price, item_amount FROM ' . DB_ORDERS_ITEMS . ' WHERE order_id = :orid', "orid={$_SESSION['wc_payorder']['order_id']}");
        if ($Read->getResult()):
            $cc = 0;

            foreach ($Read->getResult() as $item):
                $Read->FullRead('SELECT stock_code_title, stock_color_title FROM ' . DB_PDT_STOCK . ' WHERE stock_id = :stock', "stock={$item['stock_id']}");
                $instructionLines[$cc] = "{$item['item_name']} - Cor: {$Read->getResult()[0]['stock_color_title']}, Tamanho: {$Read->getResult()[0]['stock_code_title']} - R$ " . number_format($item['item_price'], 2, ',', '.');
                $cc++;
            endforeach;
        endif;

        $payment->setBoleto($expirationDate, $logoUri, $instructionLines);
        $payment->execute();

        //ORDER :: UPDATE
        $code = $createOrder->getId();

        $UpdateOrder = [
            'order_status' => 4,
            'order_payment' => 102,
            'order_installments' => 1,
            'order_installment' => $_SESSION['wc_payorder']['order_price'],
            'order_free' => 0,
            'order_code' => $code,
            'order_billet' => $payment->getHrefPrintBoleto()
        ];

        $Update->ExeUpdate(DB_ORDERS, $UpdateOrder, 'WHERE order_id = :id', "id={$_SESSION['wc_payorder']['order_id']}");

        //REDIRECT
        $jSON['resume'] = BASE . "/pedido/obrigado#cart";
        break;
endswitch;

sleep(1);
echo json_encode($jSON);