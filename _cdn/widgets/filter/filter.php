<?php
/* check history */
if (isset($_SESSION['filter']['history']) && $_SESSION['filter']['history'] != $URL[1]):
    unset($_SESSION['filter']);
endif;

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

$condDepartment = ($URL[0] == 'produtos' && !empty($_SESSION['filter']['add']['pdt_department']) && strpos($_SESSION['filter']['add']['pdt_department'], ',') ? " AND (FIND_IN_SET(:category, p.pdt_category) OR ({$findDepartment}))" : ($URL[0] == 'produtos' && !empty($_SESSION['filter']['add']['pdt_department']) && !strpos($_SESSION['filter']['add']['pdt_department'], ',') ? " AND FIND_IN_SET(:category, p.pdt_category) AND FIND_IN_SET('{$_SESSION['filter']['add']['pdt_department']}', p.pdt_subcategory)" : ($URL[0] == 'produtos' && empty($_SESSION['filter']['add']['pdt_department']) ? ' AND (FIND_IN_SET(:category, p.pdt_category) OR FIND_IN_SET(:category, p.pdt_subcategory))' : ($URL[0] == 'pesquisa' && !empty($_SESSION['filter']['add']['pdt_department']) && strpos($_SESSION['filter']['add']['pdt_department'], ',') ? " AND ({$findDepartment})" : ($URL[0] == 'pesquisa') && !empty($_SESSION['filter']['add']['pdt_department']) && !strpos($_SESSION['filter']['add']['pdt_department'], ',') ? " AND FIND_IN_SET('{$_SESSION['filter']['add']['pdt_department']}', p.pdt_subcategory)" : ''))));
$parseDepartment = ($URL[0] == 'produtos' ? "&category={$cat_id}" : '');

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

/* price */
$condPrice = (!empty($_SESSION['filter']['add']['pdt_price']) ? " AND ((FLOOR(p.pdt_price) >= :step AND FLOOR(p.pdt_price) <= :range) OR (p.pdt_offer_price IS NOT NULL AND p.pdt_offer_start <= NOW() AND p.pdt_offer_end >= NOW() AND FLOOR(p.pdt_offer_price) >= :step AND FLOOR(p.pdt_offer_price) <= :range))" : '');
$parsePrice = (!empty($_SESSION['filter']['add']['pdt_price']) ? "&step={$_SESSION['filter']['add']['pdt_price']['step']}&range={$_SESSION['filter']['add']['pdt_price']['range']}" : '');

/* search */
$condSearch = ($URL[0] == 'pesquisa' && !empty($URL[1]) ? " AND p.pdt_title LIKE '%' :search '%'" : '');
$parseSearch = ($URL[0] == 'pesquisa' && !empty($URL[1]) ? '&search=' . urldecode($URL[1]) . '' : '');
?>

<div class="workcontrol_filter<?= (!empty($_SESSION['filter']['access']) ? ' active' : ''); ?>">
    <form class="workcontrol_filter_form" name="workcontrol_filter" action="" method="post">
        <input type="hidden" name="action" value="filter_add"/>
        <input type="hidden" name="url" value="<?= $URL[0] . '/' . $URL[1]; ?>"/>

        <?php
        /* size */
        $Read->FullRead("SELECT s.size_id, a.attr_size_code, a.attr_size_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_SIZES . " a ON s.size_id = a.attr_size_id WHERE s.pdt_id IN (SELECT p.pdt_id FROM " . DB_PDT . " p WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL){$condSearch}{$condDepartment}{$condBrand}{$condDiscount}{$condPrice}) AND s.size_id IS NOT NULL{$condColor}{$condPrint} AND s.stock_inventory >= :inventory GROUP BY s.size_id ORDER BY a.attr_size_created ASC", "status=1{$parseSearch}{$parseDepartment}{$parsePrice}&inventory=1");
        if ($Read->getResult()):
            ?>
            <div class="workcontrol_filter_form_item size">
                <p>Tamanhos</p>

                <div class="workcontrol_filter_form_item_options">
                    <?php
                    foreach ($Read->getResult() as $Size):
                        $active = (!empty($_SESSION['filter']['add']['pdt_size']) && in_array($Size['size_id'], $_SESSION['filter']['add']['pdt_size']) ? ' active' : '');
                        $checked = (!empty($_SESSION['filter']['add']['pdt_size']) && in_array($Size['size_id'], $_SESSION['filter']['add']['pdt_size']) ? ' checked="checked"' : '');
                        ?><label class="j_filter_check label_check<?= $active; ?>" title="<?= $Size['attr_size_title']; ?>"><input type="checkbox" value="<?= $Size['size_id']; ?>" name="pdt_size[]"<?= $checked; ?>/><?= $Size['attr_size_code']; ?></label><?php
                    endforeach;
                    ?>
                </div>
            </div>
            <?php
        endif;

        /* color */
        $Read->FullRead("SELECT s.color_id, a.attr_color_code, a.attr_color_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_COLORS . " a ON s.color_id = a.attr_color_id WHERE s.pdt_id IN (SELECT p.pdt_id FROM ws_products p WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL){$condSearch}{$condDepartment}{$condBrand}{$condDiscount}{$condPrice}) AND s.size_id IS NOT NULL{$condSize}{$condPrint} AND s.stock_inventory >= :inventory GROUP BY a.attr_color_code", "status=1{$parseSearch}{$parseDepartment}{$parsePrice}&inventory=1");
        if ($Read->getResult()):
            ?>
            <div class="workcontrol_filter_form_item color">
                <p>Cores Disponíveis</p>

                <div class="workcontrol_filter_form_item_options">
                    <?php
                    foreach ($Read->getResult() as $Color):
                        if (strpos($Color['attr_color_code'], ',')):
                            $arr_color = explode(',', $Color['attr_color_code']);
                            $bg_color = (count($arr_color) == 2 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%);" : (count($arr_color) == 3 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]});" : "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]});"));
                        else:
                            $bg_color = "background-color: {$Color['attr_color_code']};";
                        endif;
                        $active = (!empty($_SESSION['filter']['add']['pdt_color']) && in_array($Color['color_id'], $_SESSION['filter']['add']['pdt_color']) ? ' active' : '');
                        $checked = (!empty($_SESSION['filter']['add']['pdt_color']) && in_array($Color['color_id'], $_SESSION['filter']['add']['pdt_color']) ? ' checked="checked"' : '');
                        ?><label style="<?= $bg_color; ?>" class="j_filter_check label_check<?= $active; ?>" title="<?= $Color['attr_color_title']; ?>"><span class="icon_check icon-checkmark2 icon-notext"></span><input type="checkbox" value="<?= $Color['color_id']; ?>" name="pdt_color[]"<?= $checked; ?>/></label><?php
                    endforeach;
                    ?>
                </div>
            </div>
            <?php
        endif;

        /* print */
        $Read->FullRead("SELECT s.print_id, a.attr_print_code, a.attr_print_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_PRINTS . " a ON s.print_id = a.attr_print_id WHERE s.pdt_id IN (SELECT p.pdt_id FROM ws_products p WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL){$condSearch}{$condDepartment}{$condBrand}{$condDiscount}{$condPrice}) AND s.print_id IS NOT NULL{$condSize}{$condColor} AND s.stock_inventory >= :inventory GROUP BY a.attr_print_code", "status=1{$parseSearch}{$parseDepartment}{$parsePrice}&inventory=1");
        if ($Read->getResult()):
            ?>
            <div class="workcontrol_filter_form_item print">
                <p>Tipos de estampas</p>

                <div class="workcontrol_filter_form_item_options">
                    <?php
                    foreach ($Read->getResult() as $Print):
                        $bg_print = "background: url(" . BASE . "/uploads/{$Print['attr_print_code']}) center / cover no-repeat;";
                        $active = (!empty($_SESSION['filter']['add']['pdt_print']) && in_array($Print['print_id'], $_SESSION['filter']['add']['pdt_print']) ? ' active' : '');
                        $checked = (!empty($_SESSION['filter']['add']['pdt_print']) && in_array($Print['print_id'], $_SESSION['filter']['add']['pdt_print']) ? ' checked="checked"' : '');
                        ?><label style="<?= $bg_print; ?>" class="j_filter_check label_check<?= $active; ?>" title="<?= $Print['attr_print_title']; ?>"><span class="icon_check icon-checkmark2 icon-notext"></span><input type="checkbox" value="<?= $Print['print_id']; ?>" name="pdt_print[]"<?= $checked; ?>/></label><?php
                    endforeach;
                    ?>
                </div>
            </div>
        <?php
        endif;

        /* brand */
        $Read->FullRead("SELECT b.brand_id, b.brand_title, (SELECT COUNT(DISTINCT (p.pdt_id)) FROM " . DB_PDT . " p{$joinStock} WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL) AND b.brand_id = p.pdt_brand{$condSearch}{$condDepartment}{$condDiscount}{$condPrice}{$condSize}{$condColor}{$condPrint}{$condStock}) AS total_pdt FROM " . DB_PDT_BRANDS . " b", "status=1{$parseSearch}{$parseDepartment}{$parsePrice}{$parseStock}");
        if ($Read->getResult()):
            $total_pdt = 0;
            foreach ($Read->getResult() as $BRAND):
                if ($BRAND['total_pdt'] >= 1):
                    $total_pdt++;
                endif;
            endforeach;

            if ($total_pdt >= 1):
                ?>
                <div class="workcontrol_filter_form_item brand">
                    <p>Marcas</p>

                    <div class="workcontrol_filter_form_item_options">
                        <?php
                        foreach ($Read->getResult() as $BRAND):
                            if ($BRAND['total_pdt'] >= 1):
                                $active = (!empty($_SESSION['filter']['add']['pdt_brand']) && in_array($BRAND['brand_id'], $_SESSION['filter']['add']['pdt_brand']) ? ' class="active"' : '');
                                $checked = (!empty($_SESSION['filter']['add']['pdt_brand']) && in_array($BRAND['brand_id'], $_SESSION['filter']['add']['pdt_brand']) ? ' checked="checked"' : '');
                                ?>
                                <label class="j_filter_check" title="<?= $BRAND['brand_title']; ?>">
                                    <input type="checkbox" name="pdt_brand[]" value="<?= $BRAND['brand_id']; ?>"<?= $checked; ?>/>
                                    <span<?= $active; ?>><?= $BRAND['brand_title']; ?> [<?= $BRAND['total_pdt']; ?>]</span>
                                </label>
                                <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
                <?php
            endif;
        endif;

        /* department */
        $Read->FullRead("SELECT c.cat_id, c.cat_title, (SELECT COUNT(DISTINCT (p.pdt_id)) FROM " . DB_PDT . " p{$joinStock} WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL) AND FIND_IN_SET(c.cat_id, p.pdt_subcategory){$condSearch}{$condBrand}{$condDiscount}{$condPrice}{$condSize}{$condColor}{$condPrint}{$condStock}) AS total_pdt FROM " . DB_PDT_CATS . " c" . ($URL[0] == 'produtos' ? " WHERE c.cat_parent = :category" : '') . "", "status=1{$parseSearch}{$parsePrice}{$parseStock}" . ($URL[0] == 'produtos' ? "&category={$cat_id}" : '') . "");
        if ($Read->getResult()):
            $total_pdt = 0;
            foreach ($Read->getResult() as $Department):
                if ($Department['total_pdt'] >= 1):
                    $total_pdt++;
                endif;
            endforeach;

            if ($total_pdt >= 1):
                ?>
                <div class="workcontrol_filter_form_item department">
                    <p>Departamentos</p>

                    <div class="workcontrol_filter_form_item_options">
                        <?php
                        foreach ($Read->getResult() as $Department):
                            if ($Department['total_pdt'] >= 1):
                                $active = (!empty($_SESSION['filter']['add']['pdt_department']) && strpos($_SESSION['filter']['add']['pdt_department'], ',') && in_array($Department['cat_id'], explode(',', $_SESSION['filter']['add']['pdt_department'])) ? ' class="active"' : (!empty($_SESSION['filter']['add']['pdt_department']) && !strpos($_SESSION['filter']['add']['pdt_department'], ',') && $Department['cat_id'] == $_SESSION['filter']['add']['pdt_department'] ? ' class="active"' : ''));
                                $checked = (!empty($_SESSION['filter']['add']['pdt_department']) && strpos($_SESSION['filter']['add']['pdt_department'], ',') && in_array($Department['cat_id'], explode(',', $_SESSION['filter']['add']['pdt_department'])) ? ' checked="checked"' : (!empty($_SESSION['filter']['add']['pdt_department']) && !strpos($_SESSION['filter']['add']['pdt_department'], ',') && $Department['cat_id'] == $_SESSION['filter']['add']['pdt_department'] ? ' checked="checked"' : ''));
                                ?>
                                <label class="j_filter_check" title="<?= $Department['cat_title']; ?>">
                                    <input type="checkbox" name="pdt_department[]" value="<?= $Department['cat_id']; ?>"<?= $checked; ?>/>
                                    <span<?= $active; ?>><?= $Department['cat_title']; ?> [<?= $Department['total_pdt']; ?>]</span>
                                </label>
                                <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
                <?php
            endif;
        endif;

        /* discount */
        $Read->FullRead("SELECT (100 - (FLOOR(((p.pdt_offer_price / p.pdt_price) * 100) / 10) * 10)) AS pdt_discount FROM " . DB_PDT . " p{$joinStock} WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL) AND p.pdt_offer_price IS NOT NULL AND p.pdt_offer_start <= NOW() AND p.pdt_offer_end >= NOW(){$condSearch}{$condDepartment}{$condBrand}{$condPrice}{$condSize}{$condColor}{$condPrint}{$condStock} GROUP BY pdt_discount", "status=1{$parseSearch}{$parseDepartment}{$parsePrice}{$parseStock}");
        if ($Read->getResult()):
            ?>
            <div class="workcontrol_filter_form_item discount">
                <p>Faixas de descontos</p>

                <div class="workcontrol_filter_form_item_options">
                    <?php
                    foreach ($Read->getResult() as $Discount):
                        $step = ($Discount['pdt_discount'] - 10);
                        $values = $step . '-' . $Discount['pdt_discount'];
                        $text = ($step == 0 ? 'até 10%' : "{$step}% - {$Discount['pdt_discount']}%");

                        $Read->FullRead("SELECT COUNT(DISTINCT (p.pdt_id)) AS total_pdt FROM " . DB_PDT . " p{$joinStock} WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL) AND p.pdt_offer_price IS NOT NULL AND p.pdt_offer_start <= NOW() AND p.pdt_offer_end >= NOW() AND (100 - (FLOOR(((p.pdt_offer_price / p.pdt_price) * 100) / 10) * 10)) >= '{$step}' AND (100 - (FLOOR(((p.pdt_offer_price / p.pdt_price) * 100) / 10) * 10)) <= '{$Discount['pdt_discount']}'{$condSearch}{$condDepartment}{$condBrand}{$condPrice}{$condSize}{$condColor}{$condStock}", "status=1{$parseSearch}{$parseDepartment}{$parsePrice}{$parseStock}");
                        $total_pdt = $Read->getResult()[0]['total_pdt'];

                        $active = (!empty($_SESSION['filter']['add']['pdt_discount']) && strpos($_SESSION['filter']['add']['pdt_discount']['values'], ',') && in_array($values, explode(',', $_SESSION['filter']['add']['pdt_discount']['values'])) ? ' class="active"' : (!empty($_SESSION['filter']['add']['pdt_discount']) && !strpos($_SESSION['filter']['add']['pdt_discount']['values'], ',') && $values == $_SESSION['filter']['add']['pdt_discount']['values'] ? ' class="active"' : ''));
                        $checked = (!empty($_SESSION['filter']['add']['pdt_discount']) && strpos($_SESSION['filter']['add']['pdt_discount']['values'], ',') && in_array($values, explode(',', $_SESSION['filter']['add']['pdt_discount']['values'])) ? ' checked="checked"' : (!empty($_SESSION['filter']['add']['pdt_discount']) && !strpos($_SESSION['filter']['add']['pdt_discount']['values'], ',') && $values == $_SESSION['filter']['add']['pdt_discount']['values'] ? ' checked="checked"' : ''));
                        ?>
                        <label class="j_filter_check" title="Descontos de até <?= $Discount['pdt_discount']; ?>%">
                            <input type="checkbox" name="pdt_discount[]" value="<?= $values; ?>"<?= $checked; ?>/>
                            <span<?= $active; ?>><?= $text; ?> [<?= $total_pdt; ?>]</span>
                        </label>
                        <?php
                    endforeach;
                    ?>
                </div>
            </div>
            <?php
        endif;

        /* price */
        $Read->FullRead("SELECT p.pdt_price, p.pdt_offer_price, p.pdt_offer_start, p.pdt_offer_end FROM " . DB_PDT . " p{$joinStock} WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL){$condSearch}{$condDepartment}{$condBrand}{$condDiscount}{$condSize}{$condColor}{$condPrint}{$condStock}", "status=1{$parseSearch}{$parseDepartment}{$parseStock}");
        if ($Read->getResult()):
            $products = array();

            foreach ($Read->getResult() as $PDT):
                if ($PDT['pdt_offer_price'] && $PDT['pdt_offer_start'] <= date('Y-m-d H:i:s') && $PDT['pdt_offer_end'] >= date('Y-m-d H:i:s')):
                    $PDT['pdt_price'] = $PDT['pdt_offer_price'];
                    unset($PDT['pdt_offer_price'], $PDT['pdt_offer_start'], $PDT['pdt_offer_end']);
                else:
                    unset($PDT['pdt_offer_price'], $PDT['pdt_offer_start'], $PDT['pdt_offer_end']);
                endif;

                $products[] = $PDT['pdt_price'];
            endforeach;

            $min_price = (int) min($products);
            $max_price = (int) max($products);

            $step_price = (!empty($_SESSION['filter']['add']['pdt_price']) ? $_SESSION['filter']['add']['pdt_price']['step'] : $min_price);
            $range_price = (!empty($_SESSION['filter']['add']['pdt_price']) ? $_SESSION['filter']['add']['pdt_price']['range'] : $max_price);
            ?>
            <div class="workcontrol_filter_form_item price">
                <p>Preços</p>

                <input type="hidden" name="pdt_price" value="<?= ((!empty($_SESSION['filter']['add']['pdt_price']) && $_SESSION['filter']['add']['pdt_price']['step'] != $min_price) || (!empty($_SESSION['filter']['add']['pdt_price']) && $_SESSION['filter']['add']['pdt_price']['range'] != $max_price) ? $_SESSION['filter']['add']['pdt_price']['step'] . ',' . $_SESSION['filter']['add']['pdt_price']['range'] : ''); ?>" data-min="<?= $min_price; ?>" data-max="<?= $max_price; ?>" data-step="<?= $step_price; ?>" data-range="<?= $range_price; ?>"/>
                <input type="text" id="amount" readonly/>
                <div id="slider_price"></div>
            </div>
            <?php
        endif;
        ?>
    </form>

    <div class="workcontrol_filter_access j_filter_access" data-history="<?= $cat_id; ?>">
        <span class="icon-cog icon-notext"></span>
    </div>
</div>