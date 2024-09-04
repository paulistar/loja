<?php

/**
 * JADLOG [ HELPER ]
 * Classe para realizar cotações no Webservice da transportadora JadLog
 * @copyright (c) year, Elizandro Echer - REVOSYS
 */
class Jadlog {

    /**
     * Informar a modalidade do frete. Deve conter apenas números
     * @var int
     */
    private $vModalidade;

    /**
     * Informar a senha de acesso à área de Serviços on-line do site da JADLOG
     * @var string
     */
    private $Password;

    /**
     * Informar Tipo do Seguro ―N‖ normal ―A‖ apólice própria
     * @var string
     */
    private $vSeguro;

    /**
     * Informar o valor total do pedido
     * @var string
     */
    private $vVlDec;

    /**
     * Informar o valor da coleta negociado com representante JADLOG.
     * @var float
     */
    private $vVlColeta;

    /**
     * Informar o CEP de origem da mercadoria
     * @var string
     */
    private $vCepOrig;

    /**
     * Informar o CEP de destino da mercadoria
     * @var string
     */
    private $vCepDest;

    /**
     * Peso Real será obtido a partir da função getPsReal()
     * @var float
     */
    private $psReal;

    /**
     * Informar se Frete a pagar no destino, ―S‖ = sim ―N‖ = não.
     * @var string
     */
    private $vFrap;

    /**
     * Informar o Tipo de entrega ―R‖ retira unidade JADLOG, ―D‖ domicilio.
     * @var string
     */
    private $vEntrega;

    /**
     * Informar o CNPJ do contratante
     * @var string
     */
    private $vCnpj;

    /**
     * Informar o peso total do pedido (decimal deve ser informado com ".")
     * @var float
     */
    private $totalweight;

    /**
     * OPCIONAL (CASO NÃO INFORMADO A COTAÇÃO SERÁ SOMENTE POR PESO) - Informar o volume total do pedido (largura x comprimento x altura x quantidade) em m para obter o peso cubado (decimal deve ser informado com ".")
     * @var float
     */
    private $totalvolume;

    /**
     * Peso cubado calculado pela função getPsReal
     * @var float
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
     * Valor total de frete retornado pela consulta
     * @var string
     */
    private $vlTotalFrete;

    /**
     * Código do tipo de envio
     * @var string
     */
    private $ShipCode;

    /**
     * Resultado retornado pela classe
     * @return string
     */
    private $Result;

    /**
     * Classe para realizar consulta no Web Service da JadLog
     * @param string $Password Informar a senha de acesso à área de Serviços on-line do site da JADLOG
     * @param number $vCnpj Informar o CNPJ do contratante
     * @param number $vCepOrig Informar o CEP de origem da mercadoria
     */
    public function __construct($Password, $vCnpj, $vCepOrig) {
        $this->setVCnpj($vCnpj);
        $this->setPassword($Password);
        $this->setVCepOrig($vCepOrig);
    }

    public function setVModalidade($vModalidade) {
        $this->vModalidade = $vModalidade;
    }

    public function setPassword($Password) {
        $this->Password = $Password;
    }

    public function setVSeguro($vSeguro) {
        $this->vSeguro = $vSeguro;
    }

    public function setVVlDec($vVlDec) {
        $this->vVlDec = number_format(preg_replace('/[^0-9.]*/', '', $vVlDec), 2, '.', '');
    }

    public function setVVlColeta($vVlColeta) {
        $this->vVlColeta = number_format(preg_replace('/[^0-9.]*/', '', $vVlColeta), 2, '.', '');
    }

    public function setVCepOrig($vCepOrig) {
        $this->vCepOrig = preg_replace("/[^0-9]/", "", $vCepOrig);
    }

    public function setVCepDest($vCepDest) {
        $this->vCepDest = preg_replace("/[^0-9]/", "", $vCepDest);
    }

    public function setVFrap($vFrap) {
        $this->vFrap = $vFrap;
    }

    public function setVEntrega($vEntrega) {
        $this->vEntrega = $vEntrega;
    }

    public function setVCnpj($vCnpj) {
        $this->vCnpj = preg_replace("/[^0-9]/", "", $vCnpj);
    }

    public function setTotalweight($totalweight) {
        $this->totalweight = number_format($totalweight, 4, '.', '');
    }

    public function setTotalvolume($totalvolume) {
        $this->totalvolume = (!empty($totalvolume) ? number_format($totalvolume, 4, '.', '') : '');
    }

    function setQuoteByWeight($quoteByWeight) {
        $this->quoteByWeight = $quoteByWeight;
    }

    public function setAdditionalCharge($additionalCharge) {
        $this->additionalCharge = $additionalCharge;
    }

    public function setAdditionalPercent($additionalPercent) {
        $this->additionalPercent = $additionalPercent;
    }

    /**
     * Método para setar informações para obter cotação de frete no webservice da Jadlog
     * @param string $vCepDest Informar o CEP de destino da mercadoria
     * @param float $totalweight Informar o peso total do pedido (decimal deve ser informado com ".")
     * @param float $vVlDec Informar o valor total do pedido (decimal deve ser informado com ".")
     * @param float $totalvolume OPCIONAL (CASO NÃO INFORMADO A COTAÇÃO SERÁ SOMENTE POR PESO) - Informar o volume total do pedido (largura x comprimento x altura x quantidade) em m para obter o peso cubado (decimal deve ser informado com ".")
     * @param string $vFrap Informar se Frete a pagar no destino, ―S‖ = sim ―N‖ = não.
     * @param string $vEntrega Informar o Tipo de entrega ―R‖ retira unidade JADLOG, ―D‖ domicilio.
     * @param float $vVlColeta Informar o valor da coleta negociado com representante JADLOG.
     * @param int $vModalidade Informar a modalidade do frete. Deve conter apenas números
     * @param string $vSeguro Informar Tipo do Seguro ―N‖ normal ―A‖ apólice própria
     * @param int $quoteByWeight Informar se a cotação deverá ser realizada somente por peso ou deverá ser calculado o peso cubado
     * @param float $additionalPercent Informar o valor percentual a ser somado ao valor do frete
     * @param float $additionalCharge Informar o valor fixo a ser somado ao valor do frete
     */
    public function setQuoteData($vCepDest, $totalweight, $vVlDec, $totalvolume, $vFrap, $vEntrega, $vVlColeta, $vModalidade, $vSeguro, $quoteByWeight = NULL, $additionalPercent = NULL, $additionalCharge = NULL) {
        $this->setQuoteByWeight($quoteByWeight);
        $this->setAdditionalPercent($additionalPercent);
        $this->setAdditionalCharge($additionalCharge);
        $this->setVCepDest($vCepDest);
        $this->setTotalweight($totalweight);
        $this->setVVlDec($vVlDec);
        $this->setTotalvolume($totalvolume);
        $this->setVFrap($vFrap);
        $this->setVEntrega($vEntrega);
        $this->setVVlColeta($vVlColeta);
        $this->setVModalidade($vModalidade);
        $this->setVSeguro($vSeguro);
    }

    public function getQuote() {
        $this->getPsReal();
        $this->makeQuoteRequest();
        return $this->getResult();
    }

    public function getResult() {
        return $this->Result;
    }

    /**
     * ****************************************
     * ******* PRIVATE METHODS  ********
     * ****************************************
     */
    private function getPsReal() {
        if (!empty($this->totalvolume) && !empty($this->totalweight) && !$this->quoteByWeight):
            if ($this->vModalidade == 0 || $this->vModalidade == 7 || $this->vModalidade == 9 || $this->vModalidade == 10 || $this->vModalidade == 12):
                $this->cubicweight = ($this->totalvolume * 1000000) / 6000;
                if ($this->cubicweight > $this->totalweight):
                    $this->psReal = number_format($this->cubicweight, 4, '.', '');
                else:
                    $this->psReal = $this->totalweight;
                endif;
            else:
                $this->cubicweight = ($this->totalvolume * 1000000) / 3333;
                if ($this->cubicweight > $this->totalweight):
                    $this->psReal = number_format($this->cubicweight, 4, '.', '');
                else:
                    $this->psReal = $this->totalweight;
                endif;
            endif;
        else:
            (!empty($this->totalweight) ? $this->psReal = $this->totalweight : null);
        endif;
    }

    private function buildQuoteRequest($request) {
        return array(
            $request =>
            array(
                'vModalidade' => $this->vModalidade,
                'Password' => $this->Password,
                'vSeguro' => $this->vSeguro,
                'vVlDec' => $this->vVlDec,
                'vVlColeta' => $this->vVlColeta,
                'vCepOrig' => $this->vCepOrig,
                'vCepDest' => $this->vCepDest,
                'vPeso' => $this->psReal,
                'vFrap' => $this->vFrap,
                'vEntrega' => $this->vEntrega,
                'vCnpj' => $this->vCnpj
            )
        );
    }

    private function makeQuoteRequest() {
        $client = new SoapClient('http://www.jadlog.com.br:8080/JadlogEdiWs/services/ValorFreteBean?wsdl');
        $request = 'valorar';
        try {
            $resultado = $client->__soapCall($request, $this->buildQuoteRequest($request));
            $this->parseQuoteResult($resultado);
        } catch (SoapFault $e) {
            $this->Result = array('status' => 'ERROR', 'errorMsg' => $e->faultstring);
        }
    }

    private function parseQuoteResult($resultado) {
        $xml = simplexml_load_string($resultado->valorarReturn);
        if ($this->vModalidade == 0):
            $this->ShipCode = '10010';
        elseif ($this->vModalidade == 3):
            $this->ShipCode = '10011';
        elseif ($this->vModalidade == 4):
            $this->ShipCode = '10012';
        elseif ($this->vModalidade == 5):
            $this->ShipCode = '10013';
        elseif ($this->vModalidade == 6):
            $this->ShipCode = '10014';
        elseif ($this->vModalidade == 7):
            $this->ShipCode = '10015';
        elseif ($this->vModalidade == 9):
            $this->ShipCode = '10016';
        elseif ($this->vModalidade == 10):
            $this->ShipCode = '10017';
        elseif ($this->vModalidade == 12):
            $this->ShipCode = '10018';
        elseif ($this->vModalidade == 14):
            $this->ShipCode = '10019';
        else:
            $this->ShipCode = '';
        endif;

        if ($xml->Jadlog_Valor_Frete->Mensagem == 'Valor do Frete') {
            $this->vlTotalFrete = number_format(preg_replace('/[^0-9.]*/', '', str_replace(',', '.', str_replace('.', '', $xml->Jadlog_Valor_Frete->Retorno))), 2, '.', '');
            $this->vlTotalFrete = ($this->vlTotalFrete / (1 - ($this->additionalPercent / 100))) + $this->additionalCharge;
            $this->vlTotalFrete = number_format($this->vlTotalFrete, 2, '.', '');
            $this->Result = array(
                'status' => 'OK',
                'shipcode' => $this->ShipCode,
                'valorfrete' => $this->vlTotalFrete,
            );
        } else {
            $this->Result = array('status' => 'ERROR', 'errorMsg' => (string) $xml->Jadlog_Valor_Frete->Mensagem, 'shipcode' => $this->ShipCode);
        }
    }

}
