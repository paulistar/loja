<?php

/*
 * @cielo Classe resposável por se comunicar com a Cielo
 * @author Ebrahim P. Leite | Web Creative www.webcreative.com.br
 * Criado em 01/10/2018
 */

namespace Payment;

class Cielo {

    private $merchantId;
    private $merchantKey;
    private $apiUrlQuery;
    private $apiUrl;
    private $headers;
    private $endPoint;
    private $params;
    private $callback;

    public function __construct() {
        if (CIELO_ENV == 'production') {
            $this->merchantId = CIELO_MERCHANT_ID_PRODUCTION;
            $this->merchantKey = CIELO_MERCHANT_KEY_PRODUCTION;
            $this->apiUrlQuery = 'https://apiquery.cieloecommerce.cielo.com.br';
            $this->apiUrl = 'https://api.cieloecommerce.cielo.com.br';
        } else {
            $this->merchantId = CIELO_MERCHANT_ID_SANDBOX;
            $this->merchantKey = CIELO_MERCHANT_KEY_SANDBOX;
            $this->apiUrlQuery = 'https://apiquerysandbox.cieloecommerce.cielo.com.br';
            $this->apiUrl = 'https://apisandbox.cieloecommerce.cielo.com.br';
        }
        $this->headers = [
            'Content-Type: application/json',
            "MerchantId: {$this->merchantId}",
            "MerchantKey: {$this->merchantKey}",
        ];
    }

    public function createCreditCard($name, $cardNumber, $cardHolderName, $cardExpirationDate, $cardCVV) {
        $brand = $this->getCreditCardData($cardNumber);
        $this->endPoint = '/1/card';
        $this->params = [
            'CustomerName' => $name,
            'CardNumber' => $cardNumber,
            'Holder' => $cardHolderName,
            'ExpirationDate' => $cardExpirationDate,
            'SecurityCode' => $cardCVV,
            'Brand' => $brand->Provider,
        ];
        $this->post();
        return $this->callback;
    }

    public function getCreditCard($cardToken) {
        $this->endPoint = "/1/card/{$cardToken}";
        $this->get();
        return $this->callback;
    }

    public function getCreditCardData($cardNumber) {
        $cardNumber = substr($cardNumber, 1, 6);
        $this->endPoint = "/1/cardBin/{$cardNumber}";
        $this->get();
        return $this->callback;
    }

    public function paymentRequestCard($orderId, $userName, $userEmail, $userDocument, $addrStreet, $addrNumber, $addrComplement, $addrZip, $addrCity, $addrState, $amount, $installments = 1, $cardToken, $capture = true) {
        $this->endPoint = '/1/sales';
        $this->params = [
            'MerchantOrderId' => $orderId,
            'Customer' => [
                "Name" => $userName,
                "Email" => $userEmail,
                "Identity" => $userDocument,
                "IdentityType" => "CPF",
                "Address" => [
                    "Street" => $addrStreet,
                    "Number" => $addrNumber,
                    "Complement" => $addrComplement,
                    "ZipCode" => $addrZip,
                    "City" => $addrCity,
                    "State" => $addrState,
                    "Country" => "BRA"
                ],
                "DeliveryAddress" => [
                    "Street" => $addrStreet,
                    "Number" => $addrNumber,
                    "Complement" => $addrComplement,
                    "ZipCode" => $addrZip,
                    "City" => $addrCity,
                    "State" => $addrState,
                    "Country" => "BRA"
                ],
            ],
            'Payment' => [
                'Type' => 'CreditCard',
                'Amount' => $amount,
                'Installments' => $installments,
                'SoftDescriptor' => CIELO_SOFT,
                'Capture' => $capture,
                'CreditCard' => [
                    'CardToken' => $cardToken
                ]
            ]
        ];
        $this->post();
        return $this->callback;
    }
    
    public function paymentRequestBillet($orderId, $userName, $userEmail, $userDocument, $addrStreet, $addrNumber, $addrComplement, $addrZip, $addrCity, $addrState, $amount) {
        $this->endPoint = '/1/sales';
        $this->params = [
            'MerchantOrderId' => $orderId,
            'Customer' => [
                "Name" => $userName,
                "Email" => $userEmail,
                "Identity" => $userDocument,
                "IdentityType" => "CPF",
                "Address" => [
                    "Street" => $addrStreet,
                    "Number" => $addrNumber,
                    "Complement" => $addrComplement,
                    "ZipCode" => $addrZip,
                    "City" => $addrCity,
                    "State" => $addrState,
                    "Country" => "BRA"
                ]
            ],
            'Payment' => [
                'Type' => 'Boleto',
                'Amount' => $amount,
                'Provider' => CIELO_BILLET_MODE, //consulte em _app/Config/Config.inc.php linha 290.
            ]
        ];
        $this->post();
        return $this->callback;
    }


    public function getTransaction($transaction) {
        $this->endPoint = "/1/sales/{$transaction}";
        $this->get();
        return $this->callback;
    }

    private function post() {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl . $this->endPoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($this->params),
            CURLOPT_HTTPHEADER => $this->headers,
        ]);
        $this->callback = json_decode(curl_exec($curl));
        curl_close($curl);
    }

    private function get() {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrlQuery . $this->endPoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $this->headers,
        ]);
        $this->callback = json_decode(curl_exec($curl));
        curl_close($curl);
    }

}
