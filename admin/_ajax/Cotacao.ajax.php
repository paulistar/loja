<?php

session_start();
ini_set('max_execution_time', 0);
require '../../_app/Config.inc.php';
$NivelAcess = LEVEL_WC_SHIPPING_QUOTE;

if (!APP_SHIPPING_QUOTE || empty($_SESSION['userLogin']) || empty($_SESSION['userLogin']['user_level']) || $_SESSION['userLogin']['user_level'] < $NivelAcess):
    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    echo json_encode($jSON);
    die;
endif;

usleep(50000);

//DEFINE O CALLBACK E RECUPERA O POST
$jSON = null;
$CallBack = 'Cotacao';
$PostData = filter_input_array(INPUT_POST, FILTER_DEFAULT);

//VALIDA AÇÃO
if ($PostData && $PostData['callback_action'] && $PostData['callback'] == $CallBack):
//PREPARA OS DADOS
    $Case = $PostData['callback_action'];
    unset($PostData['callback'], $PostData['callback_action']);


    // AUTO INSTANCE OBJECT READ
    if (empty($Read)):
        $Read = new Read;
    endif;

// AUTO INSTANCE OBJECT CREATE
    if (empty($Create)):
        $Create = new Create;
    endif;

// AUTO INSTANCE OBJECT UPDATE
    if (empty($Update)):
        $Update = new Update;
    endif;

// AUTO INSTANCE OBJECT DELETE
    if (empty($Delete)):
        $Delete = new Delete;
    endif;

//SELECIONA AÇÃO
    switch ($Case):
//QUOTATION
        case 'quotation':
            $volume = array_map(null, $PostData['width'], $PostData['length'], $PostData['height'], $PostData['qtd']);
            $volumetotal = 0;
            $xl = 0;
            $size = 0;
            $handlingFee = 0;
            foreach ($volume as $boxes):
                if (!empty($boxes[0] && $boxes[1] && $boxes[2] && $boxes[3])):
                    $volumetotal += number_format(str_replace(',', '.', str_replace('.', '', $boxes[0])), 2, '.', '') * number_format(str_replace(',', '.', str_replace('.', '', $boxes[1])), 2, '.', '') * number_format(str_replace(',', '.', str_replace('.', '', $boxes[2])), 2, '.', '') * $boxes[3];
                    $size += (number_format(str_replace(',', '.', str_replace('.', '', $boxes[0])), 2, '.', '') + number_format(str_replace(',', '.', str_replace('.', '', $boxes[1])), 2, '.', '') + number_format(str_replace(',', '.', str_replace('.', '', $boxes[2])), 2, '.', '')) * $boxes[3];
                    if (number_format(str_replace(',', '.', str_replace('.', '', $boxes[0])), 2, '.', '') > 1.05 || number_format(str_replace(',', '.', str_replace('.', '', $boxes[1])), 2, '.', '') > 1.05 || number_format(str_replace(',', '.', str_replace('.', '', $boxes[2])), 2, '.', '') > 1.05):
                        $xl = 1;
                    endif;
                    if (number_format(str_replace(',', '.', str_replace('.', '', $boxes[0])), 2, '.', '') > 0.70 || number_format(str_replace(',', '.', str_replace('.', '', $boxes[1])), 2, '.', '') > 0.70 || number_format(str_replace(',', '.', str_replace('.', '', $boxes[2])), 2, '.', '') > 0.70):
                        $handlingFee = 1;
                    endif;
                endif;
            endforeach;
            if ($size > 2):
                $xl = 1;
            endif;

            $totalvolume = ($volumetotal !== 0 ? number_format($volumetotal, 4, '.', '') : '');
            $cepDestino = $PostData['cepDestino'];
            $pesoFinal = number_format(str_replace(',', '.', str_replace('.', '', $PostData['pesoFinal'])), 3, '.', '');
            $vlFinal = number_format(preg_replace('/[^0-9.]*/', '', str_replace(',', '.', str_replace('.', '', $PostData['vlFinal']))), 2, '.', '');
            unset($PostData['cepDestino'], $PostData['pesoFinal'], $PostData['vlFinal'], $PostData['width'], $PostData['length'], $PostData['height'], $PostData['qtd']);

            //AUTO INSTANCE OBJECT TNT
            if (empty($Tnt) && ECOMMERCE_SHIPMENT_TNT_QUOTE):
                $Tnt = new Tnt(ECOMMERCE_SHIPMENT_TNT_LOGIN, ECOMMERCE_SHIPMENT_TNT_SENHA, ECOMMERCE_SHIPMENT_TNT_CDDIVISAOCLIENTE, ECOMMERCE_SHIPMENT_TNT_TPPESSOAREMETENTE, ECOMMERCE_SHIPMENT_TNT_TPSITUACAOTRIBUTARIAREMETENTE, SITE_ADDR_CNPJ, SITE_ADDR_IE, SITE_ADDR_ZIP);
            endif;
            //AUTO INSTANCE OBJECT JAMEF
            if (empty($Jamef) && ECOMMERCE_SHIPMENT_JAMEF_QUOTE):
                $Jamef = new Jamef(SITE_ADDR_CNPJ, SITE_ADDR_CITY, SITE_ADDR_UF, ECOMMERCE_SHIPMENT_JAMEF_FILCOT, ECOMMERCE_SHIPMENT_JAMEF_USUARIO);
            endif;
            //AUTO INSTANCE OBJECT JADLOG
            if (empty($Jadlog) && ECOMMERCE_SHIPMENT_JADLOG_QUOTE):
                $Jadlog = new Jadlog(ECOMMERCE_SHIPMENT_JADLOG_PASSWORD, SITE_ADDR_CNPJ, SITE_ADDR_ZIP);
            endif;
            //AUTO INSTANCE OBJECT CORREIOS
            if (empty($Correios) && ECOMMERCE_SHIPMENT_CORREIOS_QUOTE):
                $Correios = new CorreiosCurl(SITE_ADDR_ZIP, ECOMMERCE_SHIPMENT_CORREIOS_CDEMPRESA, ECOMMERCE_SHIPMENT_CORREIOS_CDSENHA);
            endif;
            //AUTO INSTANCE OBJECT TAMCARGO
            if (empty($TamCargo) && ECOMMERCE_SHIPMENT_TAM_QUOTE):
                $TamCargo = new TamCargo(ECOMMERCE_SHIPMENT_TAM_USERNAME, ECOMMERCE_SHIPMENT_TAM_PASSWORD, SITE_ADDR_ZIP, ECOMMERCE_SHIPMENT_TAM_PICKUP);
            endif;

            $jSON['shipping'] = null;

            if (ECOMMERCE_SHIPMENT_TNT_QUOTE && isset($PostData['tntQuote'])):
                $Tnt->setQuoteData($cepDestino, $pesoFinal, $vlFinal, $totalvolume, ECOMMERCE_SHIPMENT_TNT_TPFRETE, ECOMMERCE_SHIPMENT_TNT_TPSERVICO, 'F', 'NC', '12345', '', ECOMMERCE_SHIPMENT_TNT_BY_WEIGHT, ECOMMERCE_SHIPMENT_ADDITIONAL_PERCENT, ECOMMERCE_SHIPMENT_ADDITIONAL_CHARGE, ECOMMERCE_SHIPMENT_DELAY);
                $TntRetorno = $Tnt->getQuote();
                if ($TntRetorno['status'] === 'OK'):
                    $jSON['shipping'] .= "<div class='empresa'><div class='logo'><img height='40' src='./_siswc/cotacao/icons/logo-tnt.png'/></div><div class='result'><b>Frete Transportadora TNT Mercúrio - R$ " . number_format($TntRetorno['valorfrete'], '2', ',', '') . " ({$TntRetorno['prazoentrega']} dias úteis)</b></div></div>";
                endif;
            endif;
            if (ECOMMERCE_SHIPMENT_JAMEF_QUOTE && isset($PostData['jamefQuote'])):
                $Jamef->setQuoteData($cepDestino, $pesoFinal, $vlFinal, $totalvolume, ECOMMERCE_SHIPMENT_JAMEF_TIPTRA, ECOMMERCE_SHIPMENT_JAMEF_SEGPROD, ECOMMERCE_SHIPMENT_JAMEF_BY_WEIGHT, ECOMMERCE_SHIPMENT_ADDITIONAL_PERCENT, ECOMMERCE_SHIPMENT_ADDITIONAL_CHARGE, ECOMMERCE_SHIPMENT_DELAY);
                $JamefRetorno = $Jamef->getQuote();
                if ($JamefRetorno['status'] === 'OK'):
                    $jSON['shipping'] .= "<div class = 'empresa'><div class = 'logo'><img height = '40' src = './_siswc/cotacao/icons/logo-jamef.png'/></div><div class = 'result'><b>Frete Transportadora Jamef - R$ " . number_format($JamefRetorno['valorfrete'], '2', ',', '') . " ({$JamefRetorno['prazoentrega']} dias úteis)</b></div></div>";
                endif;
            endif;
            if (ECOMMERCE_SHIPMENT_JADLOG_QUOTE && isset($PostData['jadlogQuote'])):
                $Jadlog->setQuoteData($cepDestino, $pesoFinal, $vlFinal, $totalvolume, ECOMMERCE_SHIPMENT_JADLOG_FRAP, ECOMMERCE_SHIPMENT_JADLOG_TIPENTREGA, ECOMMERCE_SHIPMENT_JADLOG_VLCOLETA, ECOMMERCE_SHIPMENT_JADLOG_MODALIDADE, ECOMMERCE_SHIPMENT_JADLOG_SEGURO, ECOMMERCE_SHIPMENT_JADLOG_BY_WEIGHT, ECOMMERCE_SHIPMENT_ADDITIONAL_PERCENT, ECOMMERCE_SHIPMENT_ADDITIONAL_CHARGE);
                $JadlogRetorno = $Jadlog->getQuote();
                if ($JadlogRetorno['status'] === 'OK'):
                    $jSON['shipping'] .= "<div class = 'empresa'><div class = 'logo'><img height = '40' src = './_siswc/cotacao/icons/logo-jadlog.png'/></div><div class = 'result'><b>Frete Transportadora Jadlog - R$ " . number_format($JadlogRetorno['valorfrete'], '2', ',', '') . " (" . ECOMMERCE_SHIPMENT_JADLOG_DAYS . " dias úteis)</b></div></div>";
                endif;
            endif;
            if (ECOMMERCE_SHIPMENT_CORREIOS_QUOTE && !$xl && isset($PostData['correiosQuote'])):
                $additionalCharge = ECOMMERCE_SHIPMENT_ADDITIONAL_CHARGE;
                if ($handlingFee) {
                    $additionalCharge += 79.00;
                }
                $Correios->setQuoteData($cepDestino, $pesoFinal, $vlFinal, $totalvolume, ECOMMERCE_SHIPMENT_CORREIOS_SERVICE, ECOMMERCE_SHIPMENT_CORREIOS_FORMAT, ECOMMERCE_SHIPMENT_CORREIOS_OWN_HAND, ECOMMERCE_SHIPMENT_CORREIOS_ALERT, ECOMMERCE_SHIPMENT_CORREIOS_DECLARE, ECOMMERCE_SHIPMENT_CORREIOS_BY_WEIGHT, ECOMMERCE_SHIPMENT_ADDITIONAL_PERCENT, $additionalCharge, ECOMMERCE_SHIPMENT_DELAY);
                $CorreiosRetorno = $Correios->getQuote();
                if (!array_key_exists('shipcode', $CorreiosRetorno)):
                    foreach ($CorreiosRetorno as $modalidade):
                        if ($modalidade['status'] === 'OK'):
                            $jSON['shipping'] .= "<div class = 'empresa'><div class = 'logo'><img height = '40' src = './_siswc/cotacao/icons/logo-" . Check::Name(getShipmentTag(intval($modalidade['shipcode']))) . ".png'/></div><div class = 'result'><b>Frete Correios " . strtoupper(getShipmentTag(intval($modalidade['shipcode']))) . " - R$ " . number_format($modalidade['valorfrete'], '2', ',', '') . " ({$modalidade['prazoentrega']} dias úteis)</b></div></div>";
                        endif;
                    endforeach;
                else:
                    if ($CorreiosRetorno['status'] === 'OK'):
                        $jSON['shipping'] .= "<div class = 'empresa'><div class = 'logo'><img height = '40' src = './_siswc/cotacao/icons/logo-" . Check::Name(getShipmentTag(intval($CorreiosRetorno['shipcode']))) . ".png'/></div><div class = 'result'><b>Frete Correios " . strtoupper(getShipmentTag(intval($CorreiosRetorno['shipcode']))) . " - R$ " . number_format($CorreiosRetorno['valorfrete'], '2', ',', '') . " ({$CorreiosRetorno['prazoentrega']} dias úteis)</b></div></div>";
                    endif;
                endif;
            endif;

            if (ECOMMERCE_SHIPMENT_TAM_QUOTE && isset($PostData['tamQuote'])):
                $TamCargo->setQuoteData($cepDestino, $pesoFinal, $vlFinal, $totalvolume, ECOMMERCE_SHIPMENT_TAM_DELIVERY, ECOMMERCE_SHIPMENT_TAM_INSURANCE, ECOMMERCE_SHIPMENT_TAM_BY_WEIGHT, ECOMMERCE_SHIPMENT_ADDITIONAL_PERCENT, ECOMMERCE_SHIPMENT_ADDITIONAL_CHARGE, ECOMMERCE_SHIPMENT_DELAY);
                $TamRetorno = $TamCargo->getQuote();
                if ($TamRetorno['status'] === 'OK'):
                    foreach ($TamRetorno['modals'] as $modal) {
                        if ($modal['status'] === 'OK'):
                            $jSON['shipping'] .= "<div class = 'empresa'><div class = 'logo'><img height = '40' src = './_siswc/cotacao/icons/logo-tamcargo.png'/></div><div class = 'result'><b>Frete Transportadora Tam Cargo ({$modal['modal']}) - R$ " . number_format($modal['valorfrete'], '2', ',', '') . " ({$modal['prazoentrega']} dias úteis) - Aeroporto Destino: {$TamRetorno['aeroportoDestino']}</b></div></div>";
                        endif;
                    }
                else:
                    $jSON['shipping'] .= "<div class = 'empresa'><div class = 'logo'><img height = '40' src = './_siswc/cotacao/icons/logo-tamcargo.png'/></div><div class = 'result'><b>{$TamRetorno['errorMsg']}</b></div></div>";
                endif;
            endif;

            $jSON['success'] = true;

            break;
    endswitch;

//RETORNA O CALLBACK
    if ($jSON):
        echo json_encode($jSON);
    else:
        $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPSS:</b> Desculpe. Mas uma ação do sistema não respondeu corretamente. Ao persistir, contate o desenvolvedor!', E_USER_ERROR);
        echo json_encode($jSON);
    endif;
else:
//ACESSO DIRETO
    die('<br><br><br><center><h1>Acesso Restrito!</h1></center>');
endif;