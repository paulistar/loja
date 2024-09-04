<?php
if (empty($_SESSION['userLogin']['user_id'])):
    header("Location: " . BASE . '/conta/restrito#acc');
    exit;
else:
    ?>
    <div class="wishlist container">
        <div class="content">
            <section class="products">
                <header class="heading">
                    <h1>
                        Resultados de <span>Meus Favoritos</span>
                    </h1>
                </header>

                <ul class="breadcrumb">
                    <li>
                        <a href="<?= BASE; ?>" title="<?= SITE_NAME; ?>">
                            Home <i class="fa fa-angle-right"></i>
                        </a>
                    </li>

                    <li>
                        Favoritos <i class="fa fa-angle-right"></i>
                    </li>

                    <li class="active">
                        Meus Favoritos
                    </li>
                </ul>

                <div class="products_wrap">
                    <?php
                    $Read->FullRead("SELECT COUNT(p.pdt_id) AS total_pdt FROM " . DB_PDT . " p INNER JOIN " . DB_PDT_WISHLIST . " f ON p.pdt_id = f.pdt_id WHERE pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND f.user_id = :user", "status=1&user={$_SESSION['userLogin']['user_id']}");
                    $total_pdt = $Read->getResult()[0]['total_pdt'];

                    $getPage = (!empty($URL[1]) && filter_var($URL[1], FILTER_VALIDATE_INT) ? $URL[1] : 1);
                    $Pager = new Pager(BASE . "/wishlist/", "<i class='fa fa-angle-left'></i><i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i><i class='fa fa-angle-right'></i>", 3, $total_pdt);
                    $Pager->ExePager($getPage, 15);

                    $Read->FullRead("SELECT p.pdt_id, p.pdt_name, p.pdt_title, p.pdt_cover, p.pdt_offer_price, p.pdt_offer_start, p.pdt_offer_end, p.pdt_price FROM " . DB_PDT . " p INNER JOIN " . DB_PDT_WISHLIST . " f ON p.pdt_id = f.pdt_id WHERE pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND f.user_id = :user ORDER BY p.pdt_created DESC LIMIT :limit OFFSET :offset", "status=1&user={$_SESSION['userLogin']['user_id']}&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
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

                        $Pager->ExeFullPaginator("SELECT p.pdt_id, p.pdt_name, p.pdt_title, p.pdt_cover, p.pdt_offer_price, p.pdt_offer_start, p.pdt_offer_end, p.pdt_price FROM " . DB_PDT . " p INNER JOIN " . DB_PDT_WISHLIST . " f ON p.pdt_id = f.pdt_id WHERE pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND f.user_id = :user", "status=1&user={$_SESSION['userLogin']['user_id']}");
                        echo $Pager->getPaginator();
                    else:
                        ?>
                        <div class="not_found">
                            <div class="container">
                                <div class="content">
                                    <p class="not_found_icon fa fa-frown-o"></p>
                                    <p class="tagline">
                                        <i class="fa fa-exclamation-circle"></i> Oops! Não há itens na sua lista de favoritos.
                                    </p>

                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $Pager->ReturnPage();
                    endif;
                    ?>
                </div>
            </section>

            <div class="clear"></div>
        </div>
    </div>
<?php
endif;
