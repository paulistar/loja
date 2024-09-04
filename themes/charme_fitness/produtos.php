<?php
$Read->ExeRead(DB_PDT_CATS, "WHERE cat_name = :cat", "cat={$URL[1]}");
if (!$Read->getResult()):
    require REQUIRE_PATH . '/404.php';
else:
    extract($Read->getResult()[0]);
    ?>
    <div class="categories container">
        <div class="content">
            <?php require '_cdn/widgets/filter/filter.php'; ?>

            <section class="products">
                <header class="heading">
                    <h1>
                        Resultados de <span><?= $cat_title; ?></span>
                    </h1>
                </header>

                <ul class="breadcrumb">
                    <li>
                        <a href="<?= BASE; ?>" title="<?= SITE_NAME; ?>">
                            Home <i class="fa fa-angle-right"></i>
                        </a>
                    </li>

                    <li>
                        Produtos <i class="fa fa-angle-right"></i>
                    </li>

                    <li class="active">
                        <?= $cat_title; ?>
                    </li>
                </ul>

                <div class="products_wrap">
                    <?php
                    $Read->FullRead("SELECT COUNT(p.pdt_id) AS total_pdt FROM " . DB_PDT . " p{$joinStock} WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL){$condSearch}{$condDepartment}{$condBrand}{$condDiscount}{$condPrice}{$condSize}{$condColor}{$condPrint}{$condStock}{$groupByStock}", "status=1{$parseSearch}{$parseDepartment}{$parsePrice}{$parseStock}");
                    $total_pdt = $Read->getResult()[0]['total_pdt'];

                    $getPage = (!empty($URL[2]) && filter_var($URL[2], FILTER_VALIDATE_INT) ? $URL[2] : 1);
                    $Pager = new Pager(BASE . "/produtos/{$URL[1]}/", "<i class='fa fa-angle-left'></i><i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i><i class='fa fa-angle-right'></i>", 3, $total_pdt);
                    $Pager->ExePager($getPage, 15);

                    $Read->FullRead("SELECT p.* FROM " . DB_PDT . " p{$joinStock} WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL){$condSearch}{$condDepartment}{$condBrand}{$condDiscount}{$condPrice}{$condSize}{$condColor}{$condPrint}{$condStock}{$groupByStock} ORDER BY p.pdt_created DESC LIMIT :limit OFFSET :offset", "status=1{$parseSearch}{$parseDepartment}{$parsePrice}{$parseStock}&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
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

                        $Pager->ExeFullPaginator("SELECT p.* FROM " . DB_PDT . " p{$joinStock} WHERE p.pdt_status = :status AND (p.pdt_inventory >= 1 OR p.pdt_inventory IS NULL){$condSearch}{$condDepartment}{$condBrand}{$condDiscount}{$condPrice}{$condSize}{$condColor}{$condPrint}{$condStock}{$groupByStock}", "status=1{$parseSearch}{$parseDepartment}{$parsePrice}{$parseStock}");
                        echo $Pager->getPaginator();
                    else:
                        Erro("<p class='al_center'><b>OPPSSS:</b> Desculpe, mas a sua filtragem não retornou resultados!</p>", E_USER_NOTICE);
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
?>