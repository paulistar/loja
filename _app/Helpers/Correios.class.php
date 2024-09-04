<?php

/**
 * CORREIOS [ HELPER ]
 * Classe para realizar cotações no Webservice dos Correios
 * @copyright (c) year, Elizandro Echer - REVOSYS
 */
class Correios {

    /**
     * Informar o código da empresa nos Correios
     * @var string
     */
    private $nCdEmpresa;

    /**
     * Informar a senha de acesso nos Correios
     * @var string
     */
    private $sDsSenha;

    /**
     * Informar o código de Serviço do tipo de frete a ser consultado
     * @var string
     */
    private $nCdServico;

    /**
     * Informar o CEP de origem da mercadoria
     * @var string
     */
    private $sCepOrigem;

    /**
     * Informar o CEP de destino da mercadoria
     * @var string
     */
    private $sCepDestino;

    /**
     * Informar o peso total do pedido (decimal deve ser informado com ".")
     * @var string
     */
    private $nVlPeso;

    /**
     * Informar o código de formato da embalagem que será utilizado
     * @var int
     */
    private $nCdFormato;

    /**
     * OPCIONAL - Informar o comprimento da embalagem
     * @var float
     */
    private $nVlComprimento;

    /**
     * OPCIONAL - Informar a altura da embalagem
     * @var float
     */
    private $nVlAltura;

    /**
     * OPCIONAL - Informar a largura da embalagem
     * @var float
     */
    private $nVlLargura;

    /**
     * OPCIONAL - Informar o diâmetro da embalagem se formato definido como Rolo/Bobina
     * @var float
     */
    private $nVlDiametro;

    /**
     * Informar se a entrega deverá ser com serviço de mão própria
     * @var string
     */
    private $sCdMaoPropria;

    /**
     * OPCIONAL - Informar o valor total do pedido (decimal deve ser informado com ".")
     * @var float
     */
    private $nVlValorDeclarado;

    /**
     * Informar se a entrega deverá ser com serviço de aviso de recebimento
     * @var string
     */
    private $sCdAvisoRecebimento;

    /**
     * Informar a data do envio para cálculo no formato "d-m-Y"
     * @var string
     */
    private $sDtCalculo;

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
     * Peso Real será obtido a partir da função getPsReal()
     * @var string
     */
    private $psReal;

    /**
     * Informar se deverá utilizar o serviço de valor declarado dos Correios
     * @var boolean
     */
    private $valueDeclare;

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
     * Valor total de frete retornado pela consulta
     * @var string
     */
    private $vlTotalFrete;

    /**
     * Prazo de entrega retornado pela consulta
     * @var int
     */
    private $prazoEntrega;

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
     * Classe para realizar consulta no Web Service dos Correios
     * @param string $sCepOrigem Informar o CEP de origem da mercadoria
     * @param string $nCdEmpresa Informar o código da empresa nos Correios
     * @param string $sDsSenha Informar a senha de acesso nos Correios
     */
    public function __construct($sCepOrigem, $nCdEmpresa, $sDsSenha) {
        $this->setSCepOrigem($sCepOrigem);
        $this->setNCdEmpresa($nCdEmpresa);
        $this->setSDsSenha($sDsSenha);
    }

    public function setNCdEmpresa($nCdEmpresa) {
        $this->nCdEmpresa = $nCdEmpresa;
    }

    public function setSDsSenha($sDsSenha) {
        $this->sDsSenha = $sDsSenha;
    }

    public function setNCdServico($nCdServico) {
        $this->nCdServico = $nCdServico;
    }

    public function setSCepOrigem($sCepOrigem) {
        $this->sCepOrigem = preg_replace("/[^0-9]/", "", $sCepOrigem);
    }

    public function setSCepDestino($sCepDestino) {
        $this->sCepDestino = preg_replace("/[^0-9]/", "", $sCepDestino);
    }

    public function setNVlPeso($nVlPeso) {
        $this->nVlPeso = number_format($nVlPeso, 4, '.', '');
    }

    public function setNCdFormato($nCdFormato) {
        $this->nCdFormato = preg_replace("/[^0-9]/", "", $nCdFormato);
    }

    public function setNVlComprimento($nVlComprimento) {
        $this->nVlComprimento = (($this->quoteByWeight || empty($nVlComprimento) || $nVlComprimento < 16) ? 16 : number_format($nVlComprimento, 4, '.', ''));
    }

    public function setNVlAltura($nVlAltura) {
        $this->nVlAltura = (($this->quoteByWeight || empty($nVlAltura) || $nVlAltura < 2) ? 2 : number_format($nVlAltura, 4, '.', ''));
    }

    public function setNVlLargura($nVlLargura) {
        $this->nVlLargura = (($this->quoteByWeight || empty($nVlLargura) || $nVlLargura < 11) ? 11 : number_format($nVlLargura, 4, '.', ''));
    }

    public function setNVlDiametro($nVlDiametro) {
        $this->nVlDiametro = (($this->quoteByWeight || empty($nVlDiametro)) ? 0 : number_format($nVlDiametro, 4, '.', ''));
    }

    public function setSCdMaoPropria($sCdMaoPropria) {
        $this->sCdMaoPropria = $sCdMaoPropria;
    }

    public function setNVlValorDeclarado($nVlValorDeclarado) {
        $this->nVlValorDeclarado = (($this->valueDeclare && !empty($nVlValorDeclarado)) ? ($nVlValorDeclarado < 18) ? '18.00' : number_format(preg_replace('/[^0-9.]*/', '', $nVlValorDeclarado), 2, '.', '') : 0);
    }

    public function setSCdAvisoRecebimento($sCdAvisoRecebimento) {
        $this->sCdAvisoRecebimento = preg_replace("/[^0-9]/", "", $sCdAvisoRecebimento);
    }

    public function setSDtCalculo($sDtCalculo) {
        $this->sDtCalculo = (!empty($sDtCalculo) ? $sDtCalculo : date('d/m/Y'));
    }

    public function setTotalvolume($totalvolume) {
        $this->totalvolume = (!empty($totalvolume) ? number_format($totalvolume, 4, '.', '') : '');
    }

    public function setValueDeclare($valueDeclare) {
        $this->valueDeclare = $valueDeclare;
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

    function setQuoteByWeight($quoteByWeight) {
        $this->quoteByWeight = $quoteByWeight;
    }

    /**
     * Método para setar informações para obter cotação de frete no webservice dos Correios
     * @param string $sCepDestino Informar o CEP de destino da mercadoria
     * @param float $nVlPeso Informar o peso total do pedido (decimal deve ser informado com ".")
     * @param float $nVlValorDeclarado OPCIONAL - Informar o valor total do pedido (decimal deve ser informado com ".")
     * @param int $totalvolume OPCIONAL (CASO NÃO INFORMADO A COTAÇÃO SERÁ SOMENTE POR PESO) - Informar o volume total do pedido (largura x comprimento x altura x quantidade) em m para obter o peso cubado (decimal deve ser informado com ".")
     * @param string $nCdServico Informar o código de Serviço do tipo de frete a ser consultado
     * @param int $nCdFormato Informar o código de formato da embalagem que será utilizado
     * @param string $sCdMaoPropria Informar se a entrega deverá ser com serviço de mão própria
     * @param string $sCdAvisoRecebimento Informar se a entrega deverá ser com serviço de aviso de recebimento
     * @param float $nVlComprimento OPCIONAL - Informar o comprimento da embalagem
     * @param float $nVlAltura OPCIONAL - Informar a altura da embalagem
     * @param float $nVlLargura OPCIONAL - Informar a largura da embalagem
     * @param float $nVlDiametro OPCIONAL - Informar o diâmetro da embalagem se formato definido como Rolo/Bobina
     * @param date $sDtCalculo Informar a data do envio para cálculo no formato "d-m-Y"
     * @param int $valueDeclare Informar se deverá utilizar o serviço de valor declarado dos Correios
     * @param int $quoteByWeight Informar se a cotação deverá ser realizada somente por peso ou deverá ser calculado o peso cubado
     * @param float $additionalPercent Informar o valor percentual a ser somado ao valor do frete
     * @param float $additionalCharge Informar o valor fixo a ser somado ao valor do frete
     * @param int $shipmentDelay Informar o número de dias a ser somado ao prazo de entrega
     */
    public function setQuoteData($sCepDestino, $nVlPeso, $nVlValorDeclarado, $totalvolume, $nCdServico, $nCdFormato, $sCdMaoPropria, $sCdAvisoRecebimento, $valueDeclare = NULL, $quoteByWeight = NULL, $additionalPercent = NULL, $additionalCharge = NULL, $shipmentDelay = NULL, $nVlComprimento = NULL, $nVlAltura = NULL, $nVlLargura = NULL, $nVlDiametro = NULL, $sDtCalculo = NULL) {
        $this->setValueDeclare($valueDeclare);
        $this->setQuoteByWeight($quoteByWeight);
        $this->setAdditionalPercent($additionalPercent);
        $this->setAdditionalCharge($additionalCharge);
        $this->setShipmentDelay($shipmentDelay);
        $this->setSCepDestino($sCepDestino);
        $this->setNVlPeso($nVlPeso);
        $this->setNVlValorDeclarado($nVlValorDeclarado);
        $this->setTotalvolume($totalvolume);
        $this->setNCdServico($nCdServico);
        $this->setNCdFormato($nCdFormato);
        $this->setSCdMaoPropria($sCdMaoPropria);
        $this->setSCdAvisoRecebimento($sCdAvisoRecebimento);
        $this->setNVlComprimento($nVlComprimento);
        $this->setNVlAltura($nVlAltura);
        $this->setNVlLargura($nVlLargura);
        $this->setNVlDiametro($nVlDiametro);
        $this->setSDtCalculo($sDtCalculo);
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
        if (!empty($this->totalvolume) && !empty($this->nVlPeso) && !$this->quoteByWeight):
            $this->cubicweight = ($this->totalvolume * 1000000) / 6000;
            if ($this->cubicweight > 5 && $this->cubicweight > $this->nVlPeso):
                $this->psReal = number_format($this->cubicweight, 4, '.', '');
            else:
                $this->psReal = $this->nVlPeso;
            endif;
        else:
            (!empty($this->nVlPeso) ? $this->psReal = $this->nVlPeso : null);
        endif;
    }

    private function buildQuoteRequest($request) {
        return array(
            $request =>
            array(
                'nCdEmpresa' => $this->nCdEmpresa,
                'sDsSenha' => $this->sDsSenha,
                'nCdServico' => $this->nCdServico,
                'sCepOrigem' => $this->sCepOrigem,
                'sCepDestino' => $this->sCepDestino,
                'nVlPeso' => $this->psReal,
                'nCdFormato' => $this->nCdFormato,
                'nVlComprimento' => $this->nVlComprimento,
                'nVlAltura' => $this->nVlAltura,
                'nVlLargura' => $this->nVlLargura,
                'nVlDiametro' => $this->nVlDiametro,
                'sCdMaoPropria' => $this->sCdMaoPropria,
                'nVlValorDeclarado' => $this->nVlValorDeclarado,
                'sCdAvisoRecebimento' => $this->sCdAvisoRecebimento,
                'sDtCalculo' => $this->sDtCalculo
            )
        );
    }

    private function makeQuoteRequest() {
        $client = new SoapClient('http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx?WSDL');
        $request = 'CalcPrecoPrazoData';
        try {
            $resultado = $client->__soapCall($request, $this->buildQuoteRequest($request));
            $this->parseQuoteResult($resultado);
        } catch (SoapFault $e) {
            $this->Result = array('status' => 'ERROR', 'errorMsg' => $e->faultstring);
        }
    }

    private function parseQuoteResult($result)
    {
        if ($result['status'] === 200) {
            $resultado = simplexml_load_string($result['response']);

            var_dump($resultado);
            die;

            if (is_array($resultado->CalcPrecoPrazoDataResult->Servicos->cServico)):
                foreach ($resultado->CalcPrecoPrazoDataResult->Servicos->cServico as $row):
                    $this->vlTotalFrete = number_format(str_replace(',', '.', str_replace('.', '', $row->Valor)), 2, '.', '');
                    $this->vlTotalFrete = ($this->vlTotalFrete / (1 - ($this->additionalPercent / 100))) + $this->additionalCharge;
                    $this->vlTotalFrete = number_format($this->vlTotalFrete, 2, '.', '');

                    $this->prazoEntrega = $row->PrazoEntrega;
                    $this->prazoEntrega = $this->prazoEntrega + $this->shipmentDelay;

                    if ($row->Erro === '0') {
                        $this->Result[] = array(
                            'status' => 'OK',
                            'shipcode' => $row->Codigo,
                            'valorfrete' => $this->vlTotalFrete,
                            'prazoentrega' => $this->prazoEntrega,
                            'ValorMaoPropria' => number_format(str_replace(',', '.', str_replace('.', '', $row->ValorMaoPropria)), 2, '.', ''),
                            'ValorAvisoRecebimento' => number_format(str_replace(',', '.', str_replace('.', '', $row->ValorAvisoRecebimento)), 2, '.', ''),
                            'ValorValorDeclarado' => number_format(str_replace(',', '.', str_replace('.', '', $row->ValorValorDeclarado)), 2, '.', ''),
                            'EntregaDomiciliar' => $row->EntregaDomiciliar,
                            'EntregaSabado' => $row->EntregaSabado,
                            'Erro' => $row->Erro,
                            'MsgErro' => $row->MsgErro,
                            'ValorSemAdicionais' => number_format(str_replace(',', '.', str_replace('.', '', $row->ValorSemAdicionais)), 2, '.', ''),
                            'obsFim' => $row->obsFim
                        );
                    } else {
                        $this->Result[] = array(
                            'status' => 'ERROR',
                            'errorMsg' => $row->MsgErro,
                            'shipcode' => $row->Codigo,
                            'valorfrete' => $this->vlTotalFrete,
                            'prazoentrega' => $this->prazoEntrega,
                            'ValorMaoPropria' => number_format(str_replace(',', '.', str_replace('.', '', $row->ValorMaoPropria)), 2, '.', ''),
                            'ValorAvisoRecebimento' => number_format(str_replace(',', '.', str_replace('.', '', $row->ValorAvisoRecebimento)), 2, '.', ''),
                            'ValorValorDeclarado' => number_format(str_replace(',', '.', str_replace('.', '', $row->ValorValorDeclarado)), 2, '.', ''),
                            'EntregaDomiciliar' => $row->EntregaDomiciliar,
                            'EntregaSabado' => $row->EntregaSabado,
                            'Erro' => $row->Erro,
                            'MsgErro' => $row->MsgErro,
                            'ValorSemAdicionais' => number_format(str_replace(',', '.', str_replace('.', '', $row->ValorSemAdicionais)), 2, '.', ''),
                            'obsFim' => $row->obsFim
                        );
                    }
                endforeach;
            else:
                $this->vlTotalFrete = number_format(str_replace(',', '.', str_replace('.', '', $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->Valor)), 2, '.', '');
                $this->vlTotalFrete = ($this->vlTotalFrete / (1 - ($this->additionalPercent / 100))) + $this->additionalCharge;
                $this->vlTotalFrete = number_format($this->vlTotalFrete, 2, '.', '');

                $this->prazoEntrega = $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->PrazoEntrega;
                $this->prazoEntrega = $this->prazoEntrega + $this->shipmentDelay;

                if ($resultado->CalcPrecoPrazoDataResult->Servicos->cServico->Erro === '0') {
                    $this->Result = array(
                        'status' => 'OK',
                        'shipcode' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->Codigo,
                        'valorfrete' => $this->vlTotalFrete,
                        'prazoentrega' => $this->prazoEntrega,
                        'ValorMaoPropria' => number_format(str_replace(',', '.', str_replace('.', '', $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->ValorMaoPropria)), 2, '.', ''),
                        'ValorAvisoRecebimento' => number_format(str_replace(',', '.', str_replace('.', '', $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->ValorAvisoRecebimento)), 2, '.', ''),
                        'ValorValorDeclarado' => number_format(str_replace(',', '.', str_replace('.', '', $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->ValorValorDeclarado)), 2, '.', ''),
                        'EntregaDomiciliar' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->EntregaDomiciliar,
                        'EntregaSabado' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->EntregaSabado,
                        'Erro' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->Erro,
                        'MsgErro' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->MsgErro,
                        'ValorSemAdicionais' => number_format(str_replace(',', '.', str_replace('.', '', $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->ValorSemAdicionais)), 2, '.', ''),
                        'obsFim' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->obsFim
                    );
                } else {
                    $this->Result = array(
                        'status' => 'ERROR',
                        'errorMsg' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->MsgErro,
                        'shipcode' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->Codigo,
                        'valorfrete' => $this->vlTotalFrete,
                        'prazoentrega' => $this->prazoEntrega,
                        'ValorMaoPropria' => number_format(str_replace(',', '.', str_replace('.', '', $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->ValorMaoPropria)), 2, '.', ''),
                        'ValorAvisoRecebimento' => number_format(str_replace(',', '.', str_replace('.', '', $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->ValorAvisoRecebimento)), 2, '.', ''),
                        'ValorValorDeclarado' => number_format(str_replace(',', '.', str_replace('.', '', $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->ValorValorDeclarado)), 2, '.', ''),
                        'EntregaDomiciliar' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->EntregaDomiciliar,
                        'EntregaSabado' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->EntregaSabado,
                        'Erro' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->Erro,
                        'MsgErro' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->MsgErro,
                        'ValorSemAdicionais' => number_format(str_replace(',', '.', str_replace('.', '', $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->ValorSemAdicionais)), 2, '.', ''),
                        'obsFim' => $resultado->CalcPrecoPrazoDataResult->Servicos->cServico->obsFim
                    );
                }
            endif;
        }
    }

}
