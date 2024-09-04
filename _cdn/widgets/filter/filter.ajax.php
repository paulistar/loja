<?php

session_start();

$getPost = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (empty($getPost) || empty($getPost['action'])):
    die('Acesso Negado!');
endif;

$getPost['url'] = explode('/', $getPost['url']);
$getPost['file'] = $getPost['url'][0];
$getPost['history'] = $getPost['url'][1];

$Action = $getPost['action'];
$jSON['redirect'] = $getPost['url'][0] . '/' . $getPost['url'][1];
unset($getPost['url'], $getPost['action']);

$_SESSION['filter']['history'] = $getPost['history'];

usleep(2000);

require '../../../_app/Config.inc.php';
$Read = new Read;

switch ($Action):
    case 'filter_add':
        /* reseta o filtro */
        if (!empty($_SESSION['filter']['add'])):
            unset($_SESSION['filter']['add']);
        endif;

        /* size */
        if (!empty($getPost['pdt_size'])):
            $_SESSION['filter']['add']['pdt_size'] = $getPost['pdt_size'];
        endif;

        /* color */
        if (!empty($getPost['pdt_color'])):
            $_SESSION['filter']['add']['pdt_color'] = $getPost['pdt_color'];
        endif;

        /* print */
        if (!empty($getPost['pdt_print'])):
            $_SESSION['filter']['add']['pdt_print'] = $getPost['pdt_print'];
        endif;

        /* brand */
        if (!empty($getPost['pdt_brand'])):
            $_SESSION['filter']['add']['pdt_brand'] = $getPost['pdt_brand'];
        endif;

        /* department */
        if (!empty($getPost['pdt_department'])):
            $_SESSION['filter']['add']['pdt_department'] = implode(',', $getPost['pdt_department']);
        endif;

        /* discount */
        if (!empty($getPost['pdt_discount'])):
            $_SESSION['filter']['add']['pdt_discount']['values'] = implode(',', $getPost['pdt_discount']);

            for ($cc = 0; $cc < count($getPost['pdt_discount']); $cc++):
                $_SESSION['filter']['add']['pdt_discount']['conditions'][$cc]['step'] = min(explode('-', $getPost['pdt_discount'][$cc]));
                $_SESSION['filter']['add']['pdt_discount']['conditions'][$cc]['range'] = max(explode('-', $getPost['pdt_discount'][$cc]));
            endfor;
        endif;

        /* price */
        if (!empty($getPost['pdt_price'])):
            /* query stock */
            $joinStock = (!empty($_SESSION['filter']['add']['pdt_size']) || !empty($_SESSION['filter']['add']['pdt_color']) || !empty($_SESSION['filter']['add']['pdt_print']) ? ' INNER JOIN ' . DB_PDT_STOCK . ' s ON p.pdt_id = s.pdt_id' : '');
            $condStock = (!empty($_SESSION['filter']['add']['pdt_size']) || !empty($_SESSION['filter']['add']['pdt_color']) || !empty($_SESSION['filter']['add']['pdt_print']) ? ' AND s.stock_inventory >= :inventory' : '');
            $groupByStock = (!empty($_SESSION['filter']['add']['pdt_size']) || !empty($_SESSION['filter']['add']['pdt_color']) || !empty($_SESSION['filter']['add']['pdt_print']) ? ' GROUP BY s.pdt_id' : '');
            $parseStock = (!empty($_SESSION['filter']['add']['pdt_size']) || !empty($_SESSION['filter']['add']['pdt_color']) || !empty($_SESSION['filter']['add']['pdt_print']) ? '&inventory=1' : '');

            /* size */
            $condSize = (!empty($_SESSION['filter']['add']['pdt_size']) ? " AND s.size_id IN ('" . implode("', '", $_SESSION['filter']['add']['pdt_size']) . "')" : '');

            /* color */
            $condColor = (!empty($_SESSION['filter']['add']['pdt_color']) ? " AND s.color_id IN ('" . implode("', '", $_SESSION['filter']['add']['pdt_color']) . "')" : '');

            /* print */
            $condPrint = (!empty($_SESSION['filter']['add']['pdt_print']) ? " AND s.print_id IN ('" . implode("', '", $_SESSION['filter']['add']['pdt_print']) . "')" : '');

            /* brand */
            $condBrand = (!empty($_SESSION['filter']['add']['pdt_brand']) ? " AND p.pdt_brand IN ('" . implode("', '", $_SESSION['filter']['add']['pdt_brand']) . "')" : '');

            /* department */
            if (!empty($_SESSION['filter']['add']['pdt_department']) && strpos($_SESSION['filter']['add']['pdt_department'], ',')):
                $arrDepartment = explode(',', $_SESSION['filter']['add']['pdt_department']);
                $findDepartment = null;

                foreach ($arrDepartment as $CAT):
                    if ($findDepartment):
                        $findDepartment .= " OR FIND_IN_SET('{$CAT}', p.pdt_subcategory)";
                    else:
                        $findDepartment = "FIND_IN_SET('{$CAT}', p.pdt_subcategory)";
                    endif;
                endforeach;
            endif;

            $condDepartment = ($getPost['file'] == 'produtos' && !empty($_SESSION['filter']['add']['pdt_department']) && strpos($_SESSION['filter']['add']['pdt_department'], ',') ? " AND (FIND_IN_SET(:category, p.pdt_category) OR ({$findDepartment}))" : ($getPost['file'] == 'produtos' && !empty($_SESSION['filter']['add']['pdt_department']) && !strpos($_SESSION['filter']['add']['pdt_department'], ',') ? " AND FIND_IN_SET(:category, p.pdt_category) AND FIND_IN_SET('{$_SESSION['filter']['add']['pdt_department']}', p.pdt_subcategory)" : ($getPost['file'] == 'produtos' && empty($_SESSION['filter']['add']['pdt_department']) ? ' AND (FIND_IN_SET(:category, p.pdt_category) OR FIND_IN_SET(:category, p.pdt_subcategory))' : ($getPost['file'] == 'pesquisa' && !empty($_SESSION['filter']['add']['pdt_department']) && strpos($_SESSION['filter']['add']['pdt_department'], ',') ? " AND ({$findDepartment})" : ($getPost['file'] == 'pesquisa') && !empty($_SESSION['filter']['add']['pdt_department']) && !strpos($_SESSION['filter']['add']['pdt_department'], ',') ? " AND FIND_IN_SET('{$_SESSION['filter']['add']['pdt_department']}', p.pdt_subcategory)" : ''))));
            if ($getPost['file'] == 'produtos'):
                $Read->LinkResult(DB_PDT_CATS, 'cat_name', $getPost['history'], 'cat_id');
                $catId = $Read->getResult()[0]['cat_id'];
            endif;
            $parseDepartment = ($getPost['file'] == 'produtos' ? "&category={$catId}" : '');

            /* discount */
            if (!empty($_SESSION['filter']['add']['pdt_discount'])):
                $condDiscount = '';
                $condCC = false;
                foreach ($_SESSION['filter']['add']['pdt_discount']['conditions'] as $Discount):
                    if (!$condCC):
                        $condDiscount .= " AND p.pdt_offer_price IS NOT NULL AND p.pdt_offer_start <= NOW() AND p.pdt_offer_end >= NOW() AND ((100 - (FLOOR(((p.pdt_offer_price / p.pdt_price) * 100) / 10) * 10)) BETWEEN '{$Discount['step']}' AND '{$Discount['range']}')";
                        $condCC = true;
                    else:
                        $condDiscount .= " OR ((100 - (FLOOR(((p.pdt_offer_price / p.pdt_price) * 100) / 10) * 10)) BETWEEN '{$Discount['step']}' AND '{$Discount['range']}')";
                    endif;
                endforeach;
            else:
                $condDiscount = '';
            endif;

            /* search */
            $condSearch = ($getPost['file'] == 'pesquisa' && !empty($URL[1]) ? " AND p.pdt_title LIKE '%' :search '%'" : '');
            $parseSearch = ($getPost['file'] == 'pesquisa' && !empty($URL[1]) ? '&search=' . urldecode($URL[1]) . '' : '');

            /* price */
            $arrRangePrice = explode(',', $getPost['pdt_price']);
            $condPrice = " AND ((FLOOR(p.pdt_price) >= :step AND FLOOR(p.pdt_price) <= :range) OR (p.pdt_offer_price IS NOT NULL AND p.pdt_offer_start <= NOW() AND p.pdt_offer_end >= NOW() AND FLOOR(p.pdt_offer_price) >= :step AND FLOOR(p.pdt_offer_price) <= :range))";
            $parsePrice = "&step={$arrRangePrice[0]}&range={$arrRangePrice[1]}";

            $Read->FullRead("SELECT p.* FROM " . DB_PDT . " p{$joinStock} WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL){$condSearch}{$condDepartment}{$condBrand}{$condDiscount}{$condPrice}{$condSize}{$condColor}{$condPrint}{$condStock}{$groupByStock} ORDER BY p.pdt_created DESC", "status=1{$parseSearch}{$parseDepartment}{$parsePrice}{$parseStock}");
            if ($Read->getResult()):
                $_SESSION['filter']['add']['pdt_price']['step'] = $arrRangePrice[0];
                $_SESSION['filter']['add']['pdt_price']['range'] = $arrRangePrice[1];
            else:
                $condPrice = " AND ((FLOOR(p.pdt_price) > :step) OR (p.pdt_offer_price IS NOT NULL AND p.pdt_offer_start <= NOW() AND p.pdt_offer_end >= NOW() AND FLOOR(p.pdt_offer_price) > :step))";
                $parsePrice = "&step={$arrRangePrice[0]}";
                $Read->FullRead("SELECT MIN(p.pdt_price) AS min_price FROM " . DB_PDT . " p{$joinStock} WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL){$condSearch}{$condDepartment}{$condBrand}{$condDiscount}{$condPrice}{$condSize}{$condColor}{$condPrint}{$condStock}{$groupByStock}", "status=1{$parseSearch}{$parseDepartment}{$parsePrice}{$parseStock}");
                if ($Read->getResult()):
                    $_SESSION['filter']['add']['pdt_price']['step'] = $arrRangePrice[0];
                    $_SESSION['filter']['add']['pdt_price']['range'] = $Read->getResult()[0]['min_price'];
                else:
                    $condPrice = " AND ((FLOOR(p.pdt_price) < :range) OR (p.pdt_offer_price IS NOT NULL AND p.pdt_offer_start <= NOW() AND p.pdt_offer_end >= NOW() AND FLOOR(p.pdt_offer_price) < :range))";
                    $parsePrice = "&range={$arrRangePrice[1]}";
                    $Read->FullRead("SELECT MAX(p.pdt_price) AS max_price FROM " . DB_PDT . " p{$joinStock} WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL){$condSearch}{$condDepartment}{$condBrand}{$condDiscount}{$condPrice}{$condSize}{$condColor}{$condPrint}{$condStock}{$groupByStock}", "status=1{$parseSearch}{$parseDepartment}{$parsePrice}{$parseStock}");
                    if ($Read->getResult()):
                        $_SESSION['filter']['add']['pdt_price']['step'] = $Read->getResult()[0]['max_price'];
                        $_SESSION['filter']['add']['pdt_price']['range'] = $arrRangePrice[1];
                    endif;
                endif;
            endif;
        endif;
        break;

    case 'filter_access':
        if (!empty($_SESSION['filter']['access'])):
            unset($_SESSION['filter']['access']);
        else:
            $_SESSION['filter']['access'] = true;
        endif;
        break;
endswitch;

echo json_encode($jSON);
