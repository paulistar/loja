<?php
/* CUSTOM BY ALISSON */
if ($pdt_inventory >= 1):
    ?>
    <form id="<?= $pdt_id; ?>" class="wc_cart_add" name="cart_add" method="post" enctype="multipart/form-data">
        <input name="pdt_id" type="hidden" value="<?= $pdt_id; ?>"/>

        <?php
        $Read->FullRead("SELECT stock_id FROM " . DB_PDT_STOCK . " WHERE pdt_id = :id AND size_id IS NULL AND color_id IS NULL AND print_id IS NULL AND stock_inventory >= :inventory", "id={$pdt_id}&inventory=1");
        if ($Read->getResult()):
            $openByColor = false;
            $openBySizes = false;
            $openByPrint = false;
            ?>
            <input name="stock_id" type="hidden" value="<?= $Read->getResult()[0]['stock_id']; ?>"/>
            <?php
        endif;
        ?>

        <?php
        $Read->FullRead("SELECT pdt_id FROM " . DB_PDT . " WHERE pdt_parent = :id", "id={$pdt_id}");
        if ($Read->getResult() || !empty($pdt_parent)):
            $openByColor = false;

            $Read->FullRead("SELECT p.pdt_name, s.stock_id, s.pdt_id, s.size_id, a.attr_color_code, a.attr_color_title FROM " . DB_PDT . " p INNER JOIN " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_COLORS . " a ON p.pdt_id = s.pdt_id AND s.color_id = a.attr_color_id WHERE (p.pdt_id = :id OR p.pdt_parent = :id OR p.pdt_id = :parent OR p.pdt_parent = :parent) AND s.color_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.color_id ORDER BY s.stock_id ASC", "id={$pdt_id}&parent={$pdt_parent}&inventory=1");
            if ($Read->getResult()):
                ?>
                <div class="color_content has_relatives">
                    <p>Selecione uma <span>cor</span></p>

                    <?php
                    foreach ($Read->getResult() as $StockVar):
                        if (strpos($StockVar['attr_color_code'], ',')):
                            $arrColors = explode(',', $StockVar['attr_color_code']);
                            $bgColor = (count($arrColors) == 2 ? "background-image: linear-gradient(135deg, {$arrColors[0]}  50%, {$arrColors[1]} 50%);" : (count($arrColors) == 3 ? "background-image: linear-gradient(135deg, {$arrColors[0]}  33%, {$arrColors[1]} 33%, {$arrColors[1]} 67%, {$arrColors[2]} 67%, {$arrColors[2]});" : "background-image: linear-gradient(135deg, {$arrColors[0]}  25%, {$arrColors[1]} 25%, {$arrColors[1]} 50%, {$arrColors[2]} 50%, {$arrColors[2]} 75%, {$arrColors[3]} 75%, {$arrColors[3]});"));
                        else:
                            $bgColor = "background-color: {$StockVar['attr_color_code']};";
                        endif;
                        ?>
                        <a<?= ($StockVar['pdt_id'] == $pdt_id ? ' class="active"' : ''); ?> href="<?= BASE . '/produto/' . $StockVar['pdt_name'] . '#pdt'; ?>" title="<?= $StockVar['attr_color_title']; ?>" style="<?= $bgColor; ?>">
                            <?php if (empty($StockVar['size_id'])): ?>
                                <input type="radio" name="stock_id" value="<?= $StockVar['stock_id']; ?>"<?= ($StockVar['pdt_id'] == $pdt_id ? ' checked="checked"' : ''); ?>/>
                            <?php endif; ?>
                            <div class="selected_color">
                                <i class="fa fa-check-circle"></i>
                            </div>
                        </a>
                        <?php
                    endforeach;
                    ?>
                </div>
                <?php
            endif;
        else:
            $Read->FullRead("SELECT s.stock_id, s.size_id, s.color_id, s.stock_inventory, a.attr_color_code, a.attr_color_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_COLORS . " a ON s.color_id = a.attr_color_id WHERE s.pdt_id = :id AND s.color_id IS NOT NULL AND stock_inventory >= :inventory GROUP BY s.color_id ORDER BY s.stock_id ASC", "id={$pdt_id}&inventory=1");
            if ($Read->getResult()):
                if ($Read->getRowCount() > 1):
                    $openByColor = true;
                else:
                    $openByColor = false;
                endif;
                ?>
                <div class="color_content no_relatives">
                    <div class="boxing_loading">
                        <p>Selecione uma <span>cor</span></p>

                        <?php
                        foreach ($Read->getResult() as $StockVar):
                            if (strpos($StockVar['attr_color_code'], ',')):
                                $arrColors = explode(',', $StockVar['attr_color_code']);
                                $bgColor = (count($arrColors) == 2 ? "background-image: linear-gradient(135deg, {$arrColors[0]}  50%, {$arrColors[1]} 50%);" : (count($arrColors) == 3 ? "background-image: linear-gradient(135deg, {$arrColors[0]}  33%, {$arrColors[1]} 33%, {$arrColors[1]} 67%, {$arrColors[2]} 67%, {$arrColors[2]});" : "background-image: linear-gradient(135deg, {$arrColors[0]}  25%, {$arrColors[1]} 25%, {$arrColors[1]} 50%, {$arrColors[2]} 50%, {$arrColors[2]} 75%, {$arrColors[3]} 75%, {$arrColors[3]});"));
                            else:
                                $bgColor = "background-color: {$StockVar['attr_color_code']};";
                            endif;
                            ?>
                            <label id="<?= $StockVar['stock_inventory']; ?>" class="wc_select_color<?= (!$openByColor ? ' active' : ''); ?>" title="<?= $StockVar['attr_color_title']; ?>" style="<?= $bgColor; ?>" data-pdt-id="<?= $pdt_id; ?>" data-pdt-price="<?= $PdtPrice; ?>" data-stock-color="<?= $StockVar['color_id']; ?>">
                                <?php if (empty($StockVar['size_id'])): ?>
                                    <input type="radio" name="stock_id" value="<?= $StockVar['stock_id']; ?>"<?= (!$openByColor ? ' checked="checked"' : ''); ?>/>
                                <?php endif; ?>
                                <div class="selected_color">
                                    <i class="fa fa-check-circle"></i>
                                </div>
                            </label>
                            <?php
                        endforeach;
                        ?>
                    </div>

                    <img class="image_loading" src="<?= INCLUDE_PATH; ?>/images/loading.gif" alt="Carregando..." title="Carregando..."/>
                </div>
                <?php
            else:
                $openByColor = false;
            endif;
        endif;

        $Read->FullRead("SELECT pdt_id FROM " . DB_PDT . " WHERE pdt_parent = :id", "id={$pdt_id}");
        if ($Read->getResult() || !empty($pdt_parent)):
            $openByPrint = false;

            $Read->FullRead("SELECT p.pdt_name, s.stock_id, s.pdt_id, s.size_id, s.stock_inventory, a.attr_print_code, a.attr_print_title FROM " . DB_PDT . " p INNER JOIN " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_PRINTS . " a ON p.pdt_id = s.pdt_id AND s.print_id = a.attr_print_id WHERE (p.pdt_id = :id OR p.pdt_parent = :id OR p.pdt_id = :parent OR p.pdt_parent = :parent) AND s.print_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.print_id ORDER BY s.stock_id ASC;", "id={$pdt_id}&parent={$pdt_parent}&inventory=1");
            if ($Read->getResult()):
                ?>
                <div class="print_content has_relatives">
                    <p>Selecione uma <span>estampa</span></p>

                    <?php
                    foreach ($Read->getResult() as $StockVar):
                        $bgPrint = "background: url(" . BASE . "/uploads/{$StockVar['attr_print_code']}) center / cover no-repeat;";
                        ?>
                        <a<?= ($StockVar['pdt_id'] == $pdt_id ? ' class="active"' : ''); ?> href="<?= BASE . '/produto/' . $StockVar['pdt_name'] . '#pdt'; ?>" title="<?= $StockVar['attr_print_title']; ?>" style="<?= $bgPrint; ?>">
                            <?php if (empty($StockVar['size_id'])): ?>
                                <input type="radio" name="stock_id" value="<?= $StockVar['stock_id']; ?>"<?= ($StockVar['pdt_id'] == $pdt_id ? ' checked="checked"' : ''); ?>/>
                            <?php endif; ?>
                            <div class="selected_print">
                                <i class="fa fa-check-circle"></i>
                            </div>
                        </a>
                        <?php
                    endforeach;
                    ?>
                </div>
                <?php
            endif;
        else:
            $Read->FullRead("SELECT s.stock_id, s.size_id, s.print_id, s.stock_inventory, a.attr_print_code, a.attr_print_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_PRINTS . " a ON s.print_id = a.attr_print_id WHERE s.pdt_id = :id AND s.print_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.print_id ORDER BY s.stock_id ASC", "id={$pdt_id}&inventory=1");
            if ($Read->getResult()):
                if ($Read->getRowCount() > 1):
                    $openByPrint = true;
                else:
                    $openByPrint = false;
                endif;
                ?>
                <div class="print_content no_relatives">
                    <div class="boxing_loading">
                        <p>Selecione uma <span>estampa</span></p>

                        <?php
                        foreach ($Read->getResult() as $StockVar):
                            $bgPrint = "background: url(" . BASE . "/uploads/{$StockVar['attr_print_code']}) center / cover no-repeat;";
                            ?>
                            <label id="<?= $StockVar['stock_inventory']; ?>" class="wc_select_print<?= (!$openByPrint ? ' active' : ''); ?>" title="<?= $StockVar['attr_print_title']; ?>" style="<?= $bgPrint; ?>" data-pdt-id="<?= $pdt_id; ?>" data-pdt-price="<?= $PdtPrice; ?>" data-stock-print="<?= $StockVar['print_id']; ?>">
                                <?php if (empty($StockVar['size_id'])): ?>
                                    <input type="radio" name="stock_id" value="<?= $StockVar['stock_id']; ?>"<?= (!$openByPrint ? ' checked="checked"' : ''); ?>/>
                                <?php endif; ?>
                                <div class="selected_print">
                                    <i class="fa fa-check-circle"></i>
                                </div>
                            </label>
                            <?php
                        endforeach;
                        ?>
                    </div>

                    <img class="image_loading" src="<?= INCLUDE_PATH; ?>/images/loading.gif" alt="Carregando..." title="Carregando..."/>
                </div>
                <?php
            else:
                $openByPrint = false;
            endif;
        endif;

        $Read->FullRead("SELECT s.stock_id, s.stock_inventory, a.attr_size_code, a.attr_size_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_SIZES . " a ON s.size_id = a.attr_size_id WHERE s.pdt_id = :id AND s.size_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.size_id ORDER BY s.stock_id ASC", "id={$pdt_id}&inventory=1");
        if ($Read->getResult()):
            if ($Read->getRowCount() > 1):
                $openBySizes = true;
            else:
                $openBySizes = false;
            endif;
            ?>
            <div class="size_content">
                <div class="boxing_loading">
                    <p>Selecione um <span>tamanho</span></p>

                    <div class="wc_target_sizes">
                        <?php foreach ($Read->getResult() as $StockVar): ?>
                            <label style="position: relative;" id="<?= $StockVar['stock_inventory']; ?>" class="wc_select_size<?= (!$openBySizes ? ' active' : ''); ?>" title="<?= $StockVar['attr_size_title']; ?>" data-pdt-price="<?= $PdtPrice; ?>">
                                <?= $StockVar['attr_size_code']; ?> <input type="radio" name="stock_id" value="<?= $StockVar['stock_id']; ?>"<?= (!$openBySizes ? ' checked="checked"' : ''); ?>/>
                                <span><i class="fa fa-check"></i></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <?php
                    /* MEASUREMENT CHART */
                    require '_cdn/widgets/measurement-chart/measurement-chart-open.php';

                    /* VIRTUAL TASTER */
                    //require '_cdn/widgets/virtual-taster/virtual-taster-open.php';
                    ?>
                </div>

                <img class="image_loading<?= ($openByColor || $openByPrint ? ' invisible' : ''); ?>" src="<?= INCLUDE_PATH; ?>/images/loading.gif" alt="Carregando..." title="Carregando..."/>
            </div>
            <?php
        else:
            $openBySizes = false;
        endif;
        ?>

        <div class="result_content">
            <div class="boxing_loading">
                <?php if ($pdt_unity): ?>
                    <div class="qtde_label">
                        <span></span> <span><?= $pdt_unity; ?></span> <span></span>
                    </div>
                <?php endif; ?>

                <div class="qtde_content">
                    <button id="<?= $pdt_id; ?>" class="wc_cart_less minus" data-pdt-price="<?= $PdtPrice; ?>">-</button><input name="item_amount" type="text" value="1" step="<?= $pdt_step; ?>" max="<?= $pdt_inventory; ?>" readonly="readonly"/><button id="<?= $pdt_id; ?>" class="wc_cart_plus plus" data-pdt-price="<?= $PdtPrice; ?>">+</button>
                </div>

                <div class="total_content">
                    <div class="purchase_total wc_target_total">
                        <p>Total de <span>1</span> <?= ($pdt_unity ? $pdt_unity : 'item'); ?></p>
                        <p>Por <span>R$ <?= number_format($PdtPrice, 2, ',', '.'); ?></span></p>
                        <?php
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
                            ?>
                            <p>ou <span><?= $NumSplit; ?>x de R$ <?= $SplitPrice; ?></span></p>
                            <?php
                        endif;
                        ?>
                    </div>

                    <div>
                        <button class="btn_purchase"><i class="fa fa-shopping-bag"></i> <?= ECOMMERCE_BUTTON_TAG; ?></button>
                    </div>
                </div>

                <div class="exchange_content">
                    <p>Troca garantida. <a class="wc_tab" href="#warranty" title="Veja as regras">Veja as regras.</a> <span>Válido em todo território nacional</span></p>
                </div>
            </div>

            <img class="image_loading<?= ($openByColor || $openByPrint || $openBySizes ? ' invisible' : ''); ?>" src="<?= INCLUDE_PATH; ?>/images/loading.gif" alt="Carregando..." title="Carregando..."/>
        </div>
    </form>
    <?php
else:
    Erro("<p class='al_center'>DESCULPE, PRODUTO FORA DE ESTOQUE!</p>", E_USER_NOTICE);
endif;
