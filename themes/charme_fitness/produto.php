<?php
$Read->ExeRead(DB_PDT, "WHERE pdt_name = :name AND pdt_status = :status", "name={$URL[1]}&status=1");
if (!$Read->getResult()):
    header('Location: ' . BASE . '/404.php');
    exit;
else:
    extract($Read->getResult()[0]);
    $CommentKey = $pdt_id;
    $CommentType = 'product';

    $pdtViewUpdate = ['pdt_views' => $pdt_views + 1, 'pdt_lastview' => date('Y-m-d H:i:s')];
    $Update = new Update;
    $Update->ExeUpdate(DB_PDT, $pdtViewUpdate, "WHERE pdt_id = :id", "id={$pdt_id}");

    $CommentModerate = (COMMENT_MODERATE ? " AND (status = 1 OR status = 3)" : '');
    $Read->FullRead("SELECT id FROM " . DB_COMMENTS . " WHERE pdt_id = :pid{$CommentModerate}", "pid={$pdt_id}");
    $Aval = $Read->getRowCount();

    $Read->FullRead("SELECT SUM(rank) as total FROM " . DB_COMMENTS . " WHERE pdt_id = :pid{$CommentModerate}", "pid={$pdt_id}");
    $TotalAval = $Read->getResult()[0]['total'];
    $TotalRank = $Aval * 5;
    $getRank = ($TotalAval ? (($TotalAval / $TotalRank) * 50) / 10 : 0);
    $Rank = str_repeat("<li class='fa fa-star'></li>", intval($getRank)) . str_repeat("<li class='fa fa-star-o'></li>", 5 - intval($getRank));

    if ($pdt_hotlink):
        header("Location: {$pdt_hotlink}");
        exit;
    endif;

    /* browsing history */
    $browsing_history = (filter_input(INPUT_COOKIE, 'browsing_history', FILTER_DEFAULT) ? filter_input(INPUT_COOKIE, 'browsing_history', FILTER_DEFAULT) : null);
    if ($browsing_history):
        $arrIds = explode(',', $browsing_history);
        if (!in_array($pdt_id, $arrIds)):
            $arrIds[] = $pdt_id;
            $strIds = implode(',', $arrIds);
            setcookie('browsing_history', "{$strIds}", time() + 60 * 60 * 24 * 30, '/');
        endif;

        unset($browsing_history);
    else:
        setcookie('browsing_history', "{$pdt_id}", time() + 60 * 60 * 24 * 30, '/');
    endif;
endif;

/* MEASUREMENT CHART */
require '_cdn/widgets/measurement-chart/measurement-chart.php';

/* VIRTUAL TASTER */
//require '_cdn/widgets/virtual-taster/virtual-taster.php';
?>

<section class="product container" id="pdt">
    <div class="content">
        <div class="product_image">
            <div class="product_image_focus">
                <img class="j_focus_image image-zoom" src="<?= BASE; ?>/uploads/<?= $pdt_cover; ?>" alt="<?= $pdt_title; ?>" title="<?= $pdt_title; ?>" data-zoom="<?= BASE; ?>/uploads/<?= $pdt_cover; ?>"/>
            </div>

            <div class="product_image_gallery">
                <img class="j_select_gallery active" src="<?= BASE; ?>/uploads/<?= $pdt_cover; ?>" alt="<?= $pdt_title; ?>" title="<?= $pdt_title; ?>"/>
                <?php
                $Read->ExeRead(DB_PDT_GALLERY, "WHERE product_id = :id", "id={$pdt_id}");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $GALLERY):
                        ?>
                        <img class="j_select_gallery" src="<?= BASE; ?>/uploads/<?= $GALLERY['image']; ?>" alt="<?= $pdt_title; ?>" title="<?= $pdt_title; ?>"/>
                        <?php
                    endforeach;
                endif;
                ?>
            </div>

            <?php
            if (!empty($_SESSION['userLogin']['user_id'])):
                $Read->FullRead("SELECT wishlist_id FROM " . DB_PDT_WISHLIST . " WHERE pdt_id = :pdt AND user_id = :user",
                    "pdt={$pdt_id}&user={$_SESSION['userLogin']['user_id']}");

                $classWishlist = (!empty($Read->getResult()) ? 'j_toggle_wishlist active' : 'j_toggle_wishlist');
                $iconWishlist = (!empty($Read->getResult()) ? 'fa fa-heart-o faa-pulse animated active' : 'fa fa-heart-o');
                $titleWishlist = (!empty($Read->getResult()) ? 'Remover dos Favoritos' : 'Adicionar aos Favoritos');
                $attrUserId = " data-user-id='{$_SESSION['userLogin']['user_id']}'";
            else:
                $classWishlist = 'j_force_login';
                $iconWishlist = 'fa fa-heart-o';
                $titleWishlist = 'Adicionar aos Favoritos';
                $attrUserId = '';
            endif;
            ?>

            <div class="actions_wishlist">
                <span class="<?= $classWishlist; ?>" title="<?= $titleWishlist; ?>" data-pdt-id="<?= $pdt_id; ?>"<?= $attrUserId; ?>>
                    <i class="<?= $iconWishlist; ?>"></i>
                </span>
            </div>
        </div><div class="product_info">
            <header class="product_info_heading">
                <h1><?= $pdt_title; ?></h1>
            </header>

            <ul class="product_info_rating">
                <?= $Rank; ?>
            </ul>

            <?php if ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s') && strtotime($pdt_offer_end) - strtotime(date('Y-m-d H:i:s')) <= 259200): ?>
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

            <div class="product_info_price">
                <?php
                if ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s')):
                    $PdtPrice = $pdt_offer_price;
                    ?>
                    <p class="old_price">De <span>R$ <?= number_format($pdt_price, 2, ',', '.'); ?></span></p>
                    <?php
                else:
                    $PdtPrice = $pdt_price;
                endif;
                ?>
                <div class="price_heading">
                    <span class="by">Por<br/> <span>R$</span></span>
                    <span class="price"><?= number_format($PdtPrice, 2, ',', '.'); ?></span>
                </div>
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
                    <p class="discount">ou <?= $NumSplit; ?>X de R$ <?= $SplitPrice; ?><br/></p>
                    <?php
                endif;
                ?>
            </div>

            <?php require '_cdn/widgets/ecommerce/cart.add.php'; ?>
        </div>

        <div class="clear"></div>
    </div>

    <div class="product_footer container">
        <div class="content">
            <ul>
                <li>
                    <a class="wc_tab wc_active" href="#description" title="Descrição">Descrição</a>
                </li>

                <li>
                    <a class="wc_tab" href="#warranty" title="Garantia">Garantia</a>
                </li>

                <?php if (APP_COMMENTS && COMMENT_ON_PRODUCTS): ?>
                    <li>
                        <a class="wc_tab" href="#avaliacoes" title="Avaliações">Avaliações</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="product_footer_description wc_tab_target wc_active" id="description">
                <?= $pdt_content; ?>
            </div>

            <div class="product_footer_description wc_tab_target ds_none" id="warranty">
                <?= $pdt_warranty; ?>
            </div>

            <?php if (APP_COMMENTS && COMMENT_ON_PRODUCTS): ?>
                <div class="product_footer_reviews wc_tab_target ds_none" id="avaliacoes">
                    <?php require '_cdn/widgets/comments/comments.php'; ?>
                </div>
            <?php endif; ?>

            <div class="clear"></div>
        </div>
    </div>
</section>

<?php
if (!empty($_SESSION['combo']['hide'])):
    unset($_SESSION['combo']['hide']);
endif;

$Read->FullRead("SELECT c.combo_list, c.combo_discount, p.pdt_id, p.pdt_price, p.pdt_offer_price, p.pdt_offer_start, p.pdt_offer_end FROM " . DB_PDT_COMBO . " c INNER JOIN " . DB_PDT . " p ON c.pdt_id = p.pdt_id WHERE pdt_name = :name", "name={$URL[1]}");
if ($Read->getResult()):
    $pdtMain = $Read->getResult()[0]['pdt_id'];

    $comboList = $Read->getResult()[0]['combo_list'];
    $comboDiscount = $Read->getResult()[0]['combo_discount'];
    $comboPrice = ($Read->getResult()[0]['pdt_offer_price'] && $Read->getResult()[0]['pdt_offer_start'] <= date('Y-m-d H:i:s') && $Read->getResult()[0]['pdt_offer_end'] >= date('Y-m-d H:i:s') ? $Read->getResult()[0]['pdt_offer_price'] - ($Read->getResult()[0]['pdt_offer_price'] * $comboDiscount / 100) : $Read->getResult()[0]['pdt_price'] - ($Read->getResult()[0]['pdt_price'] * $comboDiscount / 100));
    $comboCount = 1;

    $Read->FullRead("SELECT pdt_id, pdt_name, pdt_title, pdt_cover, pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_id IN({$comboList}) AND pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL)", "status=1");
    if ($Read->getResult()):
        $comboResult = $Read->getResult();
        $comboCount += $Read->getRowCount();

        foreach ($comboResult as $PDT):
            $comboPrice += ($PDT['pdt_offer_price'] && $PDT['pdt_offer_start'] <= date('Y-m-d H:i:s') && $PDT['pdt_offer_end'] >= date('Y-m-d H:i:s') ? $PDT['pdt_offer_price'] - ($PDT['pdt_offer_price'] * $comboDiscount / 100) : $PDT['pdt_price'] - ($PDT['pdt_price'] * $comboDiscount / 100));
        endforeach;
        ?>
        <div class="combo container" data-pdt-id="<?= $pdtMain; ?>">
            <div class="content">
                <section class="products">
                    <header class="heading">
                        <h1>
                            Compre <span>Junto</span>
                        </h1>
                    </header>

                    <div class="combo_purchase">
                        <div class="combo_purchase_title">
                            Compre os <span><?= $comboCount; ?></span> itens
                        </div>

                        <div>
                            <span class="combo_purchase_price">
                                <span>por:</span> R$ <?= number_format($comboPrice, 2, ',', '.'); ?>
                            </span>

                            <button class="combo_purchase_button"><i class="fa fa-shopping-bag"></i> Compre Junto</button>
                        </div>
                    </div>

                    <div class="combo_restore">
                        <span class="j_combo_restore" title="Restaurar Combo">Restaurar Combo</span>
                    </div>

                    <div class="products_wrap">
                        <?php
                        $combo = $pdtMain;

                        $Read->FullRead("SELECT pdt_id, pdt_name, pdt_title, pdt_cover, pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_id = :id AND pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL)", "id={$pdtMain}&status=1");
                        if ($Read->getResult()):
                            foreach ($Read->getResult() as $PDT):
                                extract($PDT);

                                if ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s')):
                                    $PdtPrice = $pdt_offer_price;
                                    $discount = (int) ((($pdt_price - $pdt_offer_price) * 100) / $pdt_price);
                                else:
                                    $PdtPrice = $pdt_price;
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

                                require REQUIRE_PATH . '/inc/product.php';
                            endforeach;
                        endif;

                        foreach ($comboResult as $PDT):
                            extract($PDT);

                            if ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s')):
                                $PdtPrice = $pdt_offer_price;
                                $discount = (int) ((($pdt_price - $pdt_offer_price) * 100) / $pdt_price);
                            else:
                                $PdtPrice = $pdt_price;
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

                            require REQUIRE_PATH . '/inc/product.php';
                        endforeach;

                        unset($combo);
                        ?>
                    </div>
                </section>

                <div class="clear"></div>
            </div>
        </div>
        <?php
    endif;
endif;
?>

<section class="products container">
    <div class="content">
        <header class="heading">
            <h1>
                Produtos <span>Relacionados</span>
            </h1>
        </header>

        <div class="products_wrap">
            <div class="owl-carousel">
                <?php
                $arrDepartment = explode(',', $pdt_subcategory);
                $findDepartment = null;

                foreach ($arrDepartment as $CAT):
                    if ($findDepartment):
                        $findDepartment .= " OR FIND_IN_SET('{$CAT}', pdt_subcategory)";
                    else:
                        $findDepartment = "FIND_IN_SET('{$CAT}', pdt_subcategory)";
                    endif;
                endforeach;

                $Read->FullRead("SELECT pdt_id, pdt_name, pdt_title, pdt_cover, pdt_offer_price, pdt_offer_start, pdt_offer_end, pdt_price FROM " . DB_PDT . " WHERE pdt_id != :id AND pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND ({$findDepartment}) ORDER BY pdt_created DESC LIMIT :limit OFFSET :offset", "id={$pdt_id}&status=1&limit=8&offset=0");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $PDT):
                        extract($PDT);

                        if ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s')):
                            $PdtPrice = $pdt_offer_price;
                            $discount = (int) ((($pdt_price - $pdt_offer_price) * 100) / $pdt_price);
                        else:
                            $PdtPrice = $pdt_price;
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

                        require REQUIRE_PATH . '/inc/product.php';
                    endforeach;
                endif;
                ?>
            </div>
        </div>

        <div class="clear"></div>
    </div>
</section>
