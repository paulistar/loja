<div class="not_found container">
    <div class="content">
        <h1>404</h1>
        <p class="tagline"><i class="fa fa-exclamation-circle"></i> Oops! A página requisitada não foi encontrada!</p>

        <div class="clear"></div>
    </div>
</div>

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
