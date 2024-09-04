<?php

session_start();
require '../../_app/Config.inc.php';
$NivelAcess = 6;

if (empty($_SESSION['userLogin']) || empty($_SESSION['userLogin']['user_level']) || $_SESSION['userLogin']['user_level'] < $NivelAcess):
    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    echo json_encode($jSON);
    die;
endif;

usleep(50000);

//DEFINE O CALLBACK E RECUPERA O POST
$jSON = null;
$CallBack = 'Combo';
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
        case 'manager':
            $jSON['empty'] = null;

            $Read->FullRead("SELECT combo_list FROM " . DB_PDT_COMBO . " WHERE pdt_id = :id", "id={$PostData['pdt_id']}");
            if (!$Read->getResult()):
                $data = ['pdt_id' => $PostData['pdt_id'], 'combo_list' => $PostData['pdt_combo_id']];
                $Create->ExeCreate(DB_PDT_COMBO, $data);
            else:
                $comboList = explode(',', $Read->getResult()[0]['combo_list']);
                if (!in_array($PostData['pdt_combo_id'], $comboList)):
                    $comboList[] = $PostData['pdt_combo_id'];
                    $data = ['combo_list' => implode(',', $comboList)];
                    $Update->ExeUpdate(DB_PDT_COMBO, $data, "WHERE pdt_id = :id", "id={$PostData['pdt_id']}");
                else:
                    unset($comboList[array_search($PostData['pdt_combo_id'], $comboList)]);
                    $data = ['combo_list' => implode(',', $comboList)];
                    $Update->ExeUpdate(DB_PDT_COMBO, $data, "WHERE pdt_id = :id", "id={$PostData['pdt_id']}");

                    $Read->FullRead("SELECT combo_list FROM " . DB_PDT_COMBO . " WHERE pdt_id = :id", "id={$PostData['pdt_id']}");
                    if (empty($Read->getResult()[0]['combo_list'])):
                        $Delete->ExeDelete(DB_PDT_COMBO, "WHERE pdt_id = :id", "id={$PostData['pdt_id']}");
                        $jSON['empty'] = true;
                    endif;
                endif;
            endif;
            break;

        case 'discount':
            if (in_array('', $PostData)):
                $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPSS:</b> Para adicionar um desconto, insira um valor referente a porcentagem de desconto!', E_USER_WARNING);
            else:
                $Read->FullRead("SELECT pdt_id FROM " . DB_PDT_COMBO . " WHERE pdt_id = :id", "id={$PostData['pdt_id']}");
                if ($Read->getResult()):
                    $data = ['combo_discount' => $PostData['discount']];
                    $Update->ExeUpdate(DB_PDT_COMBO, $data, "WHERE pdt_id = :id", "id={$PostData['pdt_id']}");

                    $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>TUDO CERTO:</b> O desconto de <b>{$PostData['discount']}%</b> foi definido para este combo!");
                else:
                    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPSS:</b> Este produto ainda não possui um <b>combo</b>!', E_USER_WARNING);
                endif;
            endif;
            break;

        case 'content':
            $jSON['content'] = null;

            $Read->FullRead("SELECT combo_list FROM " . DB_PDT_COMBO . " WHERE pdt_id = :id", "id={$PostData['pdt_id']}");
            $comboList = ($Read->getResult() && $Read->getResult()[0]['combo_list'] ? explode(',', $Read->getResult()[0]['combo_list']) : false);

            if (isset($PostData['search'])):
                $Read->FullRead("SELECT pdt_id, pdt_name, pdt_title, pdt_cover, pdt_offer_price, pdt_offer_start, pdt_offer_end, pdt_price FROM " . DB_PDT . " WHERE pdt_id != :id AND pdt_status = :status AND pdt_title LIKE '%' :search '%' AND (pdt_inventory >= 1 OR pdt_inventory IS NULL)", "id={$PostData['pdt_id']}&status=1&search={$PostData['search']}");
            elseif (isset($PostData['offset'])):
                $Read->FullRead("SELECT pdt_id, pdt_name, pdt_title, pdt_cover, pdt_offer_price, pdt_offer_start, pdt_offer_end, pdt_price FROM " . DB_PDT . " WHERE pdt_id != :id AND pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) LIMIT :limit OFFSET :offset", "id={$PostData['pdt_id']}&status=1&limit=16&offset={$PostData['offset']}");
            elseif (isset($PostData['read'])):
                if (!$comboList):
                    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPSS:</b> Este produto ainda não possui um <b>combo</b>!', E_USER_WARNING);
                else:
                    if ($PostData['read'] == 'selected'):
                        $Read->FullRead("SELECT pdt_id, pdt_name, pdt_title, pdt_cover, pdt_offer_price, pdt_offer_start, pdt_offer_end, pdt_price FROM " . DB_PDT . " WHERE pdt_id IN('" . implode("', '", $comboList) . "') AND pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL)", "status=1");
                    else:
                        $Read->FullRead("SELECT pdt_id, pdt_name, pdt_title, pdt_cover, pdt_offer_price, pdt_offer_start, pdt_offer_end, pdt_price FROM " . DB_PDT . " WHERE pdt_id != :id AND pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL)", "id={$PostData['pdt_id']}&status=1");
                    endif;
                endif;
            endif;

            if ($Read->getResult()):
                foreach ($Read->getResult() as $PDT):
                    $active = ($comboList && in_array($PDT['pdt_id'], $comboList) ? ' active' : '');

                    if ($PDT['pdt_offer_price'] && $PDT['pdt_offer_start'] <= date('Y-m-d H:i:s') && $PDT['pdt_offer_end'] >= date('Y-m-d H:i:s')):
                        $PdtPrice = $PDT['pdt_offer_price'];
                        $discount = (int) ((($PDT['pdt_price'] - $PDT['pdt_offer_price']) * 100) / $PDT['pdt_price']);
                    else:
                        $PdtPrice = $PDT['pdt_price'];
                        $discount = false;
                    endif;

                    if (ECOMMERCE_PAY_SPLIT):
                        $MakeSplit = intval($PdtPrice / ECOMMERCE_PAY_SPLIT_MIN);
                        $NumSplit = (!$MakeSplit ? 1 : ($MakeSplit && $MakeSplit <= ECOMMERCE_PAY_SPLIT_NUM ? $MakeSplit : ECOMMERCE_PAY_SPLIT_NUM));
                        if ($NumSplit <= ECOMMERCE_PAY_SPLIT_ACN):
                            $SplitPrice = number_format(($PdtPrice / $NumSplit), '2', ',', '.');
                        elseif ($NumSplit - ECOMMERCE_PAY_SPLIT_ACN == 1):
                            $SplitPrice = number_format(($PdtPrice * (pow(1 + (ECOMMERCE_PAY_SPLIT_ACM / 100), $NumSplit - ECOMMERCE_PAY_SPLIT_ACN)) / $NumSplit), '2', ',', '.');
                        else:
                            $ParcSj = round($PdtPrice / $NumSplit, 2); // Valor das parcelas sem juros
                            $ParcRest = (ECOMMERCE_PAY_SPLIT_ACN > 1 ? $NumSplit - ECOMMERCE_PAY_SPLIT_ACN : $NumSplit);
                            $DiffParc = round(($PdtPrice * getFactor($ParcRest) * $ParcRest) - $PdtPrice, 2);
                            $SplitPrice = number_format($ParcSj + ($DiffParc / $NumSplit), '2', ',', '.');
                        endif;
                    endif;

                    $jSON['content'] .= "<article class='combo_box_item wc_normalize_height' data-pdt-combo-id='{$PDT['pdt_id']}'>";
                    $jSON['content'] .= "<div class='combo_box_item_image'>";
                    $jSON['content'] .= "<img src='" . BASE . "/uploads/{$PDT['pdt_cover']}' alt='{$PDT['pdt_title']}' title='{$PDT['pdt_title']}'/>";

                    if ($discount):
                        $jSON['content'] .= "<div class='combo_box_item_image_discount' style='width: {$discount}%;'>";
                        $jSON['content'] .= "{$discount}% off";
                        $jSON['content'] .= "</div>";
                    endif;

                    $jSON['content'] .= "<div class='combo_box_item_image_selected{$active}'>";
                    $jSON['content'] .= "<div class='combo_box_item_image_selected_box'>";
                    $jSON['content'] .= "<i class='icon-checkmark icon-notext'></i>";
                    $jSON['content'] .= "</div>";
                    $jSON['content'] .= "</div>";
                    $jSON['content'] .= "</div>";

                    $jSON['content'] .= "<div class='combo_box_item_title'>";
                    $jSON['content'] .= "{$PDT['pdt_title']}";
                    $jSON['content'] .= "</div>";

                    $jSON['content'] .= "<div class='combo_box_item_price'>";
                    if ($discount):
                        $jSON['content'] .= "<span class='old_price'>de: R$ " . number_format($PDT['pdt_price'], 2, ',', '.') . "</span> por: R$ " . number_format($PdtPrice, 2, ',', '.') . "";
                    else:
                        $jSON['content'] .= "por: R$ " . number_format($PdtPrice, 2, ',', '.') . "";
                    endif;
                    $jSON['content'] .= "<span class='installment'>{$NumSplit}x de R$ {$SplitPrice}</span>";
                    $jSON['content'] .= "</div>";
                    $jSON['content'] .= "</article>";
                endforeach;
            endif;
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
