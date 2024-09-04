<article class="products_item">
    <div class="products_item_image">
        <a href="<?= BASE; ?>/produto/<?= $pdt_name; ?>" title="<?= $pdt_title; ?>">
            <img src="<?= BASE; ?>/uploads/<?= $pdt_cover; ?>" alt="<?= $pdt_title; ?>" title="<?= $pdt_title; ?>"/>
        </a>

        <?php if (isset($launch)): ?>
            <div class="new_product">
                <p><a href="<?= BASE; ?>/produto/<?= $pdt_name; ?>" title="<?= $pdt_title; ?>">Novo</a></p>
            </div>
        <?php endif; ?>

        <?php if ($discount): ?>
            <div class="discount_product">
                <p>
                    <a href="<?= BASE; ?>/produto/<?= $pdt_name; ?>" title="<?= $pdt_title; ?>">
                        -<?= $discount; ?>% <i class="fa fa-circle-o faa-burst animated"></i>
                    </a>
                </p>
            </div>
        <?php endif; ?>

        <?php
        $classClose = (!empty($browsing_history) ? 'j_browsing_history' : (!empty($combo) && $combo != $pdt_id ? 'j_combo_hide' : ($URL[0] == 'favoritos' ? 'j_remove_wishlist' : null)));
        $titleClose = (!empty($browsing_history) ? 'Remover do Histórico' : (!empty($combo) && $combo != $pdt_id ? 'Remover do Combo' : ($URL[0] == 'favoritos' ? 'Remover dos Favoritos' : null)));
        $attrUserId = (!empty($_SESSION['userLogin']['user_id']) ? " data-user-id='{$_SESSION['userLogin']['user_id']}'" : '');

        if ($classClose):
            ?>
            <div class="products_close">
                <span title="<?= $titleClose; ?>" class="<?= $classClose; ?> fa fa-close" data-pdt-id="<?= $pdt_id; ?>"<?= $attrUserId; ?>></span>
            </div>
        <?php
        endif;
        ?>

        <div class="action_purchase">
            <a href="<?= BASE; ?>/produto/<?= $pdt_name; ?>#pdt" title="Comprar">
                <i class="fa fa-shopping-bag"></i>
            </a>
        </div>

        <div class="action_view">
            <a class="j_view_product" href="#" title="Espiar" data-pdt-id="<?= $pdt_id; ?>">
                <i class="fa fa-search-plus"></i>
            </a>
        </div>

        <?php
        if (!empty($_SESSION['userLogin']['user_id'])):
            $Read->FullRead("SELECT wishlist_id FROM " . DB_PDT_WISHLIST . " WHERE pdt_id = :pdt AND user_id = :user",
                "pdt={$pdt_id}&user={$_SESSION['userLogin']['user_id']}");

            $classWishlist = (!empty($Read->getResult()) ? 'j_toggle_wishlist active' : 'j_toggle_wishlist');
            $iconWishlist = (!empty($Read->getResult()) ? 'fa fa-heart-o faa-pulse animated' : 'fa fa-heart-o');
            $titleWishlist = (!empty($Read->getResult()) ? 'Remover dos Favoritos' : 'Adicionar aos Favoritos');
            $attrUserId = " data-user-id='{$_SESSION['userLogin']['user_id']}'";
        else:
            $classWishlist = 'j_force_login';
            $iconWishlist = 'fa fa-heart-o';
            $titleWishlist = 'Adicionar aos Favoritos';
            $attrUserId = '';
        endif;
        ?>

        <div class="action_wishlist">
            <a class="<?= $classWishlist; ?>" href="#" title="<?= $titleWishlist; ?>"
               data-pdt-id="<?= $pdt_id; ?>"<?= $attrUserId; ?>>
                <i class="<?= $iconWishlist; ?>"></i>
            </a>
        </div>
    </div>

    <?php if (isset($countdown)): ?>
        <div class="countdown" data-expire="<?= $pdt_offer_end; ?>">
            <div class="countdown_wrapper">
                <div>
                    <span>
                        Oferta<br/>
                        <span class="countdown_legend">Acaba em:</span>
                    </span>
                </div>

                <div>
                    <span>
                        <span class="days">00</span>&nbsp;&nbsp;:&nbsp;&nbsp;<br/>
                        <span class="countdown_legend">Dia</span>
                    </span>

                    <span>
                        <span class="hours">00</span>&nbsp;&nbsp;:&nbsp;&nbsp;<br/>
                        <span class="countdown_legend">Hrs</span>
                    </span>

                    <span>
                        <span class="minutes">00</span>&nbsp;&nbsp;:&nbsp;&nbsp;<br/>
                        <span class="countdown_legend">Min</span>
                    </span>
                    <span>
                        <span class="seconds">00</span><br/>
                        <span class="countdown_legend">Seg</span>
                    </span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="auto_height">
        <div class="products_item_title">
            <h1>
                <a href="<?= BASE; ?>/produto/<?= $pdt_name; ?>" title="<?= $pdt_title; ?>"><?= $pdt_title; ?></a>
            </h1>
        </div>

        <div class="products_item_price">
            <?php if ($discount): ?>
                <p>
                    <a href="<?= BASE; ?>/produto/<?= $pdt_name; ?>" title="<?= $pdt_title; ?>">
                        <span class="old_price">R$ <?= number_format($pdt_price, 2, ',', '.'); ?></span>
                        R$ <?= number_format($PdtPrice, 2, ',', '.'); ?> <span class="installment"><?= $NumSplit; ?>X de R$ <?= $SplitPrice; ?> <?= (ECOMMERCE_PAY_SPLIT && ECOMMERCE_PAY_SPLIT_ACN > 1 ? 'sem juros' : ''); ?></span>
                    </a>
                </p>
            <?php else: ?>
                <p>
                    <a href="<?= BASE; ?>/produto/<?= $pdt_name; ?>" title="<?= $pdt_title; ?>">
                        R$ <?= number_format($PdtPrice, 2, ',', '.'); ?> <span class="installment"><?= $NumSplit; ?>X de R$ <?= $SplitPrice; ?> <?= (ECOMMERCE_PAY_SPLIT && ECOMMERCE_PAY_SPLIT_ACN > 1 ? 'sem juros' : ''); ?></span>
                    </a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($combo)): ?>
        <form class="combo_cart_add" name="combo_cart_add" method="post" enctype="multipart/form-data">
            <?php
            $Read->FullRead("SELECT stock_id FROM " . DB_PDT_STOCK . " WHERE pdt_id = :id AND size_id IS NULL AND color_id IS NULL AND print_id IS NULL AND stock_inventory >= :inventory",
                "id={$pdt_id}&inventory=1");
            if ($Read->getResult()):
                $openByColor = false;
                $openBySizes = false;
                $openByPrint = false;
                ?>
                <input class="ds_none" name="stock_id" type="radio" value="<?= $Read->getResult()[0]['stock_id']; ?>"
                       checked="checked"/>
            <?php
            endif;

            $Read->FullRead("SELECT pdt_id FROM " . DB_PDT . " WHERE pdt_parent = :id", "id={$pdt_id}");
            if ($Read->getResult() || !empty($pdt_parent)):
                $openByColor = false;

                $Read->FullRead("SELECT p.pdt_name, s.stock_id, s.pdt_id, s.size_id, a.attr_color_code, a.attr_color_title FROM " . DB_PDT . " p INNER JOIN " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_COLORS . " a ON p.pdt_id = s.pdt_id AND s.color_id = a.attr_color_id WHERE (p.pdt_id = :id OR p.pdt_parent = :id OR p.pdt_id = :parent OR p.pdt_parent = :parent) AND s.color_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.color_id ORDER BY s.stock_id ASC",
                    "id={$pdt_id}&parent={$pdt_parent}&inventory=1");
                if ($Read->getResult()):
                    ?>
                    <div class="color_content has_relatives">
                        <?php
                        foreach ($Read->getResult() as $StockVar):
                            if (strpos($StockVar['attr_color_code'], ',')):
                                $arr_color = explode(',', $StockVar['attr_color_code']);
                                $bg_color = (count($arr_color) == 2 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%);" : (count($arr_color) == 3 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]});" : "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]});"));
                            else:
                                $bg_color = "background-color: {$StockVar['attr_color_code']};";
                            endif;
                            ?>
                            <a<?= ($StockVar['pdt_id'] == $pdt_id ? ' class="active"' : ''); ?> href="<?= BASE . '/produto/' . $StockVar['pdt_name'] . '#pdt'; ?>" title="<?= $StockVar['attr_color_title']; ?>" style="<?= $bg_color; ?>">
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
                $Read->FullRead("SELECT s.stock_id, s.size_id, s.color_id, s.stock_inventory, a.attr_color_code, a.attr_color_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_COLORS . " a ON s.color_id = a.attr_color_id WHERE s.pdt_id = :id AND s.color_id IS NOT NULL AND stock_inventory >= :inventory GROUP BY s.color_id ORDER BY s.stock_id ASC",
                    "id={$pdt_id}&inventory=1");
                if ($Read->getResult()):
                    if ($Read->getRowCount() > 1):
                        $openByColor = true;
                    else:
                        $openByColor = false;
                    endif;
                    ?>
                    <div class="color_content no_relatives">
                        <div class="boxing_loading">
                            <?php
                            foreach ($Read->getResult() as $StockVar):
                                if (strpos($StockVar['attr_color_code'], ',')):
                                    $arr_color = explode(',', $StockVar['attr_color_code']);
                                    $bg_color = (count($arr_color) == 2 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%);" : (count($arr_color) == 3 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]});" : "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]});"));
                                else:
                                    $bg_color = "background-color: {$StockVar['attr_color_code']};";
                                endif;
                                ?>
                                <label id="<?= $StockVar['stock_inventory']; ?>"
                                       class="combo_select_color<?= (!$openByColor ? ' active' : ''); ?>"
                                       title="<?= $StockVar['attr_color_title']; ?>" style="<?= $bg_color; ?>"
                                       data-pdt-id="<?= $pdt_id; ?>" data-stock-color="<?= $StockVar['color_id']; ?>">
                                    <?php if (empty($StockVar['size_id'])): ?>
                                        <input type="radio" name="stock_id"
                                               value="<?= $StockVar['stock_id']; ?>"<?= (!$openByColor ? ' checked="checked"' : ''); ?>/>
                                    <?php endif; ?>
                                    <div class="selected_color">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                </label>
                            <?php
                            endforeach;
                            ?>
                        </div>

                        <img class="image_loading" src="<?= INCLUDE_PATH; ?>/images/loading.gif" alt="Carregando..."
                             title="Carregando..."/>
                    </div>
                <?php
                else:
                    $openByColor = false;
                endif;
            endif;

            $Read->FullRead("SELECT pdt_id FROM " . DB_PDT . " WHERE pdt_parent = :id", "id={$pdt_id}");
            if ($Read->getResult() || !empty($pdt_parent)):
                $openByPrint = false;

                $Read->FullRead("SELECT p.pdt_name, s.stock_id, s.pdt_id, s.size_id, s.stock_inventory, a.attr_print_code, a.attr_print_title FROM " . DB_PDT . " p INNER JOIN " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_PRINTS . " a ON p.pdt_id = s.pdt_id AND s.print_id = a.attr_print_id WHERE (p.pdt_id = :id OR p.pdt_parent = :id OR p.pdt_id = :parent OR p.pdt_parent = :parent) AND s.print_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.print_id ORDER BY s.stock_id ASC;",
                    "id={$pdt_id}&parent={$pdt_parent}&inventory=1");
                if ($Read->getResult()):
                    ?>
                    <div class="print_content has_relatives">
                        <?php
                        foreach ($Read->getResult() as $StockVar):
                            $bgPrint = "background: url(" . BASE . "/uploads/{$StockVar['attr_print_code']}) center / cover no-repeat;";
                            ?>
                            <a<?= ($StockVar['pdt_id'] == $pdt_id ? ' class="active"' : ''); ?>
                                    href="<?= BASE . '/produto/' . $StockVar['pdt_name'] . '#pdt'; ?>"
                                    title="<?= $StockVar['attr_print_title']; ?>" style="<?= $bgPrint; ?>">
                                <?php if (empty($StockVar['size_id'])): ?>
                                    <input type="radio" name="stock_id"
                                           value="<?= $StockVar['stock_id']; ?>"<?= ($StockVar['pdt_id'] == $pdt_id ? ' checked="checked"' : ''); ?>/>
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
                $Read->FullRead("SELECT s.stock_id, s.size_id, s.print_id, s.stock_inventory, a.attr_print_code, a.attr_print_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_PRINTS . " a ON s.print_id = a.attr_print_id WHERE s.pdt_id = :id AND s.print_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.print_id ORDER BY s.stock_id ASC",
                    "id={$pdt_id}&inventory=1");
                if ($Read->getResult()):
                    if ($Read->getRowCount() > 1):
                        $openByPrint = true;
                    else:
                        $openByPrint = false;
                    endif;
                    ?>
                    <div class="print_content no_relatives">
                        <div class="boxing_loading">
                            <?php
                            foreach ($Read->getResult() as $StockVar):
                                $bgPrint = "background: url(" . BASE . "/uploads/{$StockVar['attr_print_code']}) center / cover no-repeat;";
                                ?>
                                <label id="<?= $StockVar['stock_inventory']; ?>"
                                       class="combo_select_print<?= (!$openByPrint ? ' active' : ''); ?>"
                                       title="<?= $StockVar['attr_print_title']; ?>" style="<?= $bgPrint; ?>"
                                       data-pdt-id="<?= $pdt_id; ?>" data-stock-print="<?= $StockVar['print_id']; ?>">
                                    <?php if (empty($StockVar['size_id'])): ?>
                                        <input type="radio" name="stock_id"
                                               value="<?= $StockVar['stock_id']; ?>"<?= (!$openByPrint ? ' checked="checked"' : ''); ?>/>
                                    <?php endif; ?>
                                    <div class="selected_print">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                </label>
                            <?php
                            endforeach;
                            ?>
                        </div>

                        <img class="image_loading" src="<?= INCLUDE_PATH; ?>/images/loading.gif" alt="Carregando..."
                             title="Carregando..."/>
                    </div>
                <?php
                else:
                    $openByPrint = false;
                endif;
            endif;

            $Read->FullRead("SELECT s.stock_id, s.stock_inventory, a.attr_size_code, a.attr_size_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_SIZES . " a ON s.size_id = a.attr_size_id WHERE s.pdt_id = :id AND s.size_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.size_id ORDER BY s.stock_id ASC",
                "id={$pdt_id}&inventory=1");
            if ($Read->getResult()):
                if ($Read->getRowCount() > 1):
                    $openBySizes = true;
                else:
                    $openBySizes = false;
                endif;
                ?>
                <div class="size_content">
                    <div class="boxing_loading">
                        <div class="combo_target_sizes">
                            <?php foreach ($Read->getResult() as $StockVar): ?>
                                <label class="combo_select_size<?= (!$openBySizes ? ' active' : ''); ?>"
                                       title="<?= $StockVar['attr_size_title']; ?>">
                                    <?= $StockVar['attr_size_code']; ?> <input type="radio" name="stock_id"
                                                                               value="<?= $StockVar['stock_id']; ?>"<?= (!$openBySizes ? ' checked="checked"' : ''); ?>/>
                                    <span><i class="fa fa-check"></i></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <img class="image_loading<?= ($openByColor || $openByPrint ? ' invisible' : ''); ?>"
                         src="<?= INCLUDE_PATH; ?>/images/loading.gif" alt="Carregando..." title="Carregando..."/>
                </div>
            <?php
            endif;
            ?>
        </form>
    <?php endif; ?>
</article>