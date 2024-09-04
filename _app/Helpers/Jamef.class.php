<?php

/**
 * JAMEF [ HELPER ]
 * Classe para realizar cotações no Webservice da transportadora Jamef
 * @copyright (c) year, Elizandro Echer - REVOSYS
 */
class Jamef {

    /**
     * Informar o Tipo de Transporte - Tipos de transportes ('1' => 'Rodoviário' ou '2' => 'Aéreo')
     * @var string
     */
    private $TIPTRA;

    /**
     * Informar o CPF/CNPJ da pessoa remetente
     * @var string
     */
    private $CNPJCPF;

    /**
     * Informar o município do remetente
     * @var string
     */
    private $MUNORI;

    /**
     * Informar o estado do remetente
     * @var string
     */
    private $ESTORI;

    /**
     * Informar o segmento do produto - Segmentos de produtos ('000010' => 'ALIMENTOS INDUSTRIALIZADOS' ou '000014' => 'CALCADO' ou '000016' => 'CARGAS FRACIONADAS' ou '000008' => 'CONFECCOES' ou '000004' => 'CONFORME NOTA FISCAL' ou '000011' => 'COSMETICOS / MATERIAL CIRURGICO' ou '000015' => 'E-COMMERCE' ou '000006' => 'JORNAIS / REVISTAS' ou '000005' => 'LIVROS' ou '000017' => 'MATERIA PRIMA' ou '000013' => 'MATERIAL ESCOLAR')
     * @var string
     */
    private $SEGPROD;

    /**
     * Informar o peso total do pedido (decimal deve ser informado com ".")
     * @var float
     */
    private $PESO;

    /**
     * Informar o valor total do pedido (decimal deve ser informado com ".")
     * @var float
     */
    private $VALMER;

    /**
     * OPCIONAL (CASO NÃO INFORMADO A COTAÇÃO SERÁ SOMENTE POR PESO) - Informar o volume total do pedido (largura x comprimento x altura x quantidade) em m para obter o peso cubado (decimal deve ser informado com ".")
     * @var float
     */
    private $METRO3;

    /**
     * Informar a filial de cotação
     * @var string
     */
    private $FILCOT;

    /**
     * Informar o CEP de destino da mercadoria
     * @var string
     */
    private $CEPDES;

    /**
     * Informar Dia que será utilizado para cálculo de Previsão de Entrega. Formato: DD.
     * @var string
     */
    private $DIA;

    /**
     * Informar Mês que será utilizado para cálculo de Previsão de Entrega. Formato: MM.
     * @var string
     */
    private $MES;

    /**
     * Informar Ano que será utilizado para cálculo de Previsão de Entrega. Foramto: YYYY.
     * @var string
     */
    private $ANO;

    /**
     * Informar Usuário válido cadastrado em nossa base de dados junto com o CNPJ do Cliente Pagador do Frete.
     * @var string
     */
    private $USUARIO;

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
     * Informar o CPF/CNPJ do destinatário
     * @var string
     */
    private $CpfCnpjDest;

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
     * OPCIONAL - Informar o formato de retorno da consulta no webservice (HTML ou XML (Padrão))
     * @var string
     */
    private $saida;

    /**
     * OPCIONAL - Informar o código da filial de coleta da mercadoria
     * @var int
     */
    private $codFilial;

    /**
     * Valor total de frete retornado pela consulta
     * @var string
     */
    private $vlTotalFrete;

    /**
     * Prazo de entrega retornado pela consulta
     * @var long
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
    private static $Data;
    private static $Format;

    /**
     *  Classe para realizar consulta no Web Service da Jamef Transportes
     * @param string $CNPJCPF Informar o CPF/CNPJ da pessoa remetente
     * @param string $MUNORI Informar o município do remetente
     * @param string $ESTORI Informar o estado do remetente
     * @param string $FILCOT Informar a filial de cotação
     * @param string $USUARIO Informar Usuário válido cadastrado em nossa base de dados junto com o CNPJ do Cliente Pagador do Frete.
     */
    public function __construct($CNPJCPF, $MUNORI, $ESTORI, $FILCOT, $USUARIO) {
        $this->setCNPJCPF($CNPJCPF);
        $this->setMUNORI($MUNORI);
        $this->setESTORI($ESTORI);
        $this->setFILCOT($FILCOT);
        $this->setUSUARIO($USUARIO);
    }

    public function setTIPTRA($TIPTRA) {
        $this->TIPTRA = $TIPTRA;
    }

    public function setCNPJCPF($CNPJCPF) {
        $this->CNPJCPF = preg_replace("/[^0-9]/", "", $CNPJCPF);
    }

    public function setMUNORI($MUNORI) {
        $this->MUNORI = $this->NameToUrl($MUNORI);
    }

    public function setESTORI($ESTORI) {
        $this->ESTORI = $this->NameToUrl($ESTORI);
    }

    public function setSEGPROD($SEGPROD) {
        $this->SEGPROD = preg_replace("/[^0-9]/", "", $SEGPROD);
    }

    public function setPESO($PESO) {
        $this->PESO = number_format($PESO, 4, '.', '');
    }

    public function setVALMER($VALMER) {
        $this->VALMER = number_format(preg_replace('/[^0-9.]*/', '', $VALMER), 2, '.', '');
    }

    public function setMETRO3($METRO3) {
        $this->METRO3 = (!empty($METRO3) && !$this->quoteByWeight ? number_format($METRO3, 4, '.', '') : 0);
    }

    public function setFILCOT($FILCOT) {
        $this->FILCOT = $FILCOT;
    }

    public function setCEPDES($CEPDES) {
        $this->CEPDES = preg_replace("/[^0-9]/", "", $CEPDES);
    }

    public function setDIA($DIA) {
        $this->DIA = (!empty($DIA) ? $DIA : date('d'));
    }

    public function setMES($MES) {
        $this->MES = (!empty($MES) ? $MES : date('m'));
    }

    public function setANO($ANO) {
        $this->ANO = (!empty($ANO) ? $ANO : date('Y'));
    }

    public function setUSUARIO($USUARIO) {
        $this->USUARIO = $USUARIO;
    }

    function setQuoteByWeight($quoteByWeight) {
        $this->quoteByWeight = $quoteByWeight;
    }

    function setAdditional_percent($additionalPercent) {
        $this->additionalPercent = (!empty($additionalPercent) ? number_format(preg_replace('/[^0-9.]*/', '', $additionalPercent), 2, '.', '') : null);
    }

    function setAdditional_charge($additionalCharge) {
        $this->additionalCharge = (!empty($additionalCharge) ? number_format(preg_replace('/[^0-9.]*/', '', $additionalCharge), 2, '.', '') : null);
    }

    function setShipment_delay($shipmentDelay) {
        $this->shipmentDelay = (!empty($shipmentDelay) ? intval($shipmentDelay) : null);
    }

    public function setCpfCnpjDest($CpfCnpjDest) {
        $this->CpfCnpjDest = preg_replace("/[^0-9]/", "", $CpfCnpjDest);
    }

    public function setNfe($nfe) {
        $this->nfe = (int) preg_replace("/[^0-9]/", "", $nfe);
    }

    public function setSerieNfe($serieNfe) {
        $this->serieNfe = (int) preg_replace("/[^0-9]/", "", $serieNfe);
    }

    public function setSaida($saida) {
        $this->saida = $saida;
    }

    public function setCodFilial($codFilial) {
        $this->codFilial = $codFilial;
    }

    /**
     * Método para setar informações para obter cotação de frete no webservice da Jamef
     * @param string $CEPDES Informar o CEP de destino da mercadoria
     * @param string $PESO Informar o peso total do pedido (decimal deve ser informado com ".")
     * @param string $VALMER Informar o valor total do pedido (decimal deve ser informado com ".")
     * @param string $METRO3 OPCIONAL (CASO NÃO INFORMADO A COTAÇÃO SERÁ SOMENTE POR PESO) - Informar o volume total do pedido (largura x comprimento x altura x quantidade) em m para obter o peso cubado (decimal deve ser informado com ".")
     * @param string $TIPTRA Informar o Tipo de Transporte - Tipos de transportes ('1' => 'Rodoviário' ou '2' => 'Aéreo') (deve ser definido na constante ECOMMERCE_SHIPMENT_JAMEF_TIPTRA)
     * @param string $SEGPROD Informar o segmento do produto - Segmentos de produtos ('000010' => 'ALIMENTOS INDUSTRIALIZADOS' ou '000014' => 'CALCADO' ou '000016' => 'CARGAS FRACIONADAS' ou '000008' => 'CONFECCOES' ou '000004' => 'CONFORME NOTA FISCAL' ou '000011' => 'COSMETICOS / MATERIAL CIRURGICO' ou '000015' => 'E-COMMERCE' ou '000006' => 'JORNAIS / REVISTAS' ou '000005' => 'LIVROS' ou '000017' => 'MATERIA PRIMA' ou '000013' => 'MATERIAL ESCOLAR')
     * @param string $DIA Informar Dia que será utilizado para cálculo de Previsão de Entrega. Formato: DD.
     * @param string $MES Informar Mês que será utilizado para cálculo de Previsão de Entrega. Formato: MM.
     * @param string $ANO Informar Ano que será utilizado para cálculo de Previsão de Entrega. Foramto: YYYY.
     * @param int $quoteByWeight Informar se a cotação deverá ser realizada somente por peso ou deverá ser calculado o peso cubado
     * @param float $additionalPercent Informar o valor percentual a ser somado ao valor do frete
     * @param float $additionalCharge Informar o valor fixo a ser somado ao valor do frete
     * @param int $shipmentDelay Informar o número de dias a ser somado ao prazo de entrega
     */
    public function setQuoteData($CEPDES, $PESO, $VALMER, $METRO3, $TIPTRA, $SEGPROD, $quoteByWeight = NULL, $additionalPercent = NULL, $additionalCharge = NULL, $shipmentDelay = NULL, $DIA = NULL, $MES = NULL, $ANO = NULL) {
        $this->setQuoteByWeight($quoteByWeight);
        $this->setAdditional_percent($additionalPercent);
        $this->setAdditional_charge($additionalCharge);
        $this->setShipment_delay($shipmentDelay);
        $this->setCEPDES($CEPDES);
        $this->setPESO($PESO);
        $this->setVALMER($VALMER);
        $this->setMETRO3($METRO3);
        $this->setTIPTRA($TIPTRA);
        $this->setSEGPROD($SEGPROD);
        $this->setDIA($DIA);
        $this->setMES($MES);
        $this->setANO($ANO);
    }

    public function setTrackData($CpfCnpjDest, $nfe, $serieNfe = null, $codFilial = null, $saida = null) {
        $this->setCpfCnpjDest($CpfCnpjDest);
        $this->setNfe($nfe);
        $this->setSerieNfe($serieNfe);
        $this->setCodFilial($codFilial);
        $this->setSaida($saida);
    }

    public function getQuote() {
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

    /**
     * ****************************************
     * ******* PRIVATE METHODS  ********
     * ****************************************
     */
    private function NameToUrl($Name) {
        self::$Format = array();
        self::$Format['a'] = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
        self::$Format['b'] = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';

        self::$Data = strtr(utf8_decode($Name), utf8_decode(self::$Format['a']), self::$Format['b']);
        self::$Data = strip_tags(trim(self::$Data));
        self::$Data = str_replace(array('     ', '    ', '   ', '  '), ' ', self::$Data);
        self::$Data = str_replace(' ', '%20', self::$Data);

        return strtoupper(utf8_encode(self::$Data));
    }

    private function buildQuoteRequest() {
        return "https://www.jamef.com.br/frete/rest/v1/$this->TIPTRA/$this->CNPJCPF/$this->MUNORI/$this->ESTORI/$this->SEGPROD/$this->PESO/$this->VALMER/$this->METRO3/$this->CEPDES/$this->FILCOT/$this->DIA/$this->MES/$this->ANO/$this->USUARIO";
    }

    private function makeQuoteRequest() {
        try {
            $resultado = Utils::CurlExec($this->buildQuoteRequest(), [], []);
            $this->parseQuoteResult($resultado);
        } catch (Exception $e) {
            $this->Result = array('status' => 'ERROR', 'errorMsg' => $e->getMessage());
        }
    }

    private function parseQuoteResult($resultado) {
        $json = json_decode($resultado['response']);
        if ($resultado['status'] === 200) {
            if ($this->TIPTRA === '1'):
                $this->ShipCode = '10008';
            elseif ($this->TIPTRA === '2'):
                $this->ShipCode = '10009';
            else:
                $this->ShipCode = '';
            endif;

            if (!empty($json->valor)):
                $this->vlTotalFrete = $json->valor;
                $this->vlTotalFrete = ($this->vlTotalFrete / (1 - ($this->additionalPercent / 100))) + $this->additionalCharge;
                $this->vlTotalFrete = number_format($this->vlTotalFrete, 2, '.', '');
            endif;

            if (!empty($json->previsao_entrega)):
                $shippingDate = new DateTime(DateTime::createFromFormat('d/m/Y', "$this->DIA/$this->MES/$this->ANO")->format('d-m-Y'));
                $prazoEntrega = new DateTime(DateTime::createFromFormat('d/m/Y', $json->previsao_entrega)->format('d-m-Y'));
                $this->prazoEntrega = $prazoEntrega->diff($shippingDate)->format("%a");

                $workingDays = 0;
                for ($i = 0; $i < $this->prazoEntrega; $i++) {
                    $shippingDate->modify('+1 day');
                    if ((int) $shippingDate->format('w') != 0 && (int) $shippingDate->format('w') != 6) {
                        $workingDays++;
                    }
                }
                $this->prazoEntrega = $workingDays;
                $this->prazoEntrega = $this->prazoEntrega + $this->shipmentDelay;
            endif;

            $this->Result = array(
                'status' => 'OK',
                'shipcode' => $this->ShipCode,
                'valorfrete' => $this->vlTotalFrete,
                'prazoentrega' => $this->prazoEntrega,
            );
        } else {
            $this->Result = array('status' => 'ERROR', 'errorMsg' => 'Erro ao realizar cotação!');
        }
    }

    private function buildTrackRequest() {
        $parameters = [
            'CIC_RESP_PGTO' => $this->CNPJCPF,
            'CIC_DEST' => $this->CpfCnpjDest,
            'NUM_NF' => $this->nfe,
            'SERIE_NF' => $this->serieNfe,
            'SAIDA' => $this->saida,
            'COD_REGN_ORIG' => $this->cod_filial
        ];

        $data = http_build_query($parameters);
        return "https://www.jamef.com.br/e-commerce/RastreamentoCargaServlet?$data";
    }

    private function makeTrackRequest() {
        try {
            $resultado = Utils::CurlExec($this->buildTrackRequest(), [], []);
            $this->parseTrackResult($resultado);
        } catch (Exception $e) {
            $this->Result = array('status' => 'ERROR', 'errorMsg' => $e->getMessage());
        }
    }

    private function parseTrackResult($resultado) {
        if ($resultado['status'] === 200) {
            $xml = simplexml_load_string($resultado['response']);

            if ($xml && !isset($xml->ERRO)) {
                $histories = array();
                $count = 0;
                foreach ($xml->HISTORICO->POSICAO as $history):
                    $histories[$count]['status'] = (string) trim($history->STATUS);
                    $histories[$count]['dtatualiz'] = (string) trim($history->DTATUALIZ);
                    $histories[$count]['manif'] = (string) trim($history->MANIF);
                    $histories[$count]['munlocl'] = (string) trim($history->MUNLOCL);
                    $histories[$count]['uflocl'] = (string) trim($history->UFLOCL);
                    $histories[$count]['mundestmanf'] = (string) trim($history->MUNDESTMANF);
                    $histories[$count]['ufdestmanf'] = (string) trim($history->UFDESTMANF);
                    $count ++;
                endforeach;

                $this->Result = array(
                    'status' => 'OK',
                    'ctrc' => (string) trim($xml->CONHECIMENTO->CTRC),
                    'nf' => (string) trim($xml->CONHECIMENTO->NF),
                    'cliorig' => (string) trim($xml->CONHECIMENTO->CLIORIG),
                    'munorig' => (string) trim($xml->CONHECIMENTO->MUNORIG),
                    'uforig' => (string) trim($xml->CONHECIMENTO->UFORIG),
                    'clidest' => (string) trim($xml->CONHECIMENTO->CLIDEST),
                    'mundest' => (string) trim($xml->CONHECIMENTO->MUNDEST),
                    'ufdest' => (string) trim($xml->CONHECIMENTO->UFDEST),
                    'linkimg' => (string) trim($xml->CONHECIMENTO->LINKIMG),
                    'histories' => $histories
                );
            } elseif ($xml && isset($xml->ERRO)) {
                $this->Result = array('status' => 'ERROR', 'errorMsg' => (string) trim($xml->ERRO->DESCERRO));
            } else {
                $this->Result = array('status' => 'ERROR', 'errorMsg' => 'Erro ao localizar rastreio!');
            }
        }
    }
}
