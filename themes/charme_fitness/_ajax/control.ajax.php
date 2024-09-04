<?php

session_start();

$getPost = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (empty($getPost) || empty($getPost['action'])):
    die('Acesso Negado!');
endif;

$strPost = array_map('strip_tags', $getPost);
$POST = array_map('trim', $strPost);

$Action = $POST['action'];
unset($POST['action']);

$jSON = null;

usleep(2000);

require '../../../_app/Config.inc.php';
$Read = new Read;
$Create = new Create;
$Update = new Update;
$Delete = new Delete;

switch ($Action):
    case 'search':
        $jSON['search'] = null;

        $search = $POST['search'];
        $Read->FullRead("SELECT pdt_title, pdt_name, pdt_cover, pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_title LIKE '%' :search '%'", "status=1&search={$search}");
        if ($Read->getResult()):
            $jSON['search'] .= '<ul>';
            foreach ($Read->getResult() as $PDT):
                if ($PDT['pdt_offer_price'] && $PDT['pdt_offer_start'] <= date('Y-m-d H:i:s') && $PDT['pdt_offer_end'] >= date('Y-m-d H:i:s')):
                    $jSON['search'] .= "<li><a href='" . BASE . "/produto/{$PDT['pdt_name']}#pdt' title='{$PDT['pdt_title']}'><img src='" . BASE . "/uploads/{$PDT['pdt_cover']}' alt='{$PDT['pdt_title']}' title='{$PDT['pdt_title']}'/><p>{$PDT['pdt_title']}<br/><span class='old_price'>R$ " . number_format($PDT['pdt_price'], 2, ',', '.') . "</span> <span class='new_price'>R$ " . number_format($PDT['pdt_offer_price'], 2, ',', '.') . "</span></p></a></li>";
                else:
                    $jSON['search'] .= "<li><a href='" . BASE . "/produto/{$PDT['pdt_name']}#pdt' title='{$PDT['pdt_title']}'><img src='" . BASE . "/uploads/{$PDT['pdt_cover']}' alt='{$PDT['pdt_title']}' title='{$PDT['pdt_title']}'/><p>{$PDT['pdt_title']}<br/><span class='new_price'>R$ " . number_format($PDT['pdt_price'], 2, ',', '.') . "</span></p></a></li>";
                endif;
            endforeach;
            $jSON['search'] .= '</ul>';
        endif;
        break;

    case 'get_pdt':
        $PdtId = $POST['pdt_id'];
        $Read->ExeRead(DB_PDT, "WHERE pdt_id = :id", "id={$PdtId}");
        $jSON['pdt'] = null;
        if ($Read->getResult()):
            extract($Read->getResult()[0]);

            $pdtViewUpdate = ['pdt_views' => $pdt_views + 1, 'pdt_lastview' => date('Y-m-d H:i:s')];
            $Update->ExeUpdate(DB_PDT, $pdtViewUpdate, "WHERE pdt_id = :id", "id={$pdt_id}");

            $CommentModerate = (COMMENT_MODERATE ? " AND (status = 1 OR status = 3)" : '');
            $Read->FullRead("SELECT id FROM " . DB_COMMENTS . " WHERE pdt_id = :pid{$CommentModerate}", "pid={$pdt_id}");
            $Aval = $Read->getRowCount();

            $Read->FullRead("SELECT SUM(rank) as total FROM " . DB_COMMENTS . " WHERE pdt_id = :pid{$CommentModerate}", "pid={$pdt_id}");
            $TotalAval = $Read->getResult()[0]['total'];
            $TotalRank = $Aval * 5;
            $getRank = ($TotalAval ? (($TotalAval / $TotalRank) * 50) / 10 : 0);
            $Rank = str_repeat("<li class='fa fa-star'></li>", intval($getRank)) . str_repeat("<li class='fa fa-star-o'></li>", 5 - intval($getRank));

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

            $jSON['pdt'] .= "<div class='products_modal'>";
            $jSON['pdt'] .= "<div class='products_modal_content'>";
            $jSON['pdt'] .= "<div class='products_modal_content_close modal_pdt_mobile'>";
            $jSON['pdt'] .= "<span class='j_close_modal_pdp' title='Fechar'>Fechar</span>";
            $jSON['pdt'] .= "</div>";
            $jSON['pdt'] .= "<section class='product' id='pdt'>";
            $jSON['pdt'] .= "<div class='container'>";
            $jSON['pdt'] .= "<div class='content'>";
            $jSON['pdt'] .= "<div class='product_image'>";
            $jSON['pdt'] .= "<div class='product_image_focus'>";
            $jSON['pdt'] .= "<img class='j_focus_image image-zoom' src='" . BASE . "/uploads/{$pdt_cover}' alt='{$pdt_title}' title='{$pdt_title}' data-zoom='" . BASE . "/uploads/{$pdt_cover}'/>";
            $jSON['pdt'] .= "</div>";
            $jSON['pdt'] .= "<div class='product_image_gallery'>";
            $jSON['pdt'] .= "<img class='j_select_gallery active' src='" . BASE . "/uploads/{$pdt_cover}' alt='{$pdt_title}' title='{$pdt_title}'/>";

            $Read->ExeRead(DB_PDT_GALLERY, "WHERE product_id = :id", "id={$pdt_id}");
            if ($Read->getResult()):
                foreach ($Read->getResult() as $GALLERY):
                    $jSON['pdt'] .= "<img class='j_select_gallery' src='" . BASE . "/uploads/{$GALLERY['image']}' alt='{$pdt_title}' title='{$pdt_title}'/>";
                endforeach;
            endif;

            $jSON['pdt'] .= "</div>";

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

            $jSON['pdt'] .= "<div class='actions_wishlist'>";
            $jSON['pdt'] .= "<span class='{$classWishlist}' title='{$titleWishlist}' data-pdt-id='{$pdt_id}'{$attrUserId}>";
            $jSON['pdt'] .= "<i class='{$iconWishlist}'></i>";
            $jSON['pdt'] .= "</span>";
            $jSON['pdt'] .= "</div>";

            $jSON['pdt'] .= "</div>";
            $jSON['pdt'] .= "<div class='product_info'>";
            $jSON['pdt'] .= "<header class='product_info_heading'>";
            $jSON['pdt'] .= "<h1>{$pdt_title}</h1>";
            $jSON['pdt'] .= "</header>";

            $jSON['pdt'] .= "<ul class='product_info_rating'>";
                $jSON['pdt'] .= "{$Rank}";
            $jSON['pdt'] .= "</ul>";

            if ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s') && strtotime($pdt_offer_end) - strtotime(date('Y-m-d H:i:s')) <= 86400):
                $jSON['pdt'] .= "<div class='countdown' data-expire='{$pdt_offer_end}'>";
                    $jSON['pdt'] .= "<div class='countdown_wrapper'>";
                        $jSON['pdt'] .= "<div>";
                            $jSON['pdt'] .= "<span>";
                                $jSON['pdt'] .= "Oferta<br/>";
                                $jSON['pdt'] .= "<span class='countdown_legend'>Acaba em:</span>";
                            $jSON['pdt'] .= "</span>";
                        $jSON['pdt'] .= "</div>";

                        $jSON['pdt'] .= "<div>";
                            $jSON['pdt'] .= "<span>";
                                $jSON['pdt'] .= "<span class='hours'>00</span>&nbsp;&nbsp;:&nbsp;&nbsp;<br/>";
                                $jSON['pdt'] .= "<span class='countdown_legend'>Hrs</span>";
                            $jSON['pdt'] .= "</span>";

                            $jSON['pdt'] .= "<span>";
                                $jSON['pdt'] .= "<span class='minutes'>00</span>&nbsp;&nbsp;:&nbsp;&nbsp;<br/>";
                                $jSON['pdt'] .= "<span class='countdown_legend'>Min</span>";
                            $jSON['pdt'] .= "</span>";

                            $jSON['pdt'] .= "<span>";
                                $jSON['pdt'] .= "<span class='seconds'>00</span><br/>";
                                $jSON['pdt'] .= "<span class='countdown_legend'>Seg</span>";
                            $jSON['pdt'] .= "</span>";
                        $jSON['pdt'] .= "</div>";
                    $jSON['pdt'] .= "</div>";
                $jSON['pdt'] .= "</div>";
            endif;

            $jSON['pdt'] .= "<div class='product_info_price'>";
            if ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s')):
                $PdtPrice = $pdt_offer_price;
                $jSON['pdt'] .= "<p class='old_price'>De: <span>R$ " . number_format($pdt_price, 2, ',', '.') . "</span></p>";
            else:
                $PdtPrice = $pdt_price;
            endif;

            $jSON['pdt'] .= "<div class='price_heading'>";
            $jSON['pdt'] .= "<span class='by'>Por<br/> <span>R$</span></span>";
            $jSON['pdt'] .= "<span class='price'>" . number_format($PdtPrice, 2, ',', '.') . "</span>";
            $jSON['pdt'] .= "</div>";

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
                $jSON['pdt'] .= "<p class='discount'>ou {$NumSplit}X de R$ {$SplitPrice}</p>";
            endif;

            $jSON['pdt'] .= "</div>";

            //INCLUDE CART ADD
            if ($pdt_inventory >= 1):
                $jSON['pdt'] .= "<form id='{$pdt_id}' class='wc_cart_add wc_online_content' name='cart_add' method='post' enctype='multipart/form-data'>";
                $jSON['pdt'] .= "<input name='pdt_id' type='hidden' value='{$pdt_id}'/>";

                $Read->FullRead("SELECT stock_id FROM " . DB_PDT_STOCK . " WHERE pdt_id = :id AND size_id IS NULL AND color_id IS NULL AND print_id IS NULL AND stock_inventory >= :inventory", "id={$pdt_id}&inventory=1");
                if ($Read->getResult()):
                    $openByColor = false;
                    $openBySizes = false;
                    $openByPrint = false;

                    $jSON['pdt'] .= "<input name='stock_id' type='hidden' value='{$Read->getResult()[0]['stock_id']}'/>";
                endif;

                $Read->FullRead("SELECT pdt_id FROM " . DB_PDT . " WHERE pdt_parent = :id", "id={$pdt_id}");
                if ($Read->getResult() || !empty($pdt_parent)):
                    $openByColor = false;

                    $Read->FullRead("SELECT p.pdt_name, s.stock_id, s.pdt_id, s.size_id, a.attr_color_code, a.attr_color_title FROM " . DB_PDT . " p INNER JOIN " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_COLORS . " a ON p.pdt_id = s.pdt_id AND s.color_id = a.attr_color_id WHERE (p.pdt_id = :id OR p.pdt_parent = :id OR p.pdt_id = :parent OR p.pdt_parent = :parent) AND s.color_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.color_id ORDER BY s.stock_id ASC", "id={$pdt_id}&parent={$pdt_parent}&inventory=1");
                    if ($Read->getResult()):
                        $jSON['pdt'] .= "<div class='color_content has_relatives'>";
                        $jSON['pdt'] .= "<p>Selecione uma <span>cor</span></p>";

                        foreach ($Read->getResult() as $StockVar):
                            if (strpos($StockVar['attr_color_code'], ',')):
                                $arr_color = explode(',', $StockVar['attr_color_code']);
                                $bg_color = (count($arr_color) == 2 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%);" : (count($arr_color) == 3 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]});" : "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]});"));
                            else:
                                $bg_color = "background-color: {$StockVar['attr_color_code']};";
                            endif;
                            $jSON['pdt'] .= "<a" . ($StockVar['pdt_id'] == $pdt_id ? ' class="active"' : '') . " href='" . BASE . '/produto/' . $StockVar['pdt_name'] . '#pdt' . "' title='{$StockVar['attr_color_title']}' style='{$bg_color}'>";
                            if (empty($StockVar['size_id'])):
                                $jSON['pdt'] .= "<input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . ($StockVar['pdt_id'] == $pdt_id ? ' checked="checked"' : '') . "/>";
                            endif;
                            $jSON['pdt'] .= "<div class='selected_color'>";
                            $jSON['pdt'] .= "<i class='fa fa-check-circle'></i>";
                            $jSON['pdt'] .= "</div>";
                            $jSON['pdt'] .= "</a> ";
                        endforeach;

                        $jSON['pdt'] .= "</div>";
                    endif;
                else:
                    $Read->FullRead("SELECT s.stock_id, s.size_id, s.color_id, s.stock_inventory, a.attr_color_code, a.attr_color_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_COLORS . " a ON s.color_id = a.attr_color_id WHERE s.pdt_id = :id AND s.color_id IS NOT NULL AND stock_inventory >= :inventory GROUP BY s.color_id ORDER BY s.stock_id ASC", "id={$pdt_id}&inventory=1");
                    if ($Read->getResult()):
                        if ($Read->getRowCount() > 1):
                            $openByColor = true;
                        else:
                            $openByColor = false;
                        endif;

                        $jSON['pdt'] .= "<div class='color_content no_relatives'>";
                        $jSON['pdt'] .= "<div class='boxing_loading'>";
                        $jSON['pdt'] .= "<p>Selecione uma <span>cor</span></p>";

                        foreach ($Read->getResult() as $StockVar):
                            if (strpos($StockVar['attr_color_code'], ',')):
                                $arr_color = explode(',', $StockVar['attr_color_code']);
                                $bg_color = (count($arr_color) == 2 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%);" : (count($arr_color) == 3 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]});" : "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]});"));
                            else:
                                $bg_color = "background-color: {$StockVar['attr_color_code']};";
                            endif;
                            $jSON['pdt'] .= "<label id='{$StockVar['stock_inventory']}' class='wc_select_color" . (!$openByColor ? ' active' : '') . "' title='{$StockVar['attr_color_title']}' style='{$bg_color}' data-pdt-id='{$pdt_id}' data-pdt-price='{$PdtPrice}' data-stock-color='{$StockVar['color_id']}'>";
                            if (empty($StockVar['size_id'])):
                                $jSON['pdt'] .= "<input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . (!$openByColor ? ' checked="checked"' : '') . "/>";
                            endif;
                            $jSON['pdt'] .= "<div class='selected_color'>";
                            $jSON['pdt'] .= "<i class='fa fa-check-circle'></i>";
                            $jSON['pdt'] .= "</div>";
                            $jSON['pdt'] .= "</label> ";
                        endforeach;

                        $jSON['pdt'] .= "</div>";
                        $jSON['pdt'] .= "<img class='image_loading' src='" . INCLUDE_PATH . "/images/loading.gif' alt='Carregando...' title='Carregando...'/>";
                        $jSON['pdt'] .= "</div>";
                    else:
                        $openByColor = false;
                    endif;
                endif;

                /* print */
                $Read->FullRead("SELECT pdt_id FROM " . DB_PDT . " WHERE pdt_parent = :id", "id={$pdt_id}");
                if ($Read->getResult() || !empty($pdt_parent)):
                    $openByPrint = false;

                    $Read->FullRead("SELECT p.pdt_name, s.stock_id, s.pdt_id, s.size_id, s.stock_inventory, a.attr_print_code, a.attr_print_title FROM " . DB_PDT . " p INNER JOIN " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_PRINTS . " a ON p.pdt_id = s.pdt_id AND s.print_id = a.attr_print_id WHERE (p.pdt_id = :id OR p.pdt_parent = :id OR p.pdt_id = :parent OR p.pdt_parent = :parent) AND s.print_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.print_id ORDER BY s.stock_id ASC;", "id={$pdt_id}&parent={$pdt_parent}&inventory=1");
                    if ($Read->getResult()):
                        $jSON['pdt'] .= "<div class='print_content has_relatives'>";
                        $jSON['pdt'] .= "<p>Selecione uma <span>estampa</span></p>";

                        foreach ($Read->getResult() as $StockVar):
                            $bg_print = "background: url(" . BASE . "/uploads/{$StockVar['attr_print_code']}) center / cover no-repeat;";
                            
                            $jSON['pdt'] .= "<a" . ($StockVar['pdt_id'] == $pdt_id ? ' class="active"' : '') . " href='" . BASE . '/produto/' . $StockVar['pdt_name'] . '#pdt' . "' title='{$StockVar['attr_print_title']}' style='{$bg_print}'>";
                            if (empty($StockVar['size_id'])):
                                $jSON['pdt'] .= "<input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . ($StockVar['pdt_id'] == $pdt_id ? ' checked="checked"' : '') . "/>";
                            endif;
                            $jSON['pdt'] .= "<div class='selected_print'>";
                            $jSON['pdt'] .= "<i class='fa fa-check-circle'></i>";
                            $jSON['pdt'] .= "</div>";
                            $jSON['pdt'] .= "</a> ";
                        endforeach;

                        $jSON['pdt'] .= "</div>";
                    endif;
                else:
                    $Read->FullRead("SELECT s.stock_id, s.size_id, s.print_id, s.stock_inventory, a.attr_print_code, a.attr_print_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_PRINTS . " a ON s.print_id = a.attr_print_id WHERE s.pdt_id = :id AND s.print_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.print_id ORDER BY s.stock_id ASC", "id={$pdt_id}&inventory=1");
                    if ($Read->getResult()):
                        if ($Read->getRowCount() > 1):
                            $openByPrint = true;
                        else:
                            $openByPrint = false;
                        endif;

                        $jSON['pdt'] .= "<div class='print_content no_relatives'>";
                        $jSON['pdt'] .= "<div class='boxing_loading'>";
                        $jSON['pdt'] .= "<p>Selecione uma <span>estampa</span></p>";

                        foreach ($Read->getResult() as $StockVar):
                            $bg_print = "background: url(" . BASE . "/uploads/{$StockVar['attr_print_code']}) center / cover no-repeat;";
                            
                            $jSON['pdt'] .= "<label id='{$StockVar['stock_inventory']}' class='wc_select_print" . (!$openByPrint ? ' active' : '') . "' title='{$StockVar['attr_print_title']}' style='{$bg_print}' data-pdt-id='{$pdt_id}' data-pdt-price='{$PdtPrice}' data-stock-print='{$StockVar['print_id']}'>";
                            if (empty($StockVar['size_id'])):
                                $jSON['pdt'] .= "<input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . (!$openByPrint ? ' checked="checked"' : '') . "/>";
                            endif;
                            $jSON['pdt'] .= "<div class='selected_print'>";
                            $jSON['pdt'] .= "<i class='fa fa-check-circle'></i>";
                            $jSON['pdt'] .= "</div>";
                            $jSON['pdt'] .= "</label> ";
                        endforeach;

                        $jSON['pdt'] .= "</div>";
                        $jSON['pdt'] .= "<img class='image_loading' src='" . INCLUDE_PATH . "/images/loading.gif' alt='Carregando...' title='Carregando...'/>";
                        $jSON['pdt'] .= "</div>";
                    else:
                        $openByPrint = false;
                    endif;
                endif;
                /* print */

                $Read->FullRead("SELECT s.stock_id, s.stock_inventory, a.attr_size_code, a.attr_size_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_SIZES . " a ON s.size_id = a.attr_size_id WHERE s.pdt_id = :id AND s.size_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.size_id ORDER BY s.stock_id ASC", "id={$pdt_id}&inventory=1");
                if ($Read->getResult()):
                    if ($Read->getRowCount() > 1):
                        $openBySizes = true;
                    else:
                        $openBySizes = false;
                    endif;

                    $jSON['pdt'] .= "<div class='size_content'>";
                    $jSON['pdt'] .= "<div class='boxing_loading'>";
                    $jSON['pdt'] .= "<p>Selecione um <span>tamanho</span></p>";

                    $jSON['pdt'] .= "<div class='wc_target_sizes'>";

                    foreach ($Read->getResult() as $StockVar):
                        $jSON['pdt'] .= "<label id='{$StockVar['stock_inventory']}' class='wc_select_size" . (!$openBySizes ? ' active' : '') . "' title='{$StockVar['attr_size_title']}' data-pdt-price='{$PdtPrice}'>";
                        $jSON['pdt'] .= $StockVar['attr_size_code'] . " <input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . (!$openBySizes ? ' checked="checked"' : '') . "/>";
                        $jSON['pdt'] .= "<span><i class='fa fa-check'></i></span>";
                        $jSON['pdt'] .= "</label> ";
                    endforeach;

                    $jSON['pdt'] .= "</div>";
                    $jSON['pdt'] .= "</div>";
                    $jSON['pdt'] .= "<img class='image_loading" . ($openByColor || $openByPrint ? ' invisible' : '') . "' src='" . INCLUDE_PATH . "/images/loading.gif' alt='Carregando...' title='Carregando...'/>";
                    $jSON['pdt'] .= "</div>";
                else:
                    $openBySizes = false;
                endif;

                $jSON['pdt'] .= "<div class='result_content'>";
                $jSON['pdt'] .= "<div class='boxing_loading'>";

                if ($pdt_unity):
                    $jSON['pdt'] .= "<div class='qtde_label'>";
                        $jSON['pdt'] .= "<span></span> <span>{$pdt_unity}</span> <span></span>";
                    $jSON['pdt'] .= "</div>";
                endif;

                $jSON['pdt'] .= "<div class='qtde_content'>";
                $jSON['pdt'] .= "<button id='{$pdt_id}' class='wc_cart_less minus' data-pdt-price='{$PdtPrice}'>-</button><input name='item_amount' type='text' value='1' step='{$pdt_step}' max='{$pdt_inventory}' readonly='readonly'/><button id='{$pdt_id}' class='wc_cart_plus plus' data-pdt-price='{$PdtPrice}'>+</button>";
                $jSON['pdt'] .= "</div>";

                $jSON['pdt'] .= "<div class='total_content'>";
                $jSON['pdt'] .= "<div class='purchase_total wc_target_total'>";
                $jSON['pdt'] .= "<p>Total de <span>1</span> " . ($pdt_unity ? $pdt_unity : 'item') . "</p>";
                $jSON['pdt'] .= "<p>Por <span>R$ " . number_format($PdtPrice, 2, ',', '.') . "</span></p>";

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
                    $jSON['pdt'] .= "<p>ou <span>{$NumSplit}x de R$ {$SplitPrice}</span></p>";
                endif;

                $jSON['pdt'] .= "</div>";

                $jSON['pdt'] .= "<div>";
                $jSON['pdt'] .= "<button class='btn_purchase'><i class='fa fa-shopping-bag'></i> " . ECOMMERCE_BUTTON_TAG . "</button>";
                $jSON['pdt'] .= "</div>";

                $jSON['pdt'] .= "</div>";

                $jSON['pdt'] .= "<div class='exchange_content'>";
                $jSON['pdt'] .= "<p>Troca garantida. <a class='wc_tab' href='#warranty' title='Veja as regras'>Veja as regras.</a> <span>Válido em todo território nacional</span></p>";
                $jSON['pdt'] .= "</div>";
                $jSON['pdt'] .= "</div>";

                $jSON['pdt'] .= "<img class='image_loading" . ($openByColor || $openByPrint || $openBySizes ? ' invisible' : '') . "' src='" . INCLUDE_PATH . "/images/loading.gif' alt='Carregando...' title='Carregando...'/>";
                $jSON['pdt'] .= "</div>";
                $jSON['pdt'] .= "</form>";
            endif;

            $jSON['pdt'] .= "</div>";
            $jSON['pdt'] .= "<div class='clear'></div>";
            $jSON['pdt'] .= "</div>";

            if (!empty($pdt_content)):
                $jSON['pdt'] .= "<div class='product_footer container'>";
                $jSON['pdt'] .= "<div class='content'>";
                $jSON['pdt'] .= "<ul>";
                $jSON['pdt'] .= "<li><a class='wc_tab wc_active' href='#description' title='Descrição'>Descrição</a></li> ";
                $jSON['pdt'] .= "<li><a class='wc_tab' href='#warranty' title='Garantia'>Garantia</a></li>";
                $jSON['pdt'] .= "</ul>";
                $jSON['pdt'] .= "<div class='product_footer_description wc_tab_target wc_active' id='description'>";
                $jSON['pdt'] .= "{$pdt_content}";
                $jSON['pdt'] .= "</div>";
                $jSON['pdt'] .= "<div class='product_footer_description wc_tab_target ds_none' id='warranty'>";
                $jSON['pdt'] .= "{$pdt_warranty}";
                $jSON['pdt'] .= "</div>";
                $jSON['pdt'] .= "<div class='clear'></div>";
                $jSON['pdt'] .= "</div>";
                $jSON['pdt'] .= "</div>";
            endif;

            $jSON['pdt'] .= "</div>";
            $jSON['pdt'] .= "</section>";

            $jSON['pdt'] .= "<div class='products_modal_content_close modal_pdt_desktop j_close_modal_pdp'>";
            $jSON['pdt'] .= "<i class='fa fa-close'></i>";
            $jSON['pdt'] .= "</div>";
            $jSON['pdt'] .= "</div>";
            $jSON['pdt'] .= "</div>";
        endif;
        break;

    case 'actions_wishlist':
        $PdtId = $POST['pdt_id'];
        $UserId = $POST['user_id'];

        $Read->FullRead("SELECT wishlist_id FROM " . DB_PDT_WISHLIST . " WHERE pdt_id = :pdt AND user_id = :user", "pdt={$PdtId}&user={$UserId}");
        if ($Read->getResult()):
            $Delete->ExeDelete(DB_PDT_WISHLIST, "WHERE pdt_id = :pdt AND user_id = :user", "pdt={$PdtId}&user={$UserId}");
        else:
            $DataCreate = ['pdt_id' => $PdtId, 'user_id' => $UserId];
            $Create->ExeCreate(DB_PDT_WISHLIST, $DataCreate);
        endif;

        $Read->FullRead("SELECT COUNT(wishlist_id) as total FROM " . DB_PDT_WISHLIST . " WHERE user_id = :user", "user={$UserId}");
        $jSON['total'] = $Read->getResult()[0]['total'];
        break;

    case 'browsing_history':
        $arrPdt = explode(',', $POST['pdt_id']);

        $browsing_history = filter_input(INPUT_COOKIE, 'browsing_history', FILTER_DEFAULT);
        if ($browsing_history):
            $arrIds = explode(',', $browsing_history);

            foreach ($arrPdt as $pdt):
                unset($arrIds[array_search($pdt, $arrIds)]);
            endforeach;

            $strIds = implode(',', $arrIds);
            setcookie('browsing_history', "{$strIds}", time() + 60 * 60 * 24 * 30, '/');
        endif;
        break;

    case 'add_newsletter':
        $email = $POST['email'];

        if (!Check::Email($email)):
            $jSON['success'] = false;
        else:
            $lists = explode(',', ACTIVE_CAMPAIGN_LISTS);
            $tags = ACTIVE_CAMPAIGN_TAGS;

            $activeCampaign = new ActiveCampaign;
            $activeCampaign->addActive($email, $lists, null, null, $tags);

            $jSON['success'] = true;
        endif;
        break;

    case 'get_sizes':
        $PdtId = $POST['id'];

        if (isset($POST['color'])):
            $Color = $POST['color'];
            $Read->FullRead("SELECT s.stock_id, s.stock_inventory, a.attr_size_code, a.attr_size_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_SIZES . " a ON s.size_id = a.attr_size_id WHERE s.pdt_id = :id AND s.size_id IS NOT NULL AND s.color_id = :color AND s.stock_inventory >= :inventory GROUP BY s.size_id ORDER BY s.stock_id ASC", "id={$PdtId}&color={$Color}&inventory=1");
        else:
            $Print = $POST['print'];
            $Read->FullRead("SELECT s.stock_id, s.stock_inventory, a.attr_size_code, a.attr_size_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_SIZES . " a ON s.size_id = a.attr_size_id WHERE s.pdt_id = :id AND s.size_id IS NOT NULL AND s.print_id = :print AND s.stock_inventory >= :inventory GROUP BY s.size_id ORDER BY s.stock_id ASC", "id={$PdtId}&print={$Print}&inventory=1");
        endif;

        if ($Read->getResult()):
            $jSON['sizes'] = null;
            foreach ($Read->getResult() as $StockVar):
                $jSON['sizes'] .= "<label class='combo_select_size' title='{$StockVar['attr_size_title']}'>{$StockVar['attr_size_code']} <input type='radio' name='stock_id' value='{$StockVar['stock_id']}'/><span><i class='fa fa-check'></i></span></label> ";
            endforeach;
        endif;
        break;

    case 'combo_cart_add':
        if (empty($_SESSION['wc_order'])):
            $_SESSION['wc_order'] = array();
        endif;

        $Read->FullRead("SELECT combo_discount FROM " . DB_PDT_COMBO . " WHERE pdt_id = :id", "id={$POST['pdt_id']}");
        if ($Read->getResult()):
            $_SESSION['wc_cupom'] = $Read->getResult()[0]['combo_discount'];
            $_SESSION['wc_cupom_code'] = "COMBO{$Read->getResult()[0]['combo_discount']}";
        endif;

        $comboList = explode(',', $POST['stock_id']);
        foreach ($comboList as $PDT):
            $Read->FullRead("SELECT pdt_id FROM " . DB_PDT_STOCK . " WHERE stock_id = :id", "id={$PDT}");
            $pdtId = $Read->getResult()[0]['pdt_id'];

            $Read->FullRead("SELECT pdt_title, pdt_inventory FROM " . DB_PDT . " WHERE pdt_id = :id", "id={$pdtId}");
            $CartPDT = $Read->getResult();

            $POST['item_amount'] = intval($POST['item_amount']);
            if (!$POST['item_amount']):
                $jSON['trigger'] = AjaxErro("<b>OPPSSS:</b> Desculpa, mas <b>{$POST['item_amount']}</b> não é uma quantidade válida para adiconar ao carrinho!", E_USER_NOTICE);
            elseif (!$Read->getResult()):
                $jSON['trigger'] = AjaxErro("<b>OPPSSS:</b> O produto solicitado não foi encontrado. Por favor, tente novamente!", E_USER_NOTICE);
            elseif ($CartPDT[0]['pdt_inventory'] < 1):
                $jSON['trigger'] = AjaxErro("<b>Desculpe:</b> No momento estamos sem estoque para o produto {$CartPDT[0]['pdt_title']}. Mas temos outras opções!", E_USER_NOTICE);
            else:
                if (empty($_SESSION['wc_order'][$PDT])):
                    $_SESSION['wc_order'][$PDT] = intval($POST['item_amount']);
                else:
                    $_SESSION['wc_order'][$PDT] += intval($POST['item_amount']);
                endif;

                $CartTotal = 0;
                foreach ($_SESSION['wc_order'] as $ItemId => $ItemAmount):
                    $Read->FullRead("SELECT pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_id = (SELECT pdt_id FROM " . DB_PDT_STOCK . " WHERE stock_id = :id)", "status=1&id={$ItemId}");
                    if ($Read->getResult()):
                        $ItemPrice = ($Read->getResult()[0]['pdt_offer_price'] && $Read->getResult()[0]['pdt_offer_start'] <= date('Y-m-d H:i:s') && $Read->getResult()[0]['pdt_offer_end'] >= date('Y-m-d H:i:s') ? $Read->getResult()[0]['pdt_offer_price'] : $Read->getResult()[0]['pdt_price']);
                        $CartTotal += $ItemPrice * $ItemAmount;
                    endif;
                endforeach;

                $CartCupom = (!empty($_SESSION['wc_cupom']) ? intval($_SESSION['wc_cupom']) : 0);
                $CartPrice = (empty($_SESSION['wc_cupom']) ? $CartTotal : $CartTotal * ((100 - $_SESSION['wc_cupom']) / 100));
                $jSON['cart_price'] = number_format($CartPrice, 2, ',', '.');

                //STOCK CONTROL
                $Read->FullRead("SELECT stock_inventory FROM " . DB_PDT_STOCK . " WHERE stock_id = :id", "id={$PDT}");
                if ($Read->getResult()[0]['stock_inventory'] <= $_SESSION['wc_order'][$PDT] && $Read->getResult()[0]['stock_inventory']):
                    $_SESSION['wc_order'][$PDT] = intval($Read->getResult()[0]['stock_inventory']);
                endif;
            endif;
        endforeach;

        $jSON['cart_amount'] = count($_SESSION['wc_order']);
        break;

    case 'combo_hide':
        $Read->FullRead("SELECT combo_list, combo_discount FROM " . DB_PDT_COMBO . " WHERE pdt_id = :id", "id={$POST['pdt_id']}");
        if ($Read->getResult()):
            $comboList = $Read->getResult()[0]['combo_list'];
            $comboDiscount = $Read->getResult()[0]['combo_discount'];
            $comboCount = 0;
            $comboPrice = 0;

            if (empty($_SESSION['combo']['hide'])):
                $_SESSION['combo']['hide'] = $POST['combo_item'];
            else:
                $_SESSION['combo']['hide'] .= ',' . $POST['combo_item'];
            endif;

            $Read->FullRead("SELECT pdt_id, pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE (pdt_id = :id OR pdt_id IN({$comboList})) AND pdt_id NOT IN({$_SESSION['combo']['hide']}) AND pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL)", "id={$POST['pdt_id']}&status=1");
            if ($Read->getResult()):
                $comboCount = $Read->getRowCount();

                foreach ($Read->getResult() as $PDT):
                    $comboPrice += ($PDT['pdt_offer_price'] && $PDT['pdt_offer_start'] <= date('Y-m-d H:i:s') && $PDT['pdt_offer_end'] >= date('Y-m-d H:i:s') ? $PDT['pdt_offer_price'] - ($PDT['pdt_offer_price'] * $comboDiscount / 100) : $PDT['pdt_price'] - ($PDT['pdt_price'] * $comboDiscount / 100));
                endforeach;
            endif;

            $jSON['combo_count'] = "Compre os <span>{$comboCount}</span> itens";
            $jSON['combo_price'] = "<span>por:</span> R$ " . number_format($comboPrice, 2, ',', '.');
        endif;
        break;

    case 'combo_restore':
        $jSON['content'] = null;
        $jSON['combo_count'] = null;
        $jSON['combo_price'] = null;

        if (!empty($_SESSION['combo']['hide'])):
            unset($_SESSION['combo']['hide']);
        endif;

        $Read->FullRead("SELECT c.combo_list, c.combo_discount, p.pdt_id, p.pdt_price, p.pdt_offer_price, p.pdt_offer_start, p.pdt_offer_end FROM " . DB_PDT_COMBO . " c INNER JOIN " . DB_PDT . " p ON c.pdt_id = p.pdt_id WHERE p.pdt_id = :id", "id={$POST['pdt_id']}");
        if ($Read->getResult()):
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

                $Read->FullRead("SELECT pdt_id, pdt_parent, pdt_name, pdt_title, pdt_cover, pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_id = :id AND pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL)", "id={$POST['pdt_id']}&status=1");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $PDT):
                        extract($PDT);

                        //CONTENT
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

                        $jSON['content'] .= "<article class='products_item'>";
                        $jSON['content'] .= "<div class='products_item_image'>";
                        $jSON['content'] .= "<a href='" . BASE . "/produto/{$pdt_name}' title='{$pdt_title}'>";
                        $jSON['content'] .= "<img src='" . BASE . "/uploads/{$pdt_cover}' alt='{$pdt_title}' title='{$pdt_title}'/>";
                        $jSON['content'] .= "</a>";

                        if ($discount):
                            $jSON['content'] .= "<div class='discount_product'>";
                            $jSON['content'] .= "<p><a href='" . BASE . "/produto/{$pdt_name}' title='{$pdt_title}'>-{$discount}% <i class='fa fa-circle-o faa-burst animated'></i></a></p>";
                            $jSON['content'] .= "</div>";
                        endif;

                        $jSON['content'] .= "<div class='action_purchase'>";
                            $jSON['content'] .= "<a href='" . BASE . "/produto/{$pdt_name}#pdt' title='Comprar'>";
                                $jSON['content'] .= "<i class='fa fa-shopping-bag'></i>";
                            $jSON['content'] .= "</a>";
                        $jSON['content'] .= "</div>";

                        $jSON['content'] .= "<div class='action_view'>";
                            $jSON['content'] .= "<a class='j_view_product' href='#' title='Espiar' data-pdt-id='{$pdt_id}'>";
                                $jSON['content'] .= "<i class='fa fa-search-plus'></i>";
                            $jSON['content'] .= "</a>";
                        $jSON['content'] .= "</div>";

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

                        $jSON['content'] .= "<div class='action_wishlist'>";
                            $jSON['content'] .= "<a class='{$classWishlist}' href='#' title='{$titleWishlist}' data-pdt-id='{$pdt_id}'{$attrUserId}>";
                                $jSON['content'] .= "<i class='{$iconWishlist}'></i>";
                            $jSON['content'] .= "</a>";
                        $jSON['content'] .= "</div>";
                        $jSON['content'] .= "</div>";

                        $jSON['content'] .= "<div class='auto_height'>";
                        $jSON['content'] .= "<div class='products_item_title'>";
                        $jSON['content'] .= "<h1>";
                        $jSON['content'] .= "<a href='" . BASE . "/produto/{$pdt_name}' title='{$pdt_title}'>{$pdt_title}</a>";
                        $jSON['content'] .= "</h1>";
                        $jSON['content'] .= "</div>";

                        $jSON['content'] .= "<div class='products_item_price'>";
                        if ($discount):
                            $jSON['content'] .= "<p><a href='" . BASE . "/produto/{$pdt_name}' title='{$pdt_title}'><span class='old_price'>de: R$ " . number_format($pdt_price, 2, ',', '.') . "</span> por: R$ " . number_format($PdtPrice, 2, ',', '.') . " <span class='installment'>{$NumSplit}X de R$ {$SplitPrice}</span></a></p>";
                        else:
                            $jSON['content'] .= "<p><a href='" . BASE . "/produto/{$pdt_name}' title='{$pdt_title}'>por: R$ " . number_format($PdtPrice, 2, ',', '.') . " <span class='installment'>{$NumSplit}X de R$ {$SplitPrice}</span></a></p>";
                        endif;
                        $jSON['content'] .= "</div>";
                        $jSON['content'] .= "</div>";

                        $jSON['content'] .= "<form class='combo_cart_add' name='combo_cart_add' method='post' enctype='multipart/form-data'>";
                        $Read->FullRead("SELECT stock_id FROM " . DB_PDT_STOCK . " WHERE pdt_id = :id AND size_id IS NULL AND color_id IS NULL AND print_id IS NULL AND stock_inventory >= :inventory", "id={$pdt_id}&inventory=1");
                        if ($Read->getResult()):
                            $openByColor = false;
                            $openBySizes = false;
                            $openByPrint = false;

                            $jSON['content'] .= "<input name='stock_id' type='hidden' value='{$Read->getResult()[0]['stock_id']}'/>";
                        endif;

                        $Read->FullRead("SELECT pdt_id FROM " . DB_PDT . " WHERE pdt_parent = :id", "id={$pdt_id}");
                        if ($Read->getResult() || !empty($pdt_parent)):
                            $openByColor = false;

                            $Read->FullRead("SELECT p.pdt_name, s.stock_id, s.pdt_id, s.size_id, a.attr_color_code, a.attr_color_title FROM " . DB_PDT . " p INNER JOIN " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_COLORS . " a ON p.pdt_id = s.pdt_id AND s.color_id = a.attr_color_id WHERE (p.pdt_id = :id OR p.pdt_parent = :id OR p.pdt_id = :parent OR p.pdt_parent = :parent) AND s.color_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.color_id ORDER BY s.stock_id ASC", "id={$pdt_id}&parent={$pdt_parent}&inventory=1");
                            if ($Read->getResult()):
                                $jSON['content'] .= "<div class='color_content has_relatives'>";
                                foreach ($Read->getResult() as $StockVar):
                                    if (strpos($StockVar['attr_color_code'], ',')):
                                        $arr_color = explode(',', $StockVar['attr_color_code']);
                                        $bg_color = (count($arr_color) == 2 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%);" : (count($arr_color) == 3 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]});" : "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]});"));
                                    else:
                                        $bg_color = "background-color: {$StockVar['attr_color_code']};";
                                    endif;

                                    $jSON['content'] .= "<a" . ($StockVar['pdt_id'] == $pdt_id ? ' class="active"' : '') . " href='" . BASE . "/produto/{$StockVar['pdt_name']}#pdt' title='{$StockVar['attr_color_title']}' style='{$bg_color}'>";
                                        if (empty($StockVar['size_id'])):
                                            $jSON['content'] .= "<input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . ($StockVar['pdt_id'] == $pdt_id ? ' checked="checked"' : '') . "/>";
                                        endif;

                                        $jSON['content'] .= "<div class='selected_color'>";
                                            $jSON['content'] .= "<i class='fa fa-check-circle'></i>";
                                        $jSON['content'] .= "</div>";
                                    $jSON['content'] .= "</a> ";
                                endforeach;
                                $jSON['content'] .= "</div>";
                            endif;
                        else:
                            $Read->FullRead("SELECT s.stock_id, s.size_id, s.color_id, s.stock_inventory, a.attr_color_code, a.attr_color_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_COLORS . " a ON s.color_id = a.attr_color_id WHERE s.pdt_id = :id AND s.color_id IS NOT NULL AND stock_inventory >= :inventory GROUP BY s.color_id ORDER BY s.stock_id ASC", "id={$pdt_id}&inventory=1");
                            if ($Read->getResult()):
                                if ($Read->getRowCount() > 1):
                                    $openByColor = true;
                                else:
                                    $openByColor = false;
                                endif;

                                $jSON['content'] .= "<div class='color_content no_relatives'>";
                                $jSON['content'] .= "<div class='boxing_loading'>";
                                foreach ($Read->getResult() as $StockVar):
                                    if (strpos($StockVar['attr_color_code'], ',')):
                                        $arr_color = explode(',', $StockVar['attr_color_code']);
                                        $bg_color = (count($arr_color) == 2 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%);" : (count($arr_color) == 3 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]});" : "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]});"));
                                    else:
                                        $bg_color = "background-color: {$StockVar['attr_color_code']};";
                                    endif;

                                    $jSON['content'] .= "<label id='{$StockVar['stock_inventory']}' class='combo_select_color" . (!$openByColor ? ' active' : '') . "' title='{$StockVar['attr_color_title']}' style='{$bg_color}' data-pdt-id='{$pdt_id}' data-stock-color='{$StockVar['color_id']}'>";
                                    if (empty($StockVar['size_id'])):
                                        $jSON['content'] .= "<input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . (!$openByColor ? ' checked="checked"' : '') . "/>";
                                    endif;

                                    $jSON['content'] .= "<div class='selected_color'>";
                                    $jSON['content'] .= "<i class='fa fa-check-circle'></i>";
                                    $jSON['content'] .= "</div>";
                                    $jSON['content'] .= "</label> ";
                                endforeach;
                                $jSON['content'] .= "</div>";

                                $jSON['content'] .= "<img class='image_loading' src='" . INCLUDE_PATH . "/images/loading.gif' alt='Carregando...' title='Carregando...'/>";
                                $jSON['content'] .= "</div>";
                            else:
                                $openByColor = false;
                            endif;
                        endif;

                        //START PRINT
                        $Read->FullRead("SELECT pdt_id FROM " . DB_PDT . " WHERE pdt_parent = :id", "id={$pdt_id}");
                        if ($Read->getResult() || !empty($pdt_parent)):
                            $openByPrint = false;

                            $Read->FullRead("SELECT p.pdt_name, s.stock_id, s.pdt_id, s.size_id, s.stock_inventory, a.attr_print_code, a.attr_print_title FROM " . DB_PDT . " p INNER JOIN " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_PRINTS . " a ON p.pdt_id = s.pdt_id AND s.print_id = a.attr_print_id WHERE (p.pdt_id = :id OR p.pdt_parent = :id OR p.pdt_id = :parent OR p.pdt_parent = :parent) AND s.print_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.print_id ORDER BY s.stock_id ASC;", "id={$pdt_id}&parent={$pdt_parent}&inventory=1");
                            if ($Read->getResult()):
                                $jSON['content'] .= "<div class='print_content has_relatives'>";
                                    foreach ($Read->getResult() as $StockVar):
                                        $bgPrint = "background: url(" . BASE . "/uploads/{$StockVar['attr_print_code']}) center / cover no-repeat;";

                                        $jSON['content'] .= "<a" . ($StockVar['pdt_id'] == $pdt_id ? ' class="active"' : '') . " href='" . BASE . "/produto/{$StockVar['pdt_name']}#pdt' title='{$StockVar['attr_print_title']}' style='{$bgPrint}'>";
                                            if (empty($StockVar['size_id'])):
                                                $jSON['content'] .= "<input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . ($StockVar['pdt_id'] == $pdt_id ? ' checked="checked"' : '') . "/>";
                                            endif;

                                            $jSON['content'] .= "<div class='selected_print'>";
                                                $jSON['content'] .= "<i class='fa fa-check-circle'></i>";
                                            $jSON['content'] .= "</div>";
                                        $jSON['content'] .= "</a> ";
                                    endforeach;
                                $jSON['content'] .= "</div>";
                            endif;
                        else:
                            $Read->FullRead("SELECT s.stock_id, s.size_id, s.print_id, s.stock_inventory, a.attr_print_code, a.attr_print_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_PRINTS . " a ON s.print_id = a.attr_print_id WHERE s.pdt_id = :id AND s.print_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.print_id ORDER BY s.stock_id ASC", "id={$pdt_id}&inventory=1");
                            if ($Read->getResult()):
                                if ($Read->getRowCount() > 1):
                                    $openByPrint = true;
                                else:
                                    $openByPrint = false;
                                endif;

                                $jSON['content'] .= "<div class='print_content no_relatives'>";
                                    $jSON['content'] .= "<div class='boxing_loading'>";
                                        foreach ($Read->getResult() as $StockVar):
                                            $bgPrint = "background: url(" . BASE . "/uploads/{$StockVar['attr_print_code']}) center / cover no-repeat;";

                                            $jSON['content'] .= "<label id='{$StockVar['stock_inventory']}' class='wc_select_print" . (!$openByPrint ? ' active' : '') . "' title='{$StockVar['attr_print_title']}' style='{$bgPrint}' data-pdt-id='{$pdt_id}' data-stock-print='{$StockVar['print_id']}'>";
                                                if (empty($StockVar['size_id'])):
                                                    $jSON['content'] .= "<input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . (!$openByPrint ? ' checked="checked"' : '') . "/>";
                                                endif;

                                                $jSON['content'] .= "<div class='selected_print'>";
                                                    $jSON['content'] .= "<i class='fa fa-check-circle'></i>";
                                                $jSON['content'] .= "</div>";
                                            $jSON['content'] .= "</label> ";
                                        endforeach;
                                    $jSON['content'] .= "</div>";

                                    $jSON['content'] .= "<img class='image_loading' src='" . INCLUDE_PATH . "/images/loading.gif' alt='Carregando...' title='Carregando...'>";
                                $jSON['content'] .= "</div>";
                            else:
                                $openByPrint = false;
                            endif;
                        endif;
                        //END PRINT

                        $Read->FullRead("SELECT s.stock_id, s.stock_inventory, a.attr_size_code, a.attr_size_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_SIZES . " a ON s.size_id = a.attr_size_id WHERE s.pdt_id = :id AND s.size_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.size_id ORDER BY s.stock_id ASC", "id={$pdt_id}&inventory=1");
                        if ($Read->getResult()):
                            if ($Read->getRowCount() > 1):
                                $openBySizes = true;
                            else:
                                $openBySizes = false;
                            endif;

                            $jSON['content'] .= "<div class='size_content'>";
                            $jSON['content'] .= "<div class='boxing_loading'>";
                            $jSON['content'] .= "<div class='combo_target_sizes'>";
                            foreach ($Read->getResult() as $StockVar):
                                $jSON['content'] .= "<label class='combo_select_size" . (!$openBySizes ? ' active' : '') . "' title='{$StockVar['attr_size_title']}'>";
                                $jSON['content'] .= "{$StockVar['attr_size_code']} <input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . (!$openBySizes ? ' checked="checked"' : '') . "/>";
                                $jSON['content'] .= "<span><i class='fa fa-check'></i></span>";
                                $jSON['content'] .= "</label> ";
                            endforeach;
                            $jSON['content'] .= "</div>";
                            $jSON['content'] .= "</div>";

                            $jSON['content'] .= "<img class='image_loading" . ($openByColor || $openByPrint ? ' invisible' : '') . "' src='" . INCLUDE_PATH . "/images/loading.gif' alt='Carregando...' title='Carregando...'/>";
                            $jSON['content'] .= "</div>";
                        endif;
                        $jSON['content'] .= "</form>";
                        $jSON['content'] .= "</article>";
                    endforeach;
                endif;

                // LISTA PRODUTOS CONECTADOS AO COMBO
                foreach ($comboResult as $PDT):
                    extract($PDT);

                    //CONTENT
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

                    $jSON['content'] .= "<article class='products_item'>";
                    $jSON['content'] .= "<div class='products_item_image'>";
                    $jSON['content'] .= "<a href='" . BASE . "/produto/{$pdt_name}' title='{$pdt_title}'>";
                    $jSON['content'] .= "<img src='" . BASE . "/uploads/{$pdt_cover}' alt='{$pdt_title}' title='{$pdt_title}'/>";
                    $jSON['content'] .= "</a>";

                    if ($discount):
                        $jSON['content'] .= "<div class='discount_product'>";
                        $jSON['content'] .= "<p><a href='" . BASE . "/produto/{$pdt_name}' title='{$pdt_title}'>{$discount}% <i class='fa fa-circle-o faa-burst animated'></i></a></p>";
                        $jSON['content'] .= "</div>";
                    endif;

                    $jSON['content'] .= "<div class='products_close'>";
                        $jSON['content'] .= "<span class='j_combo_hide fa fa-close' data-pdt-id='{$pdt_id}'></span>";
                    $jSON['content'] .= "</div>";

                    $jSON['content'] .= "<div class='action_purchase'>";
                        $jSON['content'] .= "<a href='" . BASE . "/produto/{$pdt_name}#pdt' title='Comprar'>";
                            $jSON['content'] .= "<i class='fa fa-shopping-bag'></i>";
                        $jSON['content'] .= "</a>";
                    $jSON['content'] .= "</div>";

                    $jSON['content'] .= "<div class='action_view'>";
                        $jSON['content'] .= "<a class='j_view_product' href='#' title='Espiar' data-pdt-id='{$pdt_id}'>";
                            $jSON['content'] .= "<i class='fa fa-search-plus'></i>";
                        $jSON['content'] .= "</a>";
                    $jSON['content'] .= "</div>";

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

                    $jSON['content'] .= "<div class='action_wishlist'>";
                        $jSON['content'] .= "<a class='{$classWishlist}' href='#' title='{$titleWishlist}' data-pdt-id='{$pdt_id}'{$attrUserId}>";
                            $jSON['content'] .= "<i class='{$iconWishlist}'></i>";
                        $jSON['content'] .= "</a>";
                    $jSON['content'] .= "</div>";
                    $jSON['content'] .= "</div>";

                    $jSON['content'] .= "<div class='auto_height'>";
                    $jSON['content'] .= "<div class='products_item_title'>";
                    $jSON['content'] .= "<h1>";
                    $jSON['content'] .= "<a href='" . BASE . "/produto/{$pdt_name}' title='{$pdt_title}'>{$pdt_title}</a>";
                    $jSON['content'] .= "</h1>";
                    $jSON['content'] .= "</div>";

                    $jSON['content'] .= "<div class='products_item_price'>";
                    if ($discount):
                        $jSON['content'] .= "<p><a href='" . BASE . "/produto/{$pdt_name}' title='{$pdt_title}'><span class='old_price'>de: R$ " . number_format($pdt_price, 2, ',', '.') . "</span> por: R$ " . number_format($PdtPrice, 2, ',', '.') . " <span class='installment'>{$NumSplit}X de R$ {$SplitPrice}</span></a></p>";
                    else:
                        $jSON['content'] .= "<p><a href='" . BASE . "/produto/{$pdt_name}' title='{$pdt_title}'>por: R$ " . number_format($PdtPrice, 2, ',', '.') . " <span class='installment'>{$NumSplit}X de R$ {$SplitPrice}</span></a></p>";
                    endif;
                    $jSON['content'] .= "</div>";
                    $jSON['content'] .= "</div>";

                    $jSON['content'] .= "<form class='combo_cart_add' name='combo_cart_add' method='post' enctype='multipart/form-data'>";
                    $Read->FullRead("SELECT stock_id FROM " . DB_PDT_STOCK . " WHERE pdt_id = :id AND size_id IS NULL AND color_id IS NULL AND print_id IS NULL AND stock_inventory >= :inventory", "id={$pdt_id}&inventory=1");
                    if ($Read->getResult()):
                        $openByColor = false;
                        $openBySizes = false;
                        $openByPrint = false;

                        $jSON['content'] .= "<input name='stock_id' type='hidden' value='{$Read->getResult()[0]['stock_id']}'/>";
                    endif;

                    $Read->FullRead("SELECT pdt_id FROM " . DB_PDT . " WHERE pdt_parent = :id", "id={$pdt_id}");
                    if ($Read->getResult() || !empty($pdt_parent)):
                        $openByColor = false;

                        $Read->FullRead("SELECT p.pdt_name, s.stock_id, s.size_id, a.attr_color_code, a.attr_color_title FROM " . DB_PDT . " p INNER JOIN " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_COLORS . " a ON p.pdt_id = s.pdt_id AND s.color_id = a.attr_color_id WHERE (p.pdt_id = :id OR p.pdt_parent = :id OR p.pdt_id = :parent OR p.pdt_parent = :parent) AND s.color_id IS NOT NULL AND s.stock_inventory >= :inventory ORDER BY s.stock_id ASC", "id={$pdt_id}&parent={$pdt_parent}&inventory=1");
                        if ($Read->getResult()):
                            $jSON['content'] .= "<div class='color_content has_relatives'>";
                                foreach ($Read->getResult() as $StockVar):
                                    if (strpos($StockVar['attr_color_code'], ',')):
                                        $arr_color = explode(',', $StockVar['attr_color_code']);
                                        $bg_color = (count($arr_color) == 2 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%);" : (count($arr_color) == 3 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]});" : "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]});"));
                                    else:
                                        $bg_color = "background-color: {$StockVar['attr_color_code']};";
                                    endif;

                                    $jSON['content'] .= "<a" . ($StockVar['pdt_id'] == $pdt_id ? ' class="active"' : '') . " href='" . BASE . "/produto/{$StockVar['pdt_name']}#pdt' title='{$StockVar['attr_color_title']}' style='{$bg_color}'>";
                                        if (empty($StockVar['size_id'])):
                                            $jSON['content'] .= "<input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . ($StockVar['pdt_id'] == $pdt_id ? ' checked="checked"' : '') . "/>";
                                        endif;

                                        $jSON['content'] .= "<div class='selected_color'>";
                                            $jSON['content'] .= "<i class='fa fa-check-circle'></i>";
                                        $jSON['content'] .= "</div>";
                                    $jSON['content'] .= "</a> ";
                                endforeach;
                            $jSON['content'] .= "</div>";
                        endif;
                    else:
                        $Read->FullRead("SELECT s.stock_id, s.size_id, s.color_id, s.stock_inventory, a.attr_color_code, a.attr_color_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_COLORS . " a ON s.color_id = a.attr_color_id WHERE s.pdt_id = :id AND s.color_id IS NOT NULL AND stock_inventory >= :inventory GROUP BY s.color_id ORDER BY s.stock_id ASC", "id={$pdt_id}&inventory=1");
                        if ($Read->getResult()):
                            if ($Read->getRowCount() > 1):
                                $openByColor = true;
                            else:
                                $openByColor = false;
                            endif;

                            $jSON['content'] .= "<div class='color_content no_relatives'>";
                            $jSON['content'] .= "<div class='boxing_loading'>";
                            foreach ($Read->getResult() as $StockVar):
                                if (strpos($StockVar['attr_color_code'], ',')):
                                    $arr_color = explode(',', $StockVar['attr_color_code']);
                                    $bg_color = (count($arr_color) == 2 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%); background-image: linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%);" : (count($arr_color) == 3 ? "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]}); background-image: linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]});" : "background-image: -webkit-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -moz-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -ms-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: -o-linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]}); background-image: linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]});"));
                                else:
                                    $bg_color = "background-color: {$StockVar['attr_color_code']};";
                                endif;

                                $jSON['content'] .= "<label id='{$StockVar['stock_inventory']}' class='combo_select_color" . (!$openByColor ? ' active' : '') . "' title='{$StockVar['attr_color_title']}' style='{$bg_color}' data-pdt-id='{$pdt_id}' data-stock-color='{$StockVar['color_id']}'>";
                                if (empty($StockVar['size_id'])):
                                    $jSON['content'] .= "<input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . (!$openByColor ? ' checked="checked"' : '') . "/>";
                                endif;

                                $jSON['content'] .= "<div class='selected_color'>";
                                $jSON['content'] .= "<i class='fa fa-check-circle'></i>";
                                $jSON['content'] .= "</div>";
                                $jSON['content'] .= "</label> ";
                            endforeach;
                            $jSON['content'] .= "</div>";

                            $jSON['content'] .= "<img class='image_loading' src='" . INCLUDE_PATH . "/images/loading.gif' alt='Carregando...' title='Carregando...'/>";
                            $jSON['content'] .= "</div>";
                        else:
                            $openByColor = false;
                        endif;
                    endif;

                    //START PRINT
                    $Read->FullRead("SELECT pdt_id FROM " . DB_PDT . " WHERE pdt_parent = :id", "id={$pdt_id}");
                    if ($Read->getResult() || !empty($pdt_parent)):
                        $openByPrint = false;

                        $jSON['content'] .= "<div class='print_content has_relatives'>";
                        foreach ($Read->getResult() as $StockVar):
                            $bgPrint = "background: url(" . BASE . "/uploads/{$StockVar['attr_print_code']}) center / cover no-repeat;";

                            $jSON['content'] .= "<a" . ($StockVar['pdt_id'] == $pdt_id ? ' class="active"' : '') . " href='" . BASE . "/produto/{$StockVar['pdt_name']}#pdt' title='{$StockVar['attr_print_title']}' style='{$bgPrint}'>";
                                if (empty($StockVar['size_id'])):
                                    $jSON['content'] .= "<input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . ($StockVar['pdt_id'] == $pdt_id ? ' checked="checked"' : '') . "/>";
                                endif;

                                $jSON['content'] .= "<div class='selected_print'>";
                                    $jSON['content'] .= "<i class='fa fa-check-circle'></i>";
                                $jSON['content'] .= "</div>";
                            $jSON['content'] .= "</a> ";
                        endforeach;
                        $jSON['content'] .= "</div>";
                    else:
                        $Read->FullRead("SELECT s.stock_id, s.size_id, s.print_id, s.stock_inventory, a.attr_print_code, a.attr_print_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_PRINTS . " a ON s.print_id = a.attr_print_id WHERE s.pdt_id = :id AND s.print_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.print_id ORDER BY s.stock_id ASC", "id={$pdt_id}&inventory=1");
                        if ($Read->getResult()):
                            if ($Read->getRowCount() > 1):
                                $openByPrint = true;
                            else:
                                $openByPrint = false;
                            endif;

                            $jSON['content'] .= "<div class='print_content no_relatives'>";
                            $jSON['content'] .= "<div class='boxing_loading'>";
                            foreach ($Read->getResult() as $StockVar):
                                $bgPrint = "background: url(" . BASE . "/uploads/{$StockVar['attr_print_code']}) center / cover no-repeat;";

                                $jSON['content'] .= "<label id='{$StockVar['stock_inventory']}' class='combo_select_print" . (!$openByPrint ? ' active' : '') . "' title='{$StockVar['attr_print_title']}' style='{$bgPrint}' data-pdt-id='{$pdt_id}' data-stock-print='{$StockVar['print_id']}'>";
                                if (empty($StockVar['size_id'])):
                                    $jSON['content'] .= "<input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . (!$openByPrint ? ' checked="checked"' : '') . "/>";
                                endif;

                                $jSON['content'] .= "<div class='selected_print'>";
                                $jSON['content'] .= "<i class='fa fa-check-circle'></i>";
                                $jSON['content'] .= "</div>";
                                $jSON['content'] .= "</label> ";
                            endforeach;
                            $jSON['content'] .= "</div>";

                            $jSON['content'] .= "<img class='image_loading' src='" . INCLUDE_PATH . "/images/loading.gif' alt='Carregando...' title='Carregando...'>";
                            $jSON['content'] .= "</div>";
                        else:
                            $openByPrint = false;
                        endif;
                    endif;
                    //END PRINT

                    $Read->FullRead("SELECT s.stock_id, s.stock_inventory, a.attr_size_code, a.attr_size_title FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_SIZES . " a ON s.size_id = a.attr_size_id WHERE s.pdt_id = :id AND s.size_id IS NOT NULL AND s.stock_inventory >= :inventory GROUP BY s.size_id ORDER BY s.stock_id ASC", "id={$pdt_id}&inventory=1");
                    if ($Read->getResult()):
                        if ($Read->getRowCount() > 1):
                            $openBySizes = true;
                        else:
                            $openBySizes = false;
                        endif;

                        $jSON['content'] .= "<div class='size_content'>";
                        $jSON['content'] .= "<div class='boxing_loading'>";
                        $jSON['content'] .= "<div class='combo_target_sizes'>";
                        foreach ($Read->getResult() as $StockVar):
                            $jSON['content'] .= "<label class='combo_select_size" . (!$openBySizes ? ' active' : '') . "' title='{$StockVar['attr_size_title']}'>";
                            $jSON['content'] .= "{$StockVar['attr_size_code']} <input type='radio' name='stock_id' value='{$StockVar['stock_id']}'" . (!$openBySizes ? ' checked="checked"' : '') . "/>";
                            $jSON['content'] .= "<span><i class='fa fa-check'></i></span>";
                            $jSON['content'] .= "</label> ";
                        endforeach;
                        $jSON['content'] .= "</div>";
                        $jSON['content'] .= "</div>";

                        $jSON['content'] .= "<img class='image_loading" . ($openByColor || $openByPrint ? ' invisible' : '') . "' src='" . INCLUDE_PATH . "/images/loading.gif' alt='Carregando...' title='Carregando...'/>";
                        $jSON['content'] .= "</div>";
                    endif;
                    $jSON['content'] .= "</form>";
                    $jSON['content'] .= "</article>";
                endforeach;

                $jSON['combo_count'] = "Compre os <span>{$comboCount}</span> itens";
                $jSON['combo_price'] = "<span>por:</span> R$ " . number_format($comboPrice, 2, ',', '.');
            endif;
        endif;
        break;
endswitch;

echo json_encode($jSON);
