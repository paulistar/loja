<?php
$Read->FullRead("SELECT slide_image_mobile, slide_image_tablet, slide_image_desktop, slide_title, slide_link FROM " . DB_SLIDES . " WHERE slide_status = :status AND slide_start <= NOW() AND slide_end >= NOW() ORDER BY slide_date DESC", "status=1");
if ($Read->getResult()):
    ?>
    <div class="carousel container">
        <div class="content">
            <div class="owl-carousel owl-theme">
                <?php
                foreach ($Read->getResult() as $SLIDE):
                    $image_desktop = $SLIDE['slide_image_desktop'];
                    $image_mobile = (!empty($SLIDE['slide_image_mobile']) ? $SLIDE['slide_image_mobile'] : $image_desktop);
                    $image_tablet = (!empty($SLIDE['slide_image_tablet']) ? $SLIDE['slide_image_tablet'] : $image_desktop);
                    ?>
                    <div>
                        <a href="<?= $SLIDE['slide_link']; ?>" title="<?= $SLIDE['slide_title']; ?>">
                            <picture alt="<?= $SLIDE['slide_title']; ?>">
                                <source media="(min-width: 992px)" srcset="<?= BASE; ?>/uploads/<?= $image_desktop; ?>"/>
                                <source media="(min-width: 468px)" srcset="<?= BASE; ?>/uploads/<?= $image_tablet; ?>"/>
                                <source media="(min-width: 1px)" srcset="<?= BASE; ?>/uploads/<?= $image_mobile; ?>"/>
                                <img src="<?= BASE; ?>/uploads/<?= $image_desktop; ?>" alt="<?= $SLIDE['slide_title']; ?>" title="<?= $SLIDE['slide_title']; ?>"/>
                            </picture>
                        </a>
                    </div>
                    <?php
                endforeach;
                ?>
            </div>

            <div class="clear"></div>
        </div>
    </div>
    <?php
endif;
?>

<div class="quality container">
    <div class="content">
        <div class="quality_item">
            <img src="<?= INCLUDE_PATH; ?>/images/icon_card.png" alt="Pagamento Facilitado" title="Pagamento Facilitado"/>
            <p>
                <span>Frete grátis</span> acima de R$299
            </p>
        </div>

        <div class="quality_item">
            <img src="<?= INCLUDE_PATH; ?>/images/icon_frete_gratias.png" alt="Frete Grátis p/ Brasil" title="Frete Grátis p/ Brasil"/>
            <p>
                <span>12 vezes sem juros</span> no cartão
            </p>
        </div>

        <div class="quality_item">
            <img src="<?= INCLUDE_PATH; ?>/images/icon_check.png" alt="5% De Desconto" title="5% De Desconto"/>
            <p>
                Utilize até <span>2 cartões de crédito</span>
            </p>
        </div>

        <div class="quality_item">
            <img src="<?= INCLUDE_PATH; ?>/images/icon_compra_segura.png" alt="Compra Segura" title="Compra Segura"/>
            <p>
                <span>10% de desconto</span> no boleto
            </p>
        </div>

        <div class="clear"></div>
    </div>
</div>

<?php
/*
$Read->FullRead('SELECT cat_title, cat_name, (SELECT pdt_cover FROM ' . DB_PDT . ' WHERE pdt_status = :status AND FIND_IN_SET(cat_id, pdt_category) AND pdt_cover IS NOT NULL ORDER BY pdt_delivered DESC LIMIT :limit) AS pdt_cover FROM ' . DB_PDT_CATS . ' WHERE cat_parent IS NULL', "status=1&limit=1");
if ($Read->getResult()):
    ?>
    <section class="options">
        <div class="container">
            <div class="content">
                <header class="heading">
                    <h1>
                        Aqui <span>Também Tem</span>
                    </h1>
                </header>

                <div class="owl-carousel">
                    <?php foreach ($Read->getResult() as $PDT): ?>
                        <article>
                            <a href="<?= BASE . '/produtos/' . $PDT['cat_name']; ?>" title="<?= $PDT['cat_title']; ?>">
                                <img src="<?= BASE; ?>/uploads/<?= $PDT['pdt_cover']; ?>" alt="<?= $PDT['cat_title']; ?>" title="<?= $PDT['cat_title']; ?>"/>
                                <header>
                                    <h2><?= $PDT['cat_title']; ?></h2>
                                </header>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="clear"></div>
            </div>
        </div>
    </section>
    <?php
endif;
*/
?>

<section class="options">
    <div class="container">
        <div class="content">
            <header class="heading">
                <h1>
                    Aqui <span>Também Tem</span>
                </h1>
            </header>

            <div class="owl-carousel">
                <article>
                    <a href="#" title="Jeans">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/01.png" alt="Jeans" title="Jeans"/>
                        <header>
                            <h2>Jeans</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Natação">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/02.png" alt="Natação" title="Natação"/>
                        <header>
                            <h2>Natação</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Roupas Térmicas">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/03.png" alt="Roupas Térmicas" title="Roupas Térmicas"/>
                        <header>
                            <h2>Roupas Térmicas</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Bolas de Futebol">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/04.png" alt="Bolas de Futebol" title="Bolas de Futebol"/>
                        <header>
                            <h2>Futebol</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Personalização">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/05.png" alt="Personalização" title="Personalização"/>
                        <header>
                            <h2>Personalização</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Camisetas">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/06.png" alt="Camisetas" title="Camisetas"/>
                        <header>
                            <h2>Camisetas</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Sapatênis">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/07.png" alt="Sapatênis" title="Sapatênis"/>
                        <header>
                            <h2>Sapatênis</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Mochilas">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/08.png" alt="Mochilas" title="Mochilas"/>
                        <header>
                            <h2>Mochilas</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Jaquetas">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/09.png" alt="Jaquetas e Casacos" title="Jaquetas e Casacos"/>
                        <header>
                            <h2>Jaquetas</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Bermudas">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/10.png" alt="Bermudas e Shorts" title="Bermudas e Shorts"/>
                        <header>
                            <h2>Bermudas</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Basquete">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/11.png" alt="Bolas de Basquete" title="Bolas de Basquete"/>
                        <header>
                            <h2>Basquete</h2>
                        </header>
                    </a>
                </article>
            </div>

            <div class="clear"></div>
        </div>
    </div>
</section>

<?php
$Read->FullRead("SELECT pdt_id, pdt_name, pdt_title, pdt_cover, pdt_offer_price, pdt_offer_start, pdt_offer_end, pdt_price FROM " . DB_PDT . " WHERE pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_offer_price IS NOT NULL AND pdt_offer_start <= NOW() AND pdt_offer_end >= NOW() AND TIMEDIFF(pdt_offer_end, CURRENT_TIMESTAMP()) <= '72:00:00' ORDER BY RAND()", "status=1");
if ($Read->getResult()):
    ?>
    <div class="products container">
        <div class="content">
            <section>
                <header class="heading">
                    <h1>
                        <span>Ofertas</span> Imperdíveis
                    </h1>
                </header>

                <div class="products_wrap">
                    <div class="owl-carousel">
                        <?php
                        $countdown = true;

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

                        unset($countdown);
                        ?>
                    </div>
                </div>
            </section>

            <div class="clear"></div>
        </div>
    </div>
    <?php
endif;
?>

<?php
$Read->FullRead("SELECT banner_title, banner_size, banner_link, banner_image FROM " . DB_BANNERS . " WHERE banner_status = :status AND banner_line = :line AND banner_page = :page ORDER BY banner_date DESC", "status=1&line=1&page=index");
if ($Read->getResult()):
    ?>
    <div class="banners container" id="count_line_banner">
        <div class="content">
            <section>
                <header>
                    <h1>Confira as Novidades</h1>
                </header>

                <div>
                    <?php
                    foreach ($Read->getResult() as $BANNER):
                        ?><article class="banners_item box box<?= $BANNER['banner_size']; ?>">
                            <h1><?= $BANNER['banner_title']; ?></h1>
                            <a href="<?= BASE . '/' . $BANNER['banner_link']; ?>" title="<?= $BANNER['banner_title']; ?>">
                                <img src="<?= BASE; ?>/uploads/<?= $BANNER['banner_image']; ?>" alt="<?= $BANNER['banner_title']; ?>" title="<?= $BANNER['banner_title']; ?>"/>
                            </a>
                        </article><?php
                    endforeach;
                    ?>
                </div>
            </section>

            <div class="clear"></div>
        </div>
    </div>
    <?php
endif;
?>

<?php
$Read->FullRead("SELECT pdt_id, pdt_name, pdt_title, pdt_cover, pdt_offer_price, pdt_offer_start, pdt_offer_end, pdt_price FROM " . DB_PDT . " WHERE pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) ORDER BY pdt_created DESC, RAND() LIMIT :limit", "status=1&limit=12");
if ($Read->getResult()):
    ?>
    <div class="products container">
        <div class="content">
            <section>
                <header class="heading">
                    <h1>
                        Confira as <span>Novidades</span>
                    </h1>
                </header>

                <div class="products_wrap">
                    <div class="owl-carousel">
                        <?php
                        $launch = true;

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

                        unset($launch);
                        ?>
                    </div>
                </div>
            </section>

            <div class="clear"></div>
        </div>
    </div>
    <?php
endif;
?>

<?php
$Read->FullRead("SELECT banner_title, banner_size, banner_link, banner_image FROM " . DB_BANNERS . " WHERE banner_status = :status AND banner_line = :line AND banner_page = :page ORDER BY banner_date DESC", "status=1&line=2&page=index");
if ($Read->getResult()):
    ?>
    <div class="banners container" id="count_line_banner">
        <div class="content">
            <section>
                <header>
                    <h1>Confira as Novidades</h1>
                </header>

                <div>
                    <?php
                    foreach ($Read->getResult() as $BANNER):
                        ?><article class="banners_item box box<?= $BANNER['banner_size']; ?>">
                            <h1><?= $BANNER['banner_title']; ?></h1>
                            <a href="<?= BASE . '/' . $BANNER['banner_link']; ?>" title="<?= $BANNER['banner_title']; ?>">
                                <img src="<?= BASE; ?>/uploads/<?= $BANNER['banner_image']; ?>" alt="<?= $BANNER['banner_title']; ?>" title="<?= $BANNER['banner_title']; ?>"/>
                            </a>
                        </article><?php
                    endforeach;
                    ?>
                </div>
            </section>

            <div class="clear"></div>
        </div>
    </div>
    <?php
endif;
?>

<?php
$Read->FullRead("SELECT pdt_id, pdt_name, pdt_title, pdt_cover, pdt_offer_price, pdt_offer_start, pdt_offer_end, pdt_price FROM " . DB_PDT . " WHERE pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND (pdt_offer_end IS NULL OR pdt_offer_end < NOW() OR TIMEDIFF(pdt_offer_end, CURRENT_TIMESTAMP()) > '24:00:00') ORDER BY RAND() LIMIT :limit", "status=1&limit=8");
if ($Read->getResult()):
    ?>
    <div class="products container">
        <div class="content">
            <section>
                <header class="heading">
                    <h1>
                        Mais <span>Populares</span>
                    </h1>
                </header>

                <div class="products_wrap">
                    <div class="owl-carousel">
                        <?php
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
                        ?>
                    </div>
                </div>
            </section>

            <div class="clear"></div>
        </div>
    </div>
    <?php
endif;
?>

<?php
$Read->FullRead("SELECT banner_title, banner_size, banner_link, banner_image FROM " . DB_BANNERS . " WHERE banner_status = :status AND banner_line = :line AND banner_page = :page ORDER BY banner_date DESC", "status=1&line=3&page=index");
if ($Read->getResult()):
    ?>
    <div class="banners container" id="count_line_banner">
        <div class="content">
            <section>
                <header>
                    <h1>Confira as Novidades</h1>
                </header>

                <div>
                    <?php
                    foreach ($Read->getResult() as $BANNER):
                        ?><article class="banners_item box box<?= $BANNER['banner_size']; ?>">
                            <h1><?= $BANNER['banner_title']; ?></h1>
                            <a href="<?= BASE . '/' . $BANNER['banner_link']; ?>" title="<?= $BANNER['banner_title']; ?>">
                                <img src="<?= BASE; ?>/uploads/<?= $BANNER['banner_image']; ?>" alt="<?= $BANNER['banner_title']; ?>" title="<?= $BANNER['banner_title']; ?>"/>
                            </a>
                        </article><?php
                    endforeach;
                    ?>
                </div>
            </section>

            <div class="clear"></div>
        </div>
    </div>
    <?php
endif;
?>

<?php
$Read->FullRead("SELECT pdt_id, pdt_name, pdt_title, pdt_cover, pdt_offer_price, pdt_offer_start, pdt_offer_end, pdt_price FROM " . DB_PDT . " WHERE pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND (pdt_offer_end IS NULL OR pdt_offer_end < NOW() OR TIMEDIFF(pdt_offer_end, CURRENT_TIMESTAMP()) > '24:00:00') ORDER BY RAND() LIMIT :limit", "status=1&limit=8");
if ($Read->getResult()):
    ?>
    <div class="products container">
        <div class="content">
            <section>
                <header class="heading">
                    <h1>
                        Mais <span>Vendidos</span>
                    </h1>
                </header>

                <div class="products_wrap">
                    <div class="owl-carousel">
                        <?php
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
                        ?>
                    </div>
                </div>
            </section>

            <div class="clear"></div>
        </div>
    </div>
    <?php
endif;
?>

<?php
$Read->FullRead("SELECT banner_title, banner_size, banner_link, banner_image FROM " . DB_BANNERS . " WHERE banner_status = :status AND banner_line = :line AND banner_page = :page ORDER BY banner_date DESC", "status=1&line=4&page=index");
if ($Read->getResult()):
    ?>
    <div class="banners container" id="count_line_banner">
        <div class="content">
            <section>
                <header>
                    <h1>Confira as Novidades</h1>
                </header>

                <div>
                    <?php
                    foreach ($Read->getResult() as $BANNER):
                        ?><article class="banners_item box box<?= $BANNER['banner_size']; ?>">
                            <h1><?= $BANNER['banner_title']; ?></h1>
                            <a href="<?= BASE . '/' . $BANNER['banner_link']; ?>" title="<?= $BANNER['banner_title']; ?>">
                                <img src="<?= BASE; ?>/uploads/<?= $BANNER['banner_image']; ?>" alt="<?= $BANNER['banner_title']; ?>" title="<?= $BANNER['banner_title']; ?>"/>
                            </a>
                        </article><?php
                    endforeach;
                    ?>
                </div>
            </section>

            <div class="clear"></div>
        </div>
    </div>
    <?php
endif;
?>

<?php
$browsing_history = (filter_input(INPUT_COOKIE, 'browsing_history', FILTER_DEFAULT) ? filter_input(INPUT_COOKIE, 'browsing_history', FILTER_DEFAULT) : null);
if ($browsing_history):
    $arrIds = explode(',', $browsing_history);
    $strIds = "'" . implode("','", $arrIds) . "'";

    $Read->FullRead("SELECT pdt_id, pdt_name, pdt_title, pdt_cover, pdt_offer_price, pdt_offer_start, pdt_offer_end, pdt_price FROM " . DB_PDT . " WHERE pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_id IN ({$strIds}) AND (pdt_offer_end IS NULL OR pdt_offer_end < NOW() OR TIMEDIFF(pdt_offer_end, CURRENT_TIMESTAMP()) > '24:00:00') ORDER BY RAND() LIMIT :limit", "status=1&limit=40");
    if ($Read->getResult()):
        ?>
        <div class="products browsing_history container">
            <div class="content">
                <section>
                    <header class="heading">
                        <h1>
                            Meu <span>Histórico</span>
                        </h1>
                    </header>

                    <div class="browsing_history_remove">
                        <span class="j_browsing_history j_all" title="Limpar Histórico" data-pdt-id="<?= $browsing_history; ?>">Limpar Histórico</span>
                    </div>

                    <div class="products_wrap">
                        <div class="owl-carousel">
                            <?php
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

                            unset($browsing_history);
                            ?>
                        </div>
                    </div>
                </section>

                <div class="clear"></div>
            </div>
        </div>
        <?php
    endif;
endif;
?>

<?php
/*
$Read->FullRead('SELECT cat_title, cat_name, (SELECT pdt_cover FROM ' . DB_PDT . ' WHERE pdt_status = :status AND FIND_IN_SET(cat_id, pdt_category) AND pdt_cover IS NOT NULL ORDER BY pdt_delivered DESC LIMIT :limit) AS pdt_cover FROM ' . DB_PDT_CATS . ' WHERE cat_parent IS NULL', "status=1&limit=1");
if ($Read->getResult()):
    ?>
    <section class="options">
        <div class="container">
            <div class="content">
                <header class="heading">
                    <h1>
                        Aqui <span>Também Tem</span>
                    </h1>
                </header>

                <div class="owl-carousel">
                    <?php foreach ($Read->getResult() as $PDT): ?>
                        <article>
                            <a href="<?= BASE . '/produtos/' . $PDT['cat_name']; ?>" title="<?= $PDT['cat_title']; ?>">
                                <img src="<?= BASE; ?>/uploads/<?= $PDT['pdt_cover']; ?>" alt="<?= $PDT['cat_title']; ?>" title="<?= $PDT['cat_title']; ?>"/>
                                <header>
                                    <h2><?= $PDT['cat_title']; ?></h2>
                                </header>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="clear"></div>
            </div>
        </div>
    </section>
    <?php
endif;
*/
?>

<section class="options">
    <div class="container">
        <div class="content">
            <header class="heading">
                <h1>
                    Aqui <span>Também Tem</span>
                </h1>
            </header>

            <div class="owl-carousel">
                <article>
                    <a href="#" title="Jeans">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/01.png" alt="Jeans" title="Jeans"/>
                        <header>
                            <h2>Jeans</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Natação">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/02.png" alt="Natação" title="Natação"/>
                        <header>
                            <h2>Natação</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Roupas Térmicas">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/03.png" alt="Roupas Térmicas" title="Roupas Térmicas"/>
                        <header>
                            <h2>Roupas Térmicas</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Bolas de Futebol">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/04.png" alt="Bolas de Futebol" title="Bolas de Futebol"/>
                        <header>
                            <h2>Futebol</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Personalização">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/05.png" alt="Personalização" title="Personalização"/>
                        <header>
                            <h2>Personalização</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Camisetas">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/06.png" alt="Camisetas" title="Camisetas"/>
                        <header>
                            <h2>Camisetas</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Sapatênis">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/07.png" alt="Sapatênis" title="Sapatênis"/>
                        <header>
                            <h2>Sapatênis</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Mochilas">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/08.png" alt="Mochilas" title="Mochilas"/>
                        <header>
                            <h2>Mochilas</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Jaquetas">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/09.png" alt="Jaquetas e Casacos" title="Jaquetas e Casacos"/>
                        <header>
                            <h2>Jaquetas</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Bermudas">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/10.png" alt="Bermudas e Shorts" title="Bermudas e Shorts"/>
                        <header>
                            <h2>Bermudas</h2>
                        </header>
                    </a>
                </article>
                <article>
                    <a href="#" title="Basquete">
                        <img src="<?= INCLUDE_PATH; ?>/images/options/11.png" alt="Bolas de Basquete" title="Bolas de Basquete"/>
                        <header>
                            <h2>Basquete</h2>
                        </header>
                    </a>
                </article>
            </div>

            <div class="clear"></div>
        </div>
    </div>
</section>
