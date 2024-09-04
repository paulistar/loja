<?php

ini_set('max_execution_time', 0);

/**
 * TAMCARGO [ HELPER ]
 * Classe para realizar cotações no sistema da TamCargo
 * @copyright (c) year, Elizandro Echer - REVOSYS
 */
class TamCargo {

    private $username;
    private $password;
    private $cookie;
    private $cookieLogin;
    private $cookieDisplay;
    private $viewstate;
    private $viewstateLogin;
    private $timeLogin;
    private $mainHeader;
    private $cepOrigem;
    private $cepDestino;
    private $coleta;
    private $entrega;
    private $seguro;
    private $payer;
    private $aeroportoOrigem;
    private $aeroportoDestino;

    /**
     * Peso Real será obtido a partir da função getPsReal()
     * @var string
     */
    private $psReal;

    /**
     * Informar o valor total do pedido (decimal deve ser informado com ".")
     * @var string
     */
    private $vlMercadoria;

    /**
     * Informar o peso total do pedido (decimal deve ser informado com ".")
     * @var string
     */
    private $totalweight;

    /**
     * OPCIONAL (CASO NÃO INFORMADO A COTAÇÃO SERÁ SOMENTE POR PESO) - Informar o volume total do pedido (largura x comprimento x altura x quantidade) em m para obter o peso cubado (decimal deve ser informado com ".")
     * @var string
     */
    private $totalvolume;

    /**
     * Peso cubado calculado pela função getPsReal
     * @var string
     */
    private $cubicweight;

    /**
     * Informar se a cotação deverá ser realizada somente por peso ou deverá ser calculado o peso cubado
     * @var boolean
     */
    private $quoteByWeight;

    /**
     * Informar o valor percentual a ser somado ao valor do frete
     * @var float
     */
    private $additionalPercent;

    /**
     * Informar o valor fixo a ser somado ao valor do frete
     * @var float
     */
    private $additionalCharge;

    /**
     * Informar o número de dias a ser somado ao prazo de entrega
     * @var int
     */
    private $shipmentDelay;

    /**
     * Resultado retornado pela classe
     * @return string
     */
    private $Result;

    /**
     * 
     * @param string $username Informar usuário de acesso ao MyCargo Manager da Tam Cargo
     * @param string $password Informar senha de acesso ao MyCargo Manager da Tam Cargo
     * @param string $cepOrigem Informar o CEP de origem da mercadoria
     * @param boolean $coleta OPCIONAL Informar se deseja coleta (Padrão TRUE)
     */
    public function __construct($username, $password, $cepOrigem, $coleta = TRUE) {
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setCepOrigem($cepOrigem);
        $this->setColeta($coleta);
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setCepOrigem($cepOrigem) {
        $this->cepOrigem = preg_replace("/[^0-9]/", "", $cepOrigem);
    }

    public function setCepDestino($cepDestino) {
        $this->cepDestino = preg_replace("/[^0-9]/", "", $cepDestino);
    }

    public function setColeta($coleta) {
        $this->coleta = (boolval($coleta)) ? 'true' : 'false';
    }

    public function setEntrega($entrega) {
        $this->entrega = (boolval($entrega)) ? 'true' : 'false';
    }

    public function setSeguro($seguro) {
        $this->seguro = (boolval($seguro)) ? 'TAM' : 'SIN';
    }

    public function setVlMercadoria($vlMercadoria) {
        $this->vlMercadoria = number_format(preg_replace('/[^0-9.]*/', '', $vlMercadoria), 2, '.', '');
    }

    public function setTotalweight($totalweight) {
        $this->totalweight = number_format($totalweight, 4, '.', '');
    }

    public function setTotalvolume($totalvolume) {
        $this->totalvolume = (!empty($totalvolume) ? number_format($totalvolume, 4, '.', '') : '');
    }

    public function setQuoteByWeight($quoteByWeight) {
        $this->quoteByWeight = $quoteByWeight;
    }

    public function setAdditionalPercent($additionalPercent) {
        $this->additionalPercent = (!empty($additionalPercent) ? number_format(preg_replace('/[^0-9.]*/', '', $additionalPercent), 2, '.', '') : null);
    }

    public function setAdditionalCharge($additionalCharge) {
        $this->additionalCharge = (!empty($additionalCharge) ? number_format(preg_replace('/[^0-9.]*/', '', $additionalCharge), 2, '.', '') : null);
    }

    public function setShipmentDelay($shipmentDelay) {
        $this->shipmentDelay = (!empty($shipmentDelay) ? intval($shipmentDelay) : null);
    }

    /**
     * 
     * @param string $cepDestino Informar o CEP de destino da mercadoria
     * @param string $totalweight Informar o peso total do pedido (decimal deve ser informado com ".")
     * @param string $vlMercadoria Informar o valor total do pedido (decimal deve ser informado com ".")
     * @param string $totalvolume OPCIONAL (CASO NÃO INFORMADO A COTAÇÃO SERÁ SOMENTE POR PESO) - Informar o volume total do pedido (largura x comprimento x altura x quantidade) em m para obter o peso cubado (decimal deve ser informado com ".")
     * @param boolean $entrega OPCIONAL Informar se deseja entrega (Padrão TRUE)
     * @param type $seguro OPCIONAL Informar se deseja seguro (Padrão TRUE)
     * @param int $quoteByWeight Informar se a cotação deverá ser realizada somente por peso ou deverá ser calculado o peso cubado
     * @param float $additionalPercent Informar o valor percentual a ser somado ao valor do frete
     * @param float $additionalCharge Informar o valor fixo a ser somado ao valor do frete
     * @param int $shipmentDelay Informar o número de dias a ser somado ao prazo de entrega
     */
    public function setQuoteData($cepDestino, $totalweight, $vlMercadoria, $totalvolume, $entrega = TRUE, $seguro = TRUE, $quoteByWeight = NULL, $additionalPercent = NULL, $additionalCharge = NULL, $shipmentDelay = NULL) {
        $this->setCepDestino($cepDestino);
        $this->setTotalweight($totalweight);
        $this->setVlMercadoria($vlMercadoria);
        $this->setTotalvolume($totalvolume);
        $this->setEntrega($entrega);
        $this->setSeguro($seguro);
        $this->setQuoteByWeight($quoteByWeight);
        $this->setAdditionalPercent($additionalPercent);
        $this->setAdditionalCharge($additionalCharge);
        $this->setShipmentDelay($shipmentDelay);
        $this->setPsReal();
    }

    public function getQuote() {
        $this->makeQuoteRequest();
        return $this->getResult();
    }

    public function getResult() {
        return $this->Result;
    }

    /*
     * ****************************************
     * ******* PRIVATE METHODS  ********
     * ****************************************
     */

    private function setPsReal() {
        if (!empty($this->totalvolume) && !empty($this->totalweight) && !$this->quoteByWeight):
            $this->cubicweight = $this->totalvolume * 166.5;
            if ($this->cubicweight > $this->totalweight):
                $this->psReal = number_format($this->cubicweight, 4, '.', '');
            else:
                $this->psReal = $this->totalweight;
            endif;
        else:
            (!empty($this->totalweight) ? $this->psReal = $this->totalweight : null);
        endif;
    }

    private function makeQuoteRequest() {
        $this->login();
        if (!$this->cookieLogin && !$this->viewstateLogin) {
            $this->makeQuoteRequest();
        } else {
            $this->setPayer();
            $this->setMainHeader();
            $this->defineOriginAirport();
            $this->definePickup();
            $this->defineDestinyAirport();
            $this->defineDelivery();
            $this->defineWeight();
            if ($this->seguro === 'SIN') {
                $this->defineInsurance();
            }
            $this->definePayer();
            if ($this->cookieDisplay) {
                $this->parseQuoteResult($this->getResponse());
            }
        }
    }

    private function parseQuoteResult($resultado) {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        @$doc->loadHTML($resultado['response']);
        $xpath = new DOMXpath($doc);

        $prazoentregaadicional = '';
        if ($xpath->query('//*[@id="form:j_idt83"]')->item(0)) {
            $prazoentregaadicional = intval($xpath->query('//*[@id="form:j_idt83"]')->item(0)->getAttribute('value'));
        }

        $prazoentrega = ['CONVENCIONAL' => '3', 'PROXIMO DIA' => '2', 'PROXIMO VOO' => '1', 'PREPAGO' => '2'];

        $trs = $xpath->query('//*[@id="form:quotationTableId_data"]/tr');
        $modals = [];
        foreach ($trs as $modkey => $tr) {
            $modals[$modkey]['modal'] = $tr->childNodes->item(0)->nodeValue;
            $modals[$modkey]['valorfrete'] = number_format(($tr->childNodes->item(7)->nodeValue / (1 - ($this->additionalPercent / 100))) + $this->additionalCharge, 2, '.', '');
            $modals[$modkey]['prazoentrega'] = $prazoentrega[$tr->childNodes->item(0)->nodeValue] + $prazoentregaadicional;
            $modals[$modkey]['Parcelas']['frete'] = $tr->childNodes->item(1)->nodeValue;
            $modals[$modkey]['Parcelas']['tarifa'] = $tr->childNodes->item(2)->nodeValue;
            $modals[$modkey]['Parcelas']['coleta'] = $tr->childNodes->item(3)->nodeValue;
            $modals[$modkey]['Parcelas']['entrega'] = $tr->childNodes->item(4)->nodeValue;
            $modals[$modkey]['Parcelas']['outros'] = $tr->childNodes->item(5)->nodeValue;
            $modals[$modkey]['Parcelas']['impostos'] = $tr->childNodes->item(6)->nodeValue;
        }
        $this->Result = ['status' => 'OK', 'aeroportoOrigem' => $this->aeroportoOrigem, 'aeroportoDestino' => $this->aeroportoDestino, 'redespacho' => ($prazoentregaadicional ? true : false)];
        foreach ($modals as $modal) {
            if (floatval($modal['Parcelas']['frete'])) {
                $this->Result['modals'][] = array_merge(['status' => 'OK'], $modal);
            } else {
                $this->Result['modals'][] = array_merge(['status' => 'ERROR', 'errorMsg' => 'Não foi possível obter valor para esta modalidade de envio!'], $modal);
            }
        }
    }

    private function getCookie() {
        $getcookie = Utils::CurlExec('https://minutaweb.lancargo.com/MinutaWEB-3.0/public/client.jsf');
        if ($getcookie['status'] === 200) {
            $this->cookie = $getcookie['cookie'];
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            @$doc->loadHTML($getcookie['response']);
            $xpath = new DOMXpath($doc);
            $this->viewstate = $xpath->query('//*[@name="javax.faces.ViewState"]')->item(0)->getAttribute('value');
        }
    }

    private function getOriginAddrInfo() {
        $headers = [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
            "Accept: application/xml, text/xml, */*; q=0.01",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Accept-Encoding: ",
            "Connection: keep-alive",
            "Host: minutaweb.lancargo.com",
            "Origin: https://minutaweb.lancargo.com",
            "Referer: https://minutaweb.lancargo.com/MinutaWEB-3.0/public/client.jsf",
            "X-Requested-With: XMLHttpRequest",
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "Faces-Request: partial/ajax",
            "Cookie: $this->cookie"
        ];

        $params = [
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'inputcep',
            'primefaces.resetvalues' => 'true',
            'javax.faces.partial.execute' => 'inputcep',
            'javax.faces.partial.render' => 'inputcep inputcidade inputbairro inputendereco inputcomplemento panel-sender-cep stationIataInformation',
            'javax.faces.behavior.event' => 'blur',
            'javax.faces.partial.event' => 'blur',
            'formConteudo' => 'formConteudo',
            'inputcep' => substr($this->cepOrigem, 0, 5) . '-' . substr($this->cepOrigem, 5, 3),
            'inputairorig_input' => '0',
            'inputairdest_input' => '0',
            'javax.faces.ViewState' => $this->viewstate
        ];

        $zipInfo = Utils::CurlExec("https://minutaweb.lancargo.com/MinutaWEB-3.0/public/client.jsf", $params, $headers);
    }

    private function getOriginAirport() {
        $this->getCookie();
        $this->getOriginAddrInfo();

        $headers = [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
            "Accept: application/xml, text/xml, */*; q=0.01",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Accept-Encoding: ",
            "Connection: keep-alive",
            "Host: minutaweb.lancargo.com",
            "Origin: https://minutaweb.lancargo.com",
            "Referer: https://minutaweb.lancargo.com/MinutaWEB-3.0/public/client.jsf",
            "X-Requested-With: XMLHttpRequest",
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "Faces-Request: partial/ajax",
            "Cookie: $this->cookie"
        ];

        $params = [
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'checkBoxCollect',
            'javax.faces.partial.execute' => 'checkBoxCollect',
            'javax.faces.partial.render' => 'origStationInformation',
            'javax.faces.behavior.event' => 'valueChange',
            'javax.faces.partial.event' => 'change',
            'formConteudo' => 'formConteudo',
            'inputcep' => substr($this->cepOrigem, 0, 5) . '-' . substr($this->cepOrigem, 5, 3),
            'checkBoxCollect_input' => 'on',
            'inputairorig_input' => '0',
            'inputairdest_input' => '0',
            'javax.faces.ViewState' => $this->viewstate
        ];

        $html = Utils::CurlExec("https://minutaweb.lancargo.com/MinutaWEB-3.0/public/client.jsf", $params, $headers);
        if ($html['status'] === 200) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            @$doc->loadHTML($html['response']);
            $xpath = new DOMXpath($doc);
            $options = $xpath->query('//*[@id="inputairorig_input"]/option');
            foreach ($options as $opt) {
                if ($opt->getAttribute('selected')) {
                    return [
                        'status' => 'OK',
                        'airportCode' => $opt->getAttribute('value'),
                        'airportName' => $opt->nodeValue
                    ];
                } elseif (strstr($html['response'], 'CEP não atendido para coleta')) {
                    return [
                        'status' => 'ERROR',
                        'errorMsg' => 'CEP origem inválido ou não atendido para coleta',
                    ];
                }
            }
        } else {
            return array('status' => 'ERROR', 'errorMsg' => 'Não foi possível se comunicar com o servidor da Tam!');
        }
    }

    private function getDestinyAddrInfo() {
        $headers = [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
            "Accept: application/xml, text/xml, */*; q=0.01",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Accept-Encoding: ",
            "Connection: keep-alive",
            "Host: minutaweb.lancargo.com",
            "Origin: https://minutaweb.lancargo.com",
            "Referer: https://minutaweb.lancargo.com/MinutaWEB-3.0/public/client.jsf",
            "X-Requested-With: XMLHttpRequest",
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "Faces-Request: partial/ajax",
            "Cookie: $this->cookie"
        ];

        $params = [
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'inputcep2',
            'primefaces.resetvalues' => 'true',
            'javax.faces.partial.execute' => 'inputcep2',
            'javax.faces.partial.render' => 'inputcep2 inputcidade2 inputbairro2 inputendereco2 inputcomplemento2 panel-recipient-cep stationIataInformation',
            'javax.faces.behavior.event' => 'blur',
            'javax.faces.partial.event' => 'blur',
            'formConteudo' => 'formConteudo',
            'inputcep2' => substr($this->cepDestino, 0, 5) . '-' . substr($this->cepDestino, 5, 3),
            'inputairorig_input' => '0',
            'inputairdest_input' => '0',
            'javax.faces.ViewState' => $this->viewstate
        ];

        $zipInfo = Utils::CurlExec("https://minutaweb.lancargo.com/MinutaWEB-3.0/public/client.jsf", $params, $headers);
    }

    private function getDestinyAirport() {
        $this->getCookie();
        $this->getDestinyAddrInfo();

        $headers = [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
            "Accept: application/xml, text/xml, */*; q=0.01",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Accept-Encoding: ",
            "Connection: keep-alive",
            "Host: minutaweb.lancargo.com",
            "Origin: https://minutaweb.lancargo.com",
            "Referer: https://minutaweb.lancargo.com/MinutaWEB-3.0/public/client.jsf",
            "X-Requested-With: XMLHttpRequest",
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "Faces-Request: partial/ajax",
            "Cookie: $this->cookie"
        ];

        $params = [
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'checkBoxDelivery',
            'javax.faces.partial.execute' => 'checkBoxDelivery',
            'javax.faces.partial.render' => 'destStationInformation',
            'javax.faces.behavior.event' => 'valueChange',
            'javax.faces.partial.event' => 'change',
            'formConteudo' => 'formConteudo',
            'inputcep2' => substr($this->cepDestino, 0, 5) . '-' . substr($this->cepDestino, 5, 3),
            'inputairorig_input' => '0',
            'checkBoxDelivery_input' => 'on',
            'inputairdest_input' => '0',
            'javax.faces.ViewState' => $this->viewstate
        ];

        $html = Utils::CurlExec("https://minutaweb.lancargo.com/MinutaWEB-3.0/public/client.jsf", $params, $headers);
        if ($html['status'] === 200) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            @$doc->loadHTML($html['response']);
            $xpath = new DOMXpath($doc);
            $options = $xpath->query('//*[@id="inputairdest_input"]/option');
            foreach ($options as $opt) {
                if ($opt->getAttribute('selected')) {
                    return [
                        'status' => 'OK',
                        'airportCode' => $opt->getAttribute('value'),
                        'airportName' => $opt->nodeValue
                    ];
                } elseif (strstr($html['response'], 'CEP não atendido para entrega')) {
                    return [
                        'status' => 'ERROR',
                        'errorMsg' => 'CEP destino inválido ou não atendido para entrega',
                    ];
                }
            }
        } else {
            return array('status' => 'ERROR', 'errorMsg' => 'Não foi possível se comunicar com o servidor da Tam!');
        }
    }

    private function login() {
        $firstcall = Utils::CurlExec('https://secure.lancargo.com/homepage/private/home.html?parameters=LA-en');
        $getcookie = Utils::CurlExec($firstcall['headers']['Location']);
        if ($getcookie['status'] === 200) {
            $cookie = $getcookie['cookie'];
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            @$doc->loadHTML($getcookie['response']);
            $xpath = new DOMXpath($doc);
            $lt = $xpath->query('//input[@name="lt"]')->item(0)->getAttribute('value');
            $execution = $xpath->query('//input[@name="execution"]')->item(0)->getAttribute('value');

            $cookie_home = explode('=', explode(';', $firstcall['headers']['Set-Cookie'][0])[0])[1];
            $cookie_cas = explode('=', explode(';', $getcookie['headers']['Set-Cookie'][0])[0])[1];

            $url = "https://secure.lancargo.com/cas/login;jsessionid={$cookie_cas}?service=" . urlencode("https://secure.lancargo.com/homepage/j_spring_cas_security_check;jsessionid={$cookie_home}?parameters=LA-en");

            $headers = [
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
                "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
                "Accept-Encoding: ",
                "Connection: keep-alive",
                "Cache-Control: max-age=0",
                "Content-Type: application/x-www-form-urlencoded",
                "Host: secure.lancargo.com",
                "Origin: https://secure.lancargo.com",
                "Referer: $url",
                "Upgrade-Insecure-Requests: 1",
            ];

            $params = [
                'username' => $this->username,
                'password' => $this->password,
                'lt' => $lt,
                'execution' => $execution,
                '_eventId' => 'submit',
                'submit' => 'ENTRAR',
            ];
            $html = Utils::CurlExec($url, $params, $headers);

            if ($html['status'] === 200) {

                $headers2 = [
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
                    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
                    "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
                    "Accept-Encoding: ",
                    "Connection: keep-alive",
                    "Cache-Control: max-age=0",
                    "Content-Type: application/x-www-form-urlencoded",
                    "Host: secure.lancargo.com",
                    "Origin: https://secure.lancargo.com",
                    "Referer: $url",
                    "Cookie: {$html['cookie']}",
                    "Upgrade-Insecure-Requests: 1",
                ];

                $html2 = Utils::CurlExec("https://secure.lancargo.com/homepage/private/home.html;jsessionid={$cookie_home}?parameters=LA-en", [], $headers2);
                if ($html2['status'] === 200) {
                    $html3 = Utils::CurlExec('https://secure.lancargo.com/cas/login?TARGET=https%3A%2F%2Fsecure.lancargo.com%2FeBusiness-web-1.0-view%2Fprivate%2FCreateQuotation.jsf%3Flanguage%3Den%26company%3DLA', [], $headers2);
                    if ($html3['status'] === 200) {
                        $this->cookieLogin = $html3['cookie'];
                        $doc = new DOMDocument();
                        libxml_use_internal_errors(true);
                        @$doc->loadHTML($html3['response']);
                        $xpath = new DOMXpath($doc);
                        $this->viewstateLogin = $xpath->query('//*[@name="javax.faces.ViewState"]')->item(0)->getAttribute('value');
                        $this->timeLogin = new DateTime();
                        $file = fopen('./tamLoginInfo.dat', 'w+');
                        fwrite($file, serialize(['cookie' => $this->cookieLogin, 'viewstate' => $this->viewstateLogin, 'lastlogin' => $this->timeLogin]));
                        fclose($file);
                    } else {
                        $this->Result = ['status' => 'ERROR', 'errorMsg' => (!isset($this->Result['errorMsg']) ? 'Erro ao executar login no servidor da Tam!' : $this->Result['errorMsg'] . ' | ' . 'Erro ao executar login no servidor da Tam!')];
                    }
                } else {
                    $this->Result = ['status' => 'ERROR', 'errorMsg' => (!isset($this->Result['errorMsg']) ? 'Não foi possível se comunicar com o servidor da Tam!' : $this->Result['errorMsg'] . ' | ' . 'Não foi possível se comunicar com o servidor da Tam!')];
                }
            } else {
                $this->Result = ['status' => 'ERROR', 'errorMsg' => (!isset($this->Result['errorMsg']) ? 'Não foi possível se comunicar com o servidor da Tam!' : $this->Result['errorMsg'] . ' | ' . 'Não foi possível se comunicar com o servidor da Tam!')];
            }
        } else {
            $this->Result = ['status' => 'ERROR', 'errorMsg' => (!isset($this->Result['errorMsg']) ? 'Não foi possível se comunicar com o servidor da Tam!' : $this->Result['errorMsg'] . ' | ' . 'Não foi possível se comunicar com o servidor da Tam!')];
            die;
        }
    }

    private function setPayer() {
        $headers = [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Accept-Encoding: ",
            "Connection: keep-alive",
            "Host: secure.lancargo.com",
            "Origin: https://secure.lancargo.com",
            "Referer: https://secure.lancargo.com/homepage/private/home.html?parameters=LA-en",
            "Cookie: {$this->cookieLogin}",
            "Upgrade-Insecure-Requests: 1",
        ];
        $html = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf?language=en&company=LA', [], $headers);
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        @$doc->loadHTML($html['response']);
        $xpath = new DOMXpath($doc);
        if ($xpath->query('//*[@id="form:j_idt123_input"]/option[2]')->item(0)) {
            $this->payer = $xpath->query('//*[@id="form:j_idt123_input"]/option[2]')->item(0)->getAttribute('value');
        }
    }

    private function setMainHeader() {
        $this->mainHeader = [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
            "Accept: application/xml, text/xml, */*; q=0.01",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Accept-Encoding: ",
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "Host: secure.lancargo.com",
            "Origin: https://secure.lancargo.com",
            "Cookie: {$this->cookieLogin}",
            "Referer: https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf?language=en&company=LA",
            "Faces-Request: partial/ajax",
            "X-Requested-With: XMLHttpRequest",
            "Upgrade-Insecure-Requests: 1",
        ];
    }

    private function defineOriginAirport() {
        $aeroportoOrigemCode = $this->getOriginAirport();
        if ($aeroportoOrigemCode['status'] == 'OK') {
            $params = [
                'javax.faces.partial.ajax' => 'true',
                'javax.faces.source' => 'form:originId',
                'javax.faces.partial.execute' => 'form:originId',
                'javax.faces.partial.render' => 'form:originId',
                'form:originId' => 'form:originId',
                'form:originId_query' => $aeroportoOrigemCode['airportCode'],
                'form' => 'form',
                'form:originId_input' => $aeroportoOrigemCode['airportCode'],
                'form:j_idt84_input' => 'ALL',
                'form:j_idt98_input' => 'TAM',
                'form:j_idt110' => 'P',
                'form:accordionDC_active' => '0',
                'form:table_dim_scrollState' => '0,0',
                'javax.faces.ViewState' => $this->viewstateLogin,
            ];

//           Obtém aeroporto de origem a partir da sigla do aeroporto
            $html = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf', $params, $this->mainHeader);

            $this->aeroportoOrigem = Utils::StringBetween($html['response'], 'data-item-value="', '"');

            $params2 = [
                'javax.faces.partial.ajax' => 'true',
                'javax.faces.source' => 'form:originId',
                'javax.faces.partial.execute' => 'form:originId',
                'javax.faces.behavior.event' => 'blur',
                'javax.faces.partial.event' => 'blur',
                'form' => 'form',
                'form:originId_input' => $this->aeroportoOrigem,
                'form:j_idt84_input' => 'ALL',
                'form:j_idt98_input' => 'TAM',
                'form:j_idt110' => 'P',
                'form:accordionDC_active' => '0',
                'form:table_dim_scrollState' => '0,0',
                'javax.faces.ViewState' => $this->viewstateLogin,
            ];

//           Define o aeroporto de origem com o nome obtido
            $html2 = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf', $params2, $this->mainHeader);
        } else {
            $this->Result = ['status' => 'ERROR', 'errorMsg' => (!isset($this->Result['errorMsg']) ? $aeroportoOrigemCode['errorMsg'] : $this->Result['errorMsg'] . ' | ' . $aeroportoOrigemCode['errorMsg'])];
        }
    }

    private function definePickup() {
        $params = [
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'form:j_idt30',
            'javax.faces.partial.execute' => 'form:j_idt30',
            'javax.faces.partial.render' => 'form:collectCepId',
            'javax.faces.behavior.event' => 'valueChange',
            'javax.faces.partial.event' => 'change',
            'form' => 'form',
            'form:originId_input' => $this->aeroportoOrigem,
            'form:j_idt30' => $this->coleta,
            'form:j_idt84_input' => 'ALL',
            'form:j_idt98_input' => 'TAM',
            'form:j_idt110' => 'P',
            'form:accordionDC_active' => '0',
            'form:table_dim_scrollState' => '0,0',
            'javax.faces.ViewState' => $this->viewstateLogin,
        ];

//            Define opção de coleta
        $html = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf', $params, $this->mainHeader);
    }

    private function defineDestinyAirport() {
        $aeroportoDestinoCode = $this->getDestinyAirport();
        if ($aeroportoDestinoCode['status'] == 'OK') {
            $params = [
                'javax.faces.partial.ajax' => 'true',
                'javax.faces.source' => 'form:destinationId',
                'javax.faces.partial.execute' => 'form:destinationId',
                'javax.faces.partial.render' => 'form:destinationId',
                'form:destinationId' => 'form:destinationId',
                'form:destinationId_query' => $aeroportoDestinoCode['airportCode'],
                'form' => 'form',
                'form:originId_input' => $this->aeroportoOrigem,
                'form:j_idt30' => $this->coleta,
                'form:collectCepId' => $this->cepOrigem,
                'form:destinationId_input' => $aeroportoDestinoCode['airportCode'],
                'form:j_idt84_input' => 'ALL',
                'form:j_idt98_input' => 'TAM',
                'form:j_idt110' => 'P',
                'form:accordionDC_active' => '0',
                'form:table_dim_scrollState' => '0,0',
                'javax.faces.ViewState' => $this->viewstateLogin,
            ];

//           Obtém aeroporto de destino a partir da sigla do aeroporto
            $html = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf', $params, $this->mainHeader);

            $this->aeroportoDestino = Utils::StringBetween($html['response'], 'data-item-value="', '"');


            $params2 = [
                'javax.faces.partial.ajax' => 'true',
                'javax.faces.source' => 'form:destinationId',
                'javax.faces.partial.execute' => 'form:destinationId',
                'javax.faces.behavior.event' => 'blur',
                'javax.faces.partial.event' => 'blur',
                'form' => 'form',
                'form:originId_input' => $this->aeroportoOrigem,
                'form:j_idt30' => $this->coleta,
                'form:collectCepId' => $this->cepOrigem,
                'form:destinationId_input' => $this->aeroportoDestino,
                'form:j_idt84_input' => 'ALL',
                'form:j_idt98_input' => 'TAM',
                'form:j_idt110' => 'P',
                'form:accordionDC_active' => '0',
                'form:table_dim_scrollState' => '0,0',
                'javax.faces.ViewState' => $this->viewstateLogin,
            ];
//           Define o aeroporto de destino com o nome obtido
            $html2 = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf', $params2, $this->mainHeader);
        } else {
            $this->Result = ['status' => 'ERROR', 'errorMsg' => (!isset($this->Result['errorMsg']) ? $aeroportoDestinoCode['errorMsg'] : $this->Result['errorMsg'] . ' | ' . $aeroportoDestinoCode['errorMsg'])];
        }
    }

    private function defineDelivery() {
        $params = [
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'form:j_idt44',
            'javax.faces.partial.execute' => 'form:j_idt44',
            'javax.faces.partial.render' => 'form:deliveryCepId',
            'javax.faces.behavior.event' => 'valueChange',
            'javax.faces.partial.event' => 'change',
            'form' => 'form',
            'form:originId_input' => $this->aeroportoOrigem,
            'form:j_idt30' => $this->coleta,
            'form:collectCepId' => $this->cepOrigem,
            'form:destinationId_input' => $this->aeroportoDestino,
            'form:j_idt44' => $this->entrega,
            'form:j_idt84_input' => 'ALL',
            'form:j_idt98_input' => 'TAM',
            'form:j_idt110' => 'P',
            'form:accordionDC_active' => '0',
            'form:table_dim_scrollState' => '0,0',
            'javax.faces.ViewState' => $this->viewstateLogin,
        ];

//            Define opção de entrega
        $html = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf', $params, $this->mainHeader);
    }

    private function defineWeight() {
        $params = [
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'form:btnPiecesDetail',
            'javax.faces.partial.execute' => '@all',
            'form:btnPiecesDetail' => 'form:btnPiecesDetail',
            'form' => 'form',
            'form:originId_input' => $this->aeroportoOrigem,
            'form:j_idt30' => $this->coleta,
            'form:collectCepId' => $this->cepOrigem,
            'form:destinationId_input' => $this->aeroportoDestino,
            'form:j_idt44' => $this->entrega,
            'form:deliveryCepId' => $this->cepDestino,
            'form:j_idt60_input' => '1',
            'form:idPackingType_input' => '21',
            'form:j_idt84_input' => 'ALL',
            'form:j_idt98_input' => 'TAM',
            'form:j_idt110' => 'P',
            'form:accordionDC_active' => '0',
            'form:table_dim_scrollState' => '0,0',
            'javax.faces.ViewState' => $this->viewstateLogin,
        ];
//            abre janela para definir peso e volume
        $html = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf', $params, $this->mainHeader);

        $params2 = [
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'form:j_idt221',
            'javax.faces.partial.execute' => '@all',
            'javax.faces.partial.render' => 'form:table_dim form:panel_form',
            'form:j_idt221' => 'form:j_idt221',
            'form' => 'form',
            'form:originId_input' => $this->aeroportoOrigem,
            'form:j_idt30' => $this->coleta,
            'form:collectCepId' => $this->cepOrigem,
            'form:destinationId_input' => $this->aeroportoDestino,
            'form:j_idt44' => $this->entrega,
            'form:deliveryCepId' => $this->cepDestino,
            'form:j_idt60_input' => '1',
            'form:idPackingType_input' => '21',
            'form:j_idt84_input' => 'ALL',
            'form:j_idt98_input' => 'TAM',
            'form:j_idt110' => 'P',
            'form:accordionDC_active' => '0',
            'form:idPackType_input' => '21',
            'form:idMeasures_input' => '100',
            'form:length' => '20',
            'form:width' => '20',
            'form:height' => '10',
            'form:quantity' => '1',
            'form:weight' => strval($this->psReal),
            'form:table_dim_scrollState' => '0,0',
            'javax.faces.ViewState' => $this->viewstateLogin,
        ];

//            define peso e volume
        $html2 = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf', $params2, $this->mainHeader);

        $params3 = [
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'form:j_idt277',
            'javax.faces.partial.execute' => '@all',
            'form:j_idt277' => 'form:j_idt277',
            'form' => 'form',
            'form:originId_input' => $this->aeroportoOrigem,
            'form:j_idt30' => $this->coleta,
            'form:collectCepId' => $this->cepOrigem,
            'form:destinationId_input' => $this->aeroportoDestino,
            'form:j_idt44' => $this->entrega,
            'form:deliveryCepId' => $this->cepDestino,
            'form:j_idt60_input' => '1',
            'form:idPackingType_input' => '21',
            'form:j_idt84_input' => 'ALL',
            'form:j_idt98_input' => 'TAM',
            'form:j_idt110' => 'P',
            'form:accordionDC_active' => '0',
            'form:idMeasures_input' => '100',
            'form:table_dim_scrollState' => '0,0',
            'javax.faces.ViewState' => $this->viewstateLogin,
        ];

//            adiciona volume configurado anteriormente
        $html3 = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf', $params3, $this->mainHeader);


        $params4 = [
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'form:dlgPieces',
            'javax.faces.partial.execute' => 'form:dlgPieces',
            'javax.faces.behavior.event' => 'close',
            'javax.faces.partial.event' => 'close',
            'form' => 'form',
            'form:originId_input' => $this->aeroportoOrigem,
            'form:j_idt30' => $this->coleta,
            'form:collectCepId' => $this->cepOrigem,
            'form:destinationId_input' => $this->aeroportoDestino,
            'form:j_idt44' => $this->entrega,
            'form:deliveryCepId' => $this->cepDestino,
            'form:j_idt60_input' => '1',
            'form:idPackingType_input' => '21',
            'form:j_idt84_input' => 'ALL',
            'form:j_idt98_input' => 'TAM',
            'form:j_idt110' => 'P',
            'form:accordionDC_active' => '0',
            'form:idMeasures_input' => '100',
            'form:table_dim_scrollState' => '0,0',
            'javax.faces.ViewState' => $this->viewstateLogin,
        ];

//            fecha janela para definir peso e volume
        $html4 = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf', $params4, $this->mainHeader);
    }

    private function defineInsurance() {
        $params = [
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'form:j_idt98',
            'javax.faces.partial.execute' => 'form:j_idt98',
            'javax.faces.partial.render' => 'form:declaredValueId',
            'javax.faces.behavior.event' => 'change',
            'javax.faces.partial.event' => 'change',
            'form' => 'form',
            'form:originId_input' => $this->aeroportoOrigem,
            'form:j_idt30' => $this->coleta,
            'form:collectCepId' => $this->cepOrigem,
            'form:destinationId_input' => $this->aeroportoDestino,
            'form:j_idt44' => $this->entrega,
            'form:deliveryCepId' => $this->cepDestino,
            'form:j_idt60_input' => '1',
            'form:idPackingType_input' => '21',
            'form:j_idt84_input' => 'ALL',
            'form:j_idt98_input' => $this->seguro,
            'form:j_idt110' => 'P',
            'form:accordionDC_active' => '0',
            'form:idMeasures_input' => '100',
            'form:table_dim_scrollState' => '0,0',
            'javax.faces.ViewState' => $this->viewstateLogin,
        ];

//            define opção de seguro para sem seguro
        $html = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf', $params, $this->mainHeader);
    }

    private function definePayer() {

        $params = [
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'form:btnCotizar',
            'javax.faces.partial.execute' => '@all',
            'form:btnCotizar' => 'form:btnCotizar',
            'form' => 'form',
            'form:originId_input' => $this->aeroportoOrigem,
            'form:j_idt30' => $this->coleta,
            'form:collectCepId' => $this->cepOrigem,
            'form:destinationId_input' => $this->aeroportoDestino,
            'form:j_idt44' => $this->entrega,
            'form:deliveryCepId' => $this->cepDestino,
            'form:j_idt60_input' => '1',
            'form:idPackingType_input' => '21',
            'form:j_idt84_input' => 'ALL',
            'form:j_idt98_input' => $this->seguro,
            'form:declaredValueId' => strval($this->vlMercadoria),
            'form:j_idt110' => 'P',
            'form:j_idt123_input' => $this->payer,
            'form:accordionDC_active' => '0',
            'form:idMeasures_input' => '100',
            'form:table_dim_scrollState' => '0,0',
            'javax.faces.ViewState' => $this->viewstateLogin,
        ];

//            define pagador e executa a consulta
        $html = Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf', $params, $this->mainHeader);
        if ($html['status'] === 200 && isset($html['cookie'])) {
            $this->cookieDisplay = "{$this->cookieLogin} {$html['cookie']}";
        }
    }

    private function getResponse() {
        $headers = [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Accept-Encoding: ",
            "Connection: keep-alive",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Host: secure.lancargo.com",
            "Cookie: {$this->cookieDisplay}",
            "Referer: https://secure.lancargo.com/eBusiness-web-1.0-view/private/CreateQuotation.jsf",
            "Upgrade-Insecure-Requests: 1",
        ];

//                abre janela com o resultado
        return Utils::CurlExec('https://secure.lancargo.com/eBusiness-web-1.0-view/private/DisplayQuotation.jsf', [], $headers);
    }

}
