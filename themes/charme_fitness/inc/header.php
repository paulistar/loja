<?php
require '_cdn/widgets/ecommerce/cart.inc.php';
require '_cdn/widgets/contact/contact.wc.php';
?>
<header class="header">
    <div class="header_mobile">
        <div class="header_mobile_wrap">
            <div class="header_mobile_logo container">
                <div class="content">
                    <a href="<?= BASE; ?>" title="<?= SITE_NAME; ?>">
                        <img src="<?= INCLUDE_PATH; ?>/images/logo_mobile.png" alt="<?= SITE_NAME; ?>" title="<?= SITE_NAME; ?>"/>
                    </a>

                    <div class="clear"></div>
                </div>
            </div><div class="header_mobile_nav container">
                <div class="content">
                    <ul>
                        <li>
                            <a class="<?= ($URL[0] == 'pedido' ? 'active' : ''); ?>" href="<?= BASE; ?>/pedido/home#cart" title="Meu Carrinho">
                                <i class="fa fa-shopping-bag">
                                    <?php
                                    $cartClass = (!empty($_SESSION['wc_order']) ? 'cart_count active' : 'cart_count');
                                    $cartCount = (!empty($_SESSION['wc_order']) ? count($_SESSION['wc_order']) : '');
                                    ?>

                                    <span class="<?= $cartClass; ?>"><?= $cartCount; ?></span>
                                </i>
                            </a>
                        </li><li>
                            <a class="<?= ($URL[0] == 'conta' ? 'active' : ''); ?>" href="<?= BASE; ?>/conta" title="Minha Conta">
                                <i class="fa fa-user-circle-o"></i>
                            </a>
                        </li><?php
                        if (isset($_SESSION['userLogin']['user_id'])):
                            $Read->LinkResult(DB_PDT_WISHLIST, 'user_id', $_SESSION['userLogin']['user_id']);
                            $wishlistClass = (!empty($Read->getResult()) ? 'j_menu_wishlist active' : 'j_menu_wishlist');
                            $wishlistCount = (!empty($Read->getResult()) >= 1 ? $Read->getRowCount() : '');
                            ?><li>
                                <a class="<?= ($URL[0] == 'favoritos' ? 'active' : ''); ?>" href="<?= BASE; ?>/favoritos" title="Meus Favoritos">
                                    <i class="fa fa-heart">
                                        <span class="<?= $wishlistClass; ?>"><?= $wishlistCount; ?></span>
                                    </i>
                                </a>
                            </li><?php
                        endif;
                        ?><li>
                            <a class="j_open_search_mobile" href="#" title="Pesquisar Produtos">
                                <i class="fa fa-search"></i>
                            </a>
                        </li><li>
                            <a class="j_open_categories_mobile" href="#" title="Categorias">
                                <i class="fa fa-bars"></i>
                            </a>
                        </li>
                    </ul>

                    <div class="clear"></div>
                </div>
            </div>
        </div>

        <div class="header_mobile_search container">
            <div class="content">
                <form class="one_input j_search" name="search" method="post" action="" enctype="multipart/form-data">
                    <input class="one_input_field" type="search" name="s" placeholder="oque você está procurando?" autocomplete="off" required="required"/>
                    <button class="one_input_button" type="submit">
                        <i class="fa fa-search"></i>
                    </button>

                    <div class="realtime_search"></div>
                </form>

                <div class="clear"></div>
            </div>
        </div>

        <div class="header_mobile_categories container">
            <div class="content">
                <?php
                $Read->FullRead("SELECT cat_id, cat_title, cat_name FROM " . DB_PDT_CATS . " WHERE cat_parent IS NULL ORDER BY cat_title ASC");
                if ($Read->getResult()):
                    echo "<ul class='header_mobile_categories_ul'>";
                    foreach ($Read->getResult() as $SES):
                        $Read->FullRead("SELECT cat_id, cat_title, cat_name FROM " . DB_PDT_CATS . " WHERE cat_parent = :parent ORDER BY cat_title ASC", "parent={$SES['cat_id']}");

                        echo "<li>";
                        echo "<div>";
                        echo "<div class='j_cat_open_end_close'>" . ($Read->getResult() ? "<i class='fa fa-angle-double-right'></i>" : "<i class='fa fa-angle-double-down'></i>") . "</div>";
                        echo "<a href='" . BASE . "/produtos/{$SES['cat_name']}' title='{$SES['cat_title']}'>{$SES['cat_title']}</a>";
                        echo "</div>";

                        if ($Read->getResult()):
                            echo "<ul>";
                            foreach ($Read->getResult() as $CAT):
                                $Read->FullRead("SELECT cat_id, cat_title, cat_name FROM " . DB_PDT_CATS . " WHERE cat_parent = :parent ORDER BY cat_title ASC", "parent={$CAT['cat_id']}");

                                echo "<li>";
                                echo "<div>";
                                echo "<div class='j_cat_open_end_close'>" . ($Read->getResult() ? "<i class='fa fa-angle-double-right'></i>" : "<i class='fa fa-angle-double-down'></i>") . "</div>";
                                echo "<a href='" . BASE . "/produtos/{$CAT['cat_name']}' title='{$CAT['cat_title']}'>{$CAT['cat_title']}</a>";
                                echo "</div>";

                                if ($Read->getResult()):
                                    echo "<ul>";
                                    foreach ($Read->getResult() as $SUBCAT):
                                        echo "<li>";
                                        echo "<div>";
                                        echo "<div class='j_cat_open_end_close'><i class='fa fa-angle-double-down'></i></div>";
                                        echo "<a href='" . BASE . "/produtos/{$SUBCAT['cat_name']}' title='{$SUBCAT['cat_title']}'>{$SUBCAT['cat_title']}</a>";
                                        echo "</div>";
                                        echo "</li>";
                                    endforeach;
                                    echo "</ul>";
                                endif;
                                echo "</li>";
                            endforeach;
                            echo "</ul>";
                        endif;
                        echo "</li>";
                    endforeach;
                    echo "</ul>";
                endif;
                ?>

                <div class="clear"></div>
            </div>
        </div>
    </div>

    <div class="header_desktop">
        <div class="container">
            <div class="content" style="display: flex; justify-content: space-between; align-items: center; padding: 25px 0;">
                <div class="header_desktop_logo">
                    <h1>
                        <a href="<?= BASE; ?>" title="<?= SITE_NAME; ?>">
                            <span><?= SITE_NAME; ?></span>
                            <img src="<?= INCLUDE_PATH; ?>/images/logo.png" alt="<?= SITE_NAME; ?>" title="<?= SITE_NAME; ?>"/>
                        </a>
                    </h1>
                </div><div class="header_desktop_search">
                    <form class="one_input j_search" name="search" method="post" action="" enctype="multipart/form-data">
                        <input class="one_input_field" type="search" name="s" placeholder="oque você está procurando?" autocomplete="off" required="required"/>
                        <button class="one_input_button" type="submit">
                            <i class="fa fa-search"></i>
                        </button>

                        <div class="realtime_search"></div>
                    </form>
                </div><div class="header_desktop_buttons">
                    <a class="header_desktop_buttons_account<?= ($URL[0] == 'conta' ? ' active' : ''); ?>"
                       href="<?= BASE; ?>/conta" title="Minha Conta"></a>

                    <?php
                    if (!empty($_SESSION['userLogin']['user_id'])):
                        $Read->LinkResult(DB_PDT_WISHLIST, 'user_id', $_SESSION['userLogin']['user_id']);
                        $wishlistClass = (!empty($Read->getResult()) ? 'j_menu_wishlist active' : 'j_menu_wishlist');
                        $wishlistCount = (!empty($Read->getResult()) >= 1 ? $Read->getRowCount() : '');
                        ?>
                        <a class="header_desktop_buttons_wishlist<?= ($URL[0] == 'favoritos' ? ' active' : ''); ?>"
                           href="<?= BASE; ?>/favoritos" title="Meus Favoritos">
                            <span class="<?= $wishlistClass; ?>"><?= $wishlistCount; ?></span>
                        </a>
                    <?php
                    else:
                        ?>
                        <a class="header_desktop_buttons_wishlist j_force_login" href="<?= BASE; ?>/favoritos"
                           title="Meus Favoritos">
                            <span></span>
                        </a>
                    <?php
                    endif;
                    ?>

                    <a class="header_desktop_buttons_cart<?= ($URL[0] == 'pedido' ? ' active' : ''); ?>"
                       href="<?= BASE; ?>/pedido/home#cart" title="Meu Carrinho">
                        <?php
                        $cartClass = (!empty($_SESSION['wc_order']) ? 'cart_count active' : 'cart_count');
                        $cartCount = (!empty($_SESSION['wc_order']) ? count($_SESSION['wc_order']) : '');
                        ?>

                        <span class="<?= $cartClass; ?>"><?= $cartCount; ?></span>
                    </a>
                </div>

                <div class="clear"></div>
            </div>
        </div>

        <div class="header_desktop_categories container">
            <div class="content">
                <ul class="header_desktop_categories_ul">
                    <?php
                    $Read->FullRead("SELECT cat_id, cat_title, cat_name FROM " . DB_PDT_CATS . " WHERE cat_parent IS NULL ORDER BY cat_title ASC");
                    if ($Read->getResult()):
                        foreach ($Read->getResult() as $SES):
                            ?>
                            <li>
                                <a href='<?= BASE . '/produtos/' . $SES['cat_name']; ?>' title='<?= $SES['cat_title']; ?>'><?= $SES['cat_title']; ?></a>
                                <?php
                                $Read->FullRead("SELECT cat_id, cat_title, cat_name FROM " . DB_PDT_CATS . " WHERE cat_parent = :cat ORDER BY cat_title ASC", "cat={$SES['cat_id']}");
                                if ($Read->getResult()):
                                    ?>
                                    <div>
                                        <div>
                                            <?php
                                            foreach ($Read->getResult() as $CAT):
                                                ?>
                                                <ul>
                                                    <li><a href="<?= BASE . '/produtos/' . $CAT['cat_name']; ?>" title="<?= $CAT['cat_title']; ?>"><?= $CAT['cat_title']; ?></a></li>
                                                    <?php
                                                    $Read->setPlaces("cat={$CAT['cat_id']}");
                                                    if ($Read->getResult()):
                                                        foreach ($Read->getResult() as $SUBCAT):
                                                            ?>
                                                            <li><a href="<?= BASE . '/produtos/' . $SUBCAT['cat_name']; ?>" title="<?= $SUBCAT['cat_title']; ?>"><?= $SUBCAT['cat_title']; ?></a></li>
                                                            <?php
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </ul>
                                                <?php
                                            endforeach;
                                            ?>
                                        </div><div>
                                            <a href="<?= BASE; ?>/produtos/moda-fitness" title="Moda Fitness">
                                                <img src="<?= BASE; ?>/uploads/banners/2017/12/confira-a-linha-de-lingerie.jpg" alt="Moda Fitness" title="Moda Fitness"/>
                                            </a>
                                        </div>
                                    </div>
                                    <?php
                                endif;
                                ?>
                            </li>
                            <?php
                        endforeach;
                    endif;
                    ?>

                    <!-- CATEGORIAS ESTÁTICAS -->
                    <li>
                        <a href="#" title="Moda Feminina">Acessórios</a>
                    </li>
                    <li>
                        <a href="#" title="Calçados">Calçados</a>
                    </li>
                    <li>
                        <a href="#" title="Moda Praia">Moda Praia</a>
                    </li>
                    <li>
                        <a href="#" title="Promoções">Promoções</a>
                    </li>
                    <li>
                        <a href="#" title="Lançamentos">Lançamentos</a>
                    </li>
                </ul>

                <div class="clear"></div>
            </div>
        </div>
    </div>
</header>

<div class="force_login">
    <div class="force_login_content">
        <div class="force_login_content_close">
            <svg class="j_force_login_close" width="14" height="14" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg" ratio="1">
                <line fill="none" stroke="#999999" stroke-width="1.1" x1="1" y1="1" x2="13" y2="13"></line>
                <line fill="none" stroke="#999999" stroke-width="1.1" x1="13" y1="1" x2="1" y2="13"></line>
            </svg>
        </div>

        <img src="<?= INCLUDE_PATH; ?>/images/cart-heart.png" alt="">

        <p class="force_login_content_message">
            Faça login para pode adicionar este e outros produtos aos seus favorito
        </p>

        <a class="force_login_content_button" title="Minha Conta" href="<?= BASE; ?>/conta">
            Minha Conta
        </a>
    </div>
</div>