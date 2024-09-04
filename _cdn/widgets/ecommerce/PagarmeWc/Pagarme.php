<?php

/*
 * @pagarme Classe resposável por se comunicar com a Pagar.me
 * @author Ebrahim P. Leite | Web Creative www.webcreative.com.br
 * Criado em 23/07/2018
 */

namespace Payment;

class Pagarme {
    /*
     * Service Config
     */

    private $url;
    private $endPoint;
    private $apikey;
    /*
     * Param API
     */
    private $preset;
    private $params;
    private $transaction;
    /*
     * Return and Callback
     */
    private $callback;

    public function __construct() {
        $this->url = 'https://api.pagar.me';
        $this->transaction = [];
        if (PAGARME_ENV == 'production') {
            $this->apikey = PAGARME_API_KEY_PRODUCTION; // PRODUÇÃO
        } else {
            $this->apikey = PAGARME_API_KEY_SANDBOX; // SANDBOX
        }
        $this->preset = [
            'api_key' => $this->apikey
        ];
    }

    /*     * ****************
     * *** ENVIA REQUISIÇÃO DE PAGAMENTOS *****
     * **************** */

    public function paymentRequest($amount, $installments = 1, $async = true) {
        $this->endPoint = '/1/transactions';
        $this->transaction += [
            'installments' => $installments,
            'amount' => $amount,
            'async' => $async,
            'postback_url' => BASE . '/_cdn/widgets/ecommerce/PagarmeWc/PagarmeNotify.workcontrol.php',
        ];
        $this->params = $this->transaction;

        $this->post();
        return $this->callback;
    }

    /*     * ****************
     * *** CUSTOMER ****
     * **************** */

    public function createCustomer($userId, $userName, $userEmail, $userDocument, $userPhone, $userType = 'individual', $userCountry = 'br') {
        $this->endPoint = '/1/customers';
        $this->params = [
            'external_id' => $userId,
            'name' => $userName,
            'email' => $userEmail,
            'documents' => [
                [
                    'type' => "cpf",
                    'number' => $userDocument
                ]
            ],
            'phone_numbers' => [$userPhone],
            'type' => $userType,
            'country' => $userCountry
        ];
        $this->post();
        $this->setTransactionCustomer();
        return $this->callback;
    }

    public function getCustomer($userCod) {
        $this->endPoint = "/1/customers/{$userCod}";
        $this->get();
        $this->setTransactionCustomer();
        return $this->callback;
    }

    /*     * *******************
     * *** CARTÃO DE CRÉDITO ****
     * ******************* */

    public function createCreditCard($cardNumber, $cardHolderName, $cardCvv, $cardExpirationDate) {
        $this->endPoint = '/1/cards';
        $this->params = [
            'card_number' => $cardNumber,
            'card_expiration_date' => $cardExpirationDate,
            'card_cvv' => $cardCvv,
            'card_holder_name' => $cardHolderName
        ];
        $this->post();
        $this->setTransactionCreditCard();
        return $this->callback;
    }

    public function getCreditCard($cardCod) {
        $this->endPoint = "/1/cards/{$cardCod}";
        $this->get();
        $this->setTransactionCreditCard();
//        var_dump($this->transaction);
        return $this->callback;
    }

    public function getInstallments($valueFinish) {
        $this->endPoint = "/1/transactions/calculate_installments_amount?amount=" . $valueFinish . "&free_installments=" . ECOMMERCE_PAY_SPLIT_ACN . "&max_installments=" . ECOMMERCE_PAY_SPLIT_NUM . "&interest_rate=" . PAGARME_TX_JUROS;
        $this->get();
        return $this->callback;
    }
    
    /*     * ****************
     * *** ENDEREÇO DE COBRANÇA ****
     * **************** */

    public function billing($userName, $addrStreet, $addrStreetNumber, $addrZipCode, $addrCountry, $addrState, $addrCity, $addrDistrict, $addrComplementary = null) {
        $this->transaction += [
            'billing' => [
                'name' => $userName,
                'address' => [
                    'street' => $addrStreet,
                    'street_number' => $addrStreetNumber,
                    'zipcode' => $addrZipCode,
                    'country' => $addrCountry,
                    'state' => $addrState,
                    'city' => $addrCity,
                    'neighborhood' => $addrDistrict,
                    'complementary' => $addrComplementary
                ]
            ]
        ];
    }

    /*     * ****************
     * *** ENDEREÇO DE ENTREGA ****
     * **************** */

    public function shipping($userName, $shipmentCost, $addrStreet, $addrStreetNumber, $addrZipCode, $addrCountry, $addrState, $addrCity, $addrDistrict, $addrComplementary = null) {
        $this->transaction += [
            'shipping' => [
                'name' => $userName,
                'fee' => $shipmentCost,
                'address' => [
                    'street' => $addrStreet,
                    'street_number' => $addrStreetNumber,
                    'zipcode' => $addrZipCode,
                    'country' => $addrCountry,
                    'state' => $addrState,
                    'city' => $addrCity,
                    'neighborhood' => $addrDistrict,
                    'complementary' => $addrComplementary
                ]
            ]
        ];
    }

    /*     * ****************
     * *** PRODUTOS SOLICITADOS ****
     * **************** */

    public function items($Itens) {
        $this->transaction += [
            'items' => $Itens
        ];
    }

    /*     * ****************
     * *** BOLETO ****
     * **************** */

    public function billet() {
        $this->transaction += [
            'payment_method' => 'boleto'
        ];
    }

    /**     * *********************
     * *** METHOD PRIVATE ****
     * ********************** */
    private function setTransactionCustomer() {
        $this->transaction += [
            'customer' => [
                'id' => $this->callback->id,
                'name' => $this->callback->name,
                'email' => $this->callback->email,
                'phone_numbers' => $this->callback->phone_numbers,
                'documents' => [
                    [
                        'type' => 'cpf',
                        'number' => $this->callback->documents[0]->number
                    ]
                ]
            ]
        ];
    }

    private function setTransactionCreditCard() {
        $this->transaction += [
            'card_id' => $this->callback->id,
            'payment_method' => 'credit_card'
        ];
    }

    /*     * **********************
     * **** METHOD REST ******
     * ********************** */

    private function post() {
        $url = $this->url . $this->endPoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array_merge($this->params, $this->preset)));
        curl_setopt($ch, CURLOPT_HTTPHEADER, []);
        $this->callback = json_decode(curl_exec($ch));
        curl_close($ch);
    }

    private function get() {
        $url = $this->url . $this->endPoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->preset));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $this->callback = json_decode(curl_exec($ch));
        curl_close($ch);
    }

}