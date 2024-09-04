<?php

/**
 * TNT [ HELPER ]
 * Classe para realizar cotações no Webservice da transportadora TNT Mercúrio
 * @copyright (c) year, Elizandro Echer - REVOSYS
 */
class Tnt {

    /**
     * Informar o código de divisão do cliente
     * @var int
     */
    private $cdDivisaoCliente;

    /**
     * Informar o CEP de destino da mercadoria
     * @var string
     */
    private $cepDestino;

    /**
     * Informar o CEP de origem da mercadoria
     * @var string
     */
    private $cepOrigem;

    /**
     * Informar o login de acesso na TNT
     * @var string
     */
    private $login;

    /**
     * OPCIONAL - Informar o CPF/CNPJ da pessoa destino
     * @var string
     */
    private $nrIdentifClienteDest;

    /**
     * Informar o CPF/CNPJ da pessoa remetente
     * @var string
     */
    private $nrIdentifClienteRem;

    /**
     * OPCIONAL -  Informar a Inscrição Estadual da pessoa destino
     * @var string
     */
    private $nrInscricaoEstadualDestinatario;

    /**
     * Informar a Inscrição Estadual da pessoa remetente
     * @var string
     */
    private $nrInscricaoEstadualRemetente;

    /**
     * Peso Real será obtido a partir da função getPsReal()
     * @var string
     */
    private $psReal;

    /**
     * Informar a senha de acesso na TNT
     * @var string
     */
    private $senha;

    /**
     * Informar o Tipo de Frete - Tipos de fretes ('C' => 'CIF' ou 'F' => 'FOB')
     * @var string
     */
    private $tpFrete;

    /**
     * OPCIONAL - Informar o tipo de pessoa destino ('J' => 'Jurídica' (padrão) ou 'F' => 'Física')
     * @var string
     */
    private $tpPessoaDestinatario;

    /**
     * Informar o tipo de pessoa remetente
     * @var string
     */
    private $tpPessoaRemetente;

    /**
     * Informar o Tipo de Serviço - Tipos de serviços ('RNC' => 'Rodoviário Nacional Convencional' ou 'ANC' => 'Aéreo Nacional Convencional')
     * @var string
     */
    private $tpServico;

    /**
     * OPCIONAL - Informar o tipo de situação tributária do destinatário ('CI' => 'Contribuinte Incentivado' ou 'ON' => 'Órgão Público Não Contribuinte' ou 'PN' => 'Produtor Rural Não Contribuinte' ou 'MN' => 'ME/EPP/Simples Nacional Não Contribuinte' ou 'CN' => 'Cia. Mista Não Contribuinte' ou 'OF' => 'Órgão Público - Progr. Fortalecimento Modernização Estadual' ou 'CM' => 'Cia. Mista Contribuinte' ou 'CO' => 'Contribuinte' ou 'ME' => 'ME/EPP/Simples Nacional Contribuinte' ou 'NC' => 'Não Contribuinte' ou 'OP' => 'Órgão Público Contribuinte' ou 'PR' => 'Produtor Rural Contribuinte')
     * @var string
     */
    private $tpSituacaoTributariaDestinatario;

    /**
     * Informar o tipo de situação tributária do remetente
     * @var string
     */
    private $tpSituacaoTributariaRemetente;

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
     * Informar o número da NFe a ser rastreada
     * @var int
     */
    private $nfe;

    /**
     * Informar a série da NFe a ser rastreada
     * @var int
     */
    private $serieNfe;

    /**
     * OPCIONAL - Informar o número do pedido a ser rastreado
     * @var int
     */
    private $pedido;

    /**
     * Prazo de entrega retornado pela consulta
     * @var int
     */
    private $prazoEntrega;

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
     * 
     * Classe para realizar consultas no Web Service da TNT Mercúrio
     * @param string $login Informar o login de acesso na TNT
     * @param string $senha Informar a senha de acesso na TNT
     * @param int $cdDivisaoCliente Informar o código de divisão do cliente
     * @param string $tpPessoaRemetente Informar o tipo de pessoa remetente - Informar o tipo de pessoa destino ('J' => 'Jurídica' (padrão) ou 'F' => 'Física')
     * @param string $tpSituacaoTributariaRemetente Informar o tipo de situação tributária do remetente
     * @param string $nrIdentifClienteRem Informar o CPF/CNPJ da pessoa remetente
     * @param string $nrInscricaoEstadualRemetente Informar a Inscrição Estadual da pessoa remetente    
     * @param string $cepOrigem Informar o CEP de origem da mercadoria
     * 
     */
    public function __construct($login, $senha, $cdDivisaoCliente, $tpPessoaRemetente, $tpSituacaoTributariaRemetente, $nrIdentifClienteRem, $nrInscricaoEstadualRemetente, $cepOrigem) {
        $this->setLogin($login);
        $this->setSenha($senha);
        $this->setCdDivisaoCliente($cdDivisaoCliente);
        $this->setTpPessoaRemetente($tpPessoaRemetente);
        $this->setTpSituacaoTributariaRemetente($tpSituacaoTributariaRemetente);
        $this->setNrIdentifClienteRem($nrIdentifClienteRem);
        $this->setNrInscricaoEstadualRemetente($nrInscricaoEstadualRemetente);
        $this->setCepOrigem($cepOrigem);
    }

    public function setCdDivisaoCliente($cdDivisaoCliente) {
        $this->cdDivisaoCliente = $cdDivisaoCliente;
    }

    public function setCepDestino($cepDestino) {
        $this->cepDestino = preg_replace("/[^0-9]/", "", $cepDestino);
    }

    public function setCepOrigem($cepOrigem) {
        $this->cepOrigem = preg_replace("/[^0-9]/", "", $cepOrigem);
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function setNrIdentifClienteDest($nrIdentifClienteDest) {
        $this->nrIdentifClienteDest = preg_replace("/[^0-9]/", "", $nrIdentifClienteDest);
    }

    public function setNrIdentifClienteRem($nrIdentifClienteRem) {
        $this->nrIdentifClienteRem = preg_replace("/[^0-9]/", "", $nrIdentifClienteRem);
    }

    public function setNrInscricaoEstadualDestinatario($nrInscricaoEstadualDestinatario) {
        $this->nrInscricaoEstadualDestinatario = preg_replace("/[^0-9]/", "", $nrInscricaoEstadualDestinatario);
    }

    public function setNrInscricaoEstadualRemetente($nrInscricaoEstadualRemetente) {
        $this->nrInscricaoEstadualRemetente = preg_replace("/[^0-9]/", "", $nrInscricaoEstadualRemetente);
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }

    public function setTpFrete($tpFrete) {
        $this->tpFrete = (string) $tpFrete;
    }

    public function setTpPessoaDestinatario($tpPessoaDestinatario) {
        $this->tpPessoaDestinatario = (string) $tpPessoaDestinatario;
    }

    public function setTpPessoaRemetente($tpPessoaRemetente) {
        $this->tpPessoaRemetente = (string) $tpPessoaRemetente;
    }

    public function setTpServico($tpServico) {
        $this->tpServico = (string) $tpServico;
    }

    public function setTpSituacaoTributariaDestinatario($tpSituacaoTributariaDestinatario) {
        $this->tpSituacaoTributariaDestinatario = (string) $tpSituacaoTributariaDestinatario;
    }

    public function setTpSituacaoTributariaRemetente($tpSituacaoTributariaRemetente) {
        $this->tpSituacaoTributariaRemetente = $tpSituacaoTributariaRemetente;
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

    public function setNfe($nfe) {
        $this->nfe = (int) $nfe;
    }

    public function setSerieNfe($serieNfe) {
        $this->serieNfe = (int) $serieNfe;
    }

    public function setPedido($pedido) {
        $this->pedido = (int) $pedido;
    }

    /**
     * Método para setar informações para obter cotação de frete no webservice da TNT Mercúrio
     * @param string $cepDestino Informar o CEP de destino da mercadoria
     * @param string $totalweight Informar o peso total do pedido (decimal deve ser informado com ".")
     * @param string $vlMercadoria Informar o valor total do pedido (decimal deve ser informado com ".")
     * @param string $totalvolume OPCIONAL (CASO NÃO INFORMADO A COTAÇÃO SERÁ SOMENTE POR PESO) - Informar o volume total do pedido (largura x comprimento x altura x quantidade) em m para obter o peso cubado (decimal deve ser informado com ".")
     * @param string $tpFrete Informar o Tipo de Frete - Tipos de fretes ('C' => 'CIF' ou 'F' => 'FOB')
     * @param string $tpServico Informar o Tipo de Serviço - Tipos de fretes ('RNC' => 'Rodoviário Nacional Convencional' ou 'ANC' => 'Aéreo Nacional Convencional')
     * @param string $tpPessoaDestinatario OPCIONAL - Informar o tipo de pessoa destino ('J' => 'Jurídica' (padrão) ou 'F' => 'Física')
     * @param string $tpSituacaoTributariaDestinatario OPCIONAL - Informar o tipo de situação tributária do destinatário ('CI' => 'Contribuinte Incentivado' ou 'ON' => 'Órgão Público Não Contribuinte' ou 'PN' => 'Produtor Rural Não Contribuinte' ou 'MN' => 'ME/EPP/Simples Nacional Não Contribuinte' ou 'CN' => 'Cia. Mista Não Contribuinte' ou 'OF' => 'Órgão Público - Progr. Fortalecimento Modernização Estadual' ou 'CM' => 'Cia. Mista Contribuinte' ou 'CO' => 'Contribuinte' ou 'ME' => 'ME/EPP/Simples Nacional Contribuinte' ou 'NC' => 'Não Contribuinte' ou 'OP' => 'Órgão Público Contribuinte' ou 'PR' => 'Produtor Rural Contribuinte')
     * @param string $nrIdentifClienteDest OPCIONAL - Informar o CPF/CNPJ da pessoa destino
     * @param string $nrInscricaoEstadualDestinatario OPCIONAL -  Informar a Inscrição Estadual da pessoa destino
     * @param int $quoteByWeight Informar se a cotação deverá ser realizada somente por peso ou deverá ser calculado o peso cubado
     * @param float $additionalPercent Informar o valor percentual a ser somado ao valor do frete
     * @param float $additionalCharge Informar o valor fixo a ser somado ao valor do frete
     * @param int $shipmentDelay Informar o número de dias a ser somado ao prazo de entrega
     * 
     */
    public function setQuoteData($cepDestino, $totalweight, $vlMercadoria, $totalvolume, $tpFrete, $tpServico, $tpPessoaDestinatario, $tpSituacaoTributariaDestinatario, $nrIdentifClienteDest, $nrInscricaoEstadualDestinatario = NULL, $quoteByWeight = NULL, $additionalPercent = NULL, $additionalCharge = NULL, $shipmentDelay = NULL) {
        $this->setCepDestino($cepDestino);
        $this->setTotalweight($totalweight);
        $this->setVlMercadoria($vlMercadoria);
        $this->setTotalvolume($totalvolume);
        $this->setTpFrete($tpFrete);
        $this->setTpServico($tpServico);
        $this->setTpPessoaDestinatario($tpPessoaDestinatario);
        $this->setTpSituacaoTributariaDestinatario($tpSituacaoTributariaDestinatario);
        $this->setNrIdentifClienteDest($nrIdentifClienteDest);
        $this->setNrInscricaoEstadualDestinatario($nrInscricaoEstadualDestinatario);
        $this->setQuoteByWeight($quoteByWeight);
        $this->setAdditionalPercent($additionalPercent);
        $this->setAdditionalCharge($additionalCharge);
        $this->setShipmentDelay($shipmentDelay);
    }

    /**
     * Método para setar informações para obter informações de rastreio no webservice da TNT Mercúrio
     * @param int $nf Informar o número da NFe a ser rastreada
     * @param int $nfSerie Informar a série da NFe a ser rastreada
     * @param int $pedido OPCIONAL - Informar o número do pedido a ser rastreado
     */
    public function setTrackData($nfe, $serieNfe, $pedido = NULL) {
        $this->setNfe($nfe);
        $this->setSerieNfe($serieNfe);
        $this->setPedido($pedido);
    }

    public function getQuote() {
        $this->getPsReal();
        $this->makeQuoteRequest();
        return $this->getResult();
    }

    public function getTrackInfo() {
        $this->makeTrackRequest();
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

    private function getPsReal() {
        if (!empty($this->totalvolume) && !empty($this->totalweight) && !$this->quoteByWeight):
            $this->cubicweight = $this->totalvolume * 300;
            if ($this->cubicweight > $this->totalweight):
                $this->psReal = number_format($this->cubicweight, 4, '.', '');
            else:
                $this->psReal = $this->totalweight;
            endif;
        else:
            (!empty($this->totalweight) ? $this->psReal = $this->totalweight : null);
        endif;
    }

    private function buildQuoteRequest($request) {
        return array(
            $request =>
            array('in0' =>
                array(
                    'login' => $this->login,
                    'senha' => $this->senha,
                    'tpPessoaRemetente' => $this->tpPessoaRemetente,
                    'tpSituacaoTributariaRemetente' => $this->tpSituacaoTributariaRemetente,
                    'cdDivisaoCliente' => $this->cdDivisaoCliente,
                    'nrIdentifClienteRem' => $this->nrIdentifClienteRem,
                    'nrInscricaoEstadualRemetente' => $this->nrInscricaoEstadualRemetente,
                    'cepOrigem' => $this->cepOrigem,
                    'tpFrete' => $this->tpFrete,
                    'tpServico' => $this->tpServico,
                    'tpPessoaDestinatario' => $this->tpPessoaDestinatario,
                    'tpSituacaoTributariaDestinatario' => $this->tpSituacaoTributariaDestinatario,
                    'nrIdentifClienteDest' => $this->nrIdentifClienteDest,
                    'nrInscricaoEstadualDestinatario' => $this->nrInscricaoEstadualDestinatario,
                    'cepDestino' => $this->cepDestino,
                    'psReal' => $this->psReal,
                    'vlMercadoria' => $this->vlMercadoria
                )
            )
        );
    }

    private function makeQuoteRequest() {
        $client = new SoapClient('http://ws.tntbrasil.com.br/servicos/CalculoFrete?wsdl');
        $request = 'calculaFrete';
        try {
            $resultado = $client->__soapCall($request, $this->buildQuoteRequest($request));
            $this->parseQuoteResult($resultado);
        } catch (SoapFault $e) {
            $this->Result = array('status' => 'ERROR', 'errorMsg' => $e->faultstring);
        }
    }

    private function parseQuoteResult($resultado) {
        if ($this->tpServico === 'RNC'):
            $this->ShipCode = '10006';
        elseif ($this->tpServico === 'ANC'):
            $this->ShipCode = '10007';
        else:
            $this->ShipCode = '';
        endif;

        if (property_exists($resultado->out->errorList, "string") == false) {

            $this->vlTotalFrete = number_format(($resultado->out->vlTotalFrete / (1 - ($this->additionalPercent / 100))) + $this->additionalCharge, 2, '.', '');

            $this->prazoEntrega = $resultado->out->prazoEntrega + $this->shipmentDelay;

            $this->Result = array(
                'status' => 'OK',
                'shipcode' => $this->ShipCode,
                'valorfrete' => $this->vlTotalFrete,
                'prazoentrega' => $this->prazoEntrega,
                'nmDestinatario' => $resultado->out->nmDestinatario,
                'nmMunicipioDestino' => $resultado->out->nmMunicipioDestino,
                'nmMunicipioOrigem' => $resultado->out->nmMunicipioOrigem,
                'nmRemetente' => $resultado->out->nmRemetente,
                'nrDDDFilialDestino' => $resultado->out->nrDDDFilialDestino,
                'nrDDDFilialOrigem' => $resultado->out->nrDDDFilialOrigem,
                'nrTelefoneFilialDestino' => $resultado->out->nrTelefoneFilialDestino,
                'nrTelefoneFilialOrigem' => $resultado->out->nrTelefoneFilialOrigem,
                'vlDesconto' => $resultado->out->vlDesconto,
                'vlICMSubstituicaoTributaria' => $resultado->out->vlICMSubstituicaoTributaria,
                'vlImposto' => $resultado->out->vlImposto,
                'vlTotalCtrc' => $resultado->out->vlTotalCtrc,
                'vlTotalServico' => $resultado->out->vlTotalServico,
                'parcelas' => $resultado->out->parcelas->ParcelasFreteWebService
            );
        } else {
            if (is_array($resultado->out->errorList->string)):
                $erro = "";
                foreach ($resultado->out->errorList->string as $error):
                    $erro .= " $error";
                endforeach;
                $this->Result = array('status' => 'ERROR', 'errorMsg' => $erro, 'shipcode' => $this->ShipCode);
            else:
                $this->Result = array('status' => 'ERROR', 'errorMsg' => $resultado->out->errorList->string, 'shipcode' => $this->ShipCode);
            endif;
        }
    }

    private function buildTrackRequest($request) {
        return array(
            $request =>
            array('in0' =>
                array(
                    'cnpj' => $this->nrIdentifClienteRem,
                    'nf' => $this->nfe,
                    'nfSerie' => $this->serieNfe,
                    'pedido' => $this->pedido,
                    'usuario' => $this->login
                )
            )
        );
    }

    private function makeTrackRequest() {
        $client = new SoapClient('http://ws.tntbrasil.com.br/MercurioWS/services/Localizacao?wsdl');
        $request = 'localizaMercadoria';
        try {
            $resultado = $client->__soapCall($request, $this->buildTrackRequest($request));
            $this->parseTrackResult($resultado);
        } catch (SoapFault $e) {
            $this->Result = array('status' => 'ERROR', 'errorMsg' => $e->faultstring);
        }
    }

    private function parseTrackResult($resultado) {
        if (property_exists($resultado->out->erros, "string") == false) {

            if (!empty($resultado->out->previsaoEntrega)):
                $prazoEntrega = DateTime::createFromFormat('d/m/Y H:i:s', $resultado->out->previsaoEntrega);
                $prazoEntrega = $prazoEntrega->format('d-m-Y');
                $now = new DateTime(date("d-m-Y"));
                $prazoEntrega = new DateTime("$prazoEntrega");
                $diferenca = $prazoEntrega->diff($now)->format("%a");
            endif;

            $this->Result = array(
                'status' => 'OK',
                'cnpjDevedor' => $resultado->out->cnpjDevedor,
                'conhecimento' => $resultado->out->conhecimento,
                'dataEntrega' => $resultado->out->dataEntrega,
                'emissaoConhecimento' => $resultado->out->emissaoConhecimento,
                'localizacao' => $resultado->out->localizacao,
                'motivoNaoEntrega' => $resultado->out->motivoNaoEntrega,
                'notaFiscal' => $resultado->out->notaFiscal,
                'pedido' => $resultado->out->pedido,
                'peso' => $resultado->out->peso,
                'previsaoEntrega' => $resultado->out->previsaoEntrega,
                'qtdVolumes' => $resultado->out->qtdVolumes,
            );
        } else {
            if (is_array($resultado->out->erros->string)):
                $erro = "";
                foreach ($resultado->out->erros->string as $error):
                    $erro .= " $error";
                endforeach;
                $this->Result = array('status' => 'ERROR', 'errorMsg' => $erro);
            else:
                $this->Result = array('status' => 'ERROR', 'errorMsg' => $resultado->out->erros->string);
            endif;
        }
    }

}
