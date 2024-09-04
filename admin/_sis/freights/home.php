<!----------------------------------
######## CUSTOM BY ALISSON #########
----------------------------------->

<?php
$AdminLevel = LEVEL_WC_PRODUCTS_FREIGHTS;
if (!APP_FREIGHTS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

//AUTO DELETE POST TRASH
if (DB_AUTO_TRASH):
    $Delete = new Delete;
    $Delete->ExeDelete(DB_PDT_CATS, "WHERE cat_title IS NULL AND cat_parent IS NULL AND cat_id >= :st", "st=1");
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-truck j_freight_open">Fretes</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span> Fretes
        </p>
    </div>
</header>

<div class="dashboard_content">
    <div class="freight">
        <div class="freight__box">
            <form class="freight__filter">
                <div class="freight__close">
                    <button class="j_freight_close" type="button">
                        <i class="icon-cross icon-notext"></i>
                    </button>
                </div>

                <div class="freight__search">
                    <input class="j_value_type is-disabled" type="search" value="Estados"/>
                    <button class="j_location_type" type="button">
                        <i class="icon-list2 icon-notext"></i>
                    </button>
                </div>

                <div class="freight__search">
                    <input class="j_search_freight" type="search" name="search" placeholder="Espirito Santo"/>
                    <button type="button">
                        <i class="icon-search icon-notext j_freight_loading"></i>
                    </button>
                </div>

                <button class="freight__selected j_freight_selected" type="button" data-status="1">
                    <i class="icon-checkmark icon-notext"></i>
                </button>

                <button class="freight__blocked j_freight_selected" type="button" data-status="0">
                    <i class="icon-blocked icon-notext"></i>
                </button>

                <div class="freight__pagination">
                    <button type="button">
                        <i class="icon-arrow-left icon-notext"></i>
                    </button>

                    <button type="button">
                        <i class="icon-radio-checked icon-notext"></i>
                    </button>

                    <button type="button">
                        <i class="icon-arrow-right icon-notext"></i>
                    </button>
                </div>
            </form>

            <div class="freight__content">
                <div class="freight_rule j_states is-active">
                    <?php
                    $Read->ExeRead(DB_STATES);
                    if ($Read->getResult()):
                        foreach ($Read->getResult() as $STATE):

                            $Read->ExeRead(DB_FREIGHTS, 'WHERE uf = :uf AND city IS NULL AND 
                            district IS NULL', "uf={$STATE['uf']}");

                            $days = ECOMMERCE_SHIPMENT_FIXED_DAYS;
                            $price = number_format(ECOMMERCE_SHIPMENT_FIXED_PRICE, 2, ',', '.');
                            $blocked = '';
                            $checked = '';

                            if ($Read->getResult()):
                                $days = $Read->getResult()[0]['days'];
                                $price = number_format($Read->getResult()[0]['price'], 2, ',', '.');

                                if ($Read->getResult()[0]['status'] == 0):
                                    $blocked = 'is-blocked';
                                    $checked = 'checked';
                                endif;
                            endif;
                            ?>

                            <form class="freight__form" name="freight" method="post">
                                <input type="hidden" name="callback" value="Freights">
                                <input type="hidden" name="callback_action" value="state">

                                <label class="freight__term">
                                    <span class="freight__legend">R$</span>
                                    <input class="freight__field maskMoney" type="text" name="price"
                                           value="<?= $price; ?>"
                                           placeholder="<?= $price; ?>">
                                </label>

                                <label class="freight__term">
                                    <span class="freight__legend">Até</span>
                                    <input class="freight__days" type="text" name="days" value="<?= $days; ?>"
                                           placeholder="<?= $days; ?>" autocomplete="off">
                                    <span class="freight__legend">dias</span>
                                </label>

                                <div class="freight__action">
                                    <button type="submit" class="freight__send j_freight_send">
                                        <i class="icon-share icon-notext"></i>
                                    </button>

                                    <label class="icon-blocked icon-notext freight__status j_status <?= $blocked; ?>">
                                        <input class="cities__check" type="checkbox" name="status" <?= $checked; ?>>
                                    </label>
                                </div>

                                <input class="freight__uf" type="text" name="uf" value="<?= $STATE['uf']; ?>">
                                <input class="freight__name" type="text" value="<?= $STATE['name']; ?>">
                            </form>

                        <?php
                        endforeach;
                    endif;
                    ?>
                </div>

                <div class="freight_rule j_cities">
                    <?php
                    $Read->ExeRead(DB_CITIES, 'LIMIT :limit', 'limit=28');
                    if ($Read->getResult()):
                        foreach ($Read->getResult() as $CITY):

                            $Read->ExeRead(DB_FREIGHTS, 'WHERE uf = :uf AND city = :city AND 
                            district IS NULL', "uf={$CITY['uf']}&city={$CITY['name']}");

                            $days = ECOMMERCE_SHIPMENT_FIXED_DAYS;
                            $price = number_format(ECOMMERCE_SHIPMENT_FIXED_PRICE, 2, ',', '.');
                            $blocked = '';
                            $checked = '';

                            if ($Read->getResult()):
                                $days = $Read->getResult()[0]['days'];
                                $price = number_format($Read->getResult()[0]['price'], 2, ',', '.');

                                if ($Read->getResult()[0]['status'] == 0):
                                    $blocked = 'is-blocked';
                                    $checked = 'checked';
                                endif;
                            endif;
                            ?>

                            <form class="freight__form" name="freight" method="post">
                                <input type="hidden" name="callback" value="Freights">
                                <input type="hidden" name="callback_action" value="city">

                                <label class="freight__price">
                                    <input class="freight__field maskMoney" type="text" name="price"
                                           value="<?= $price; ?>" placeholder="R$ 12,00">
                                </label>

                                <label class="freight__term">
                                    <span class="freight__legend">Até</span>
                                    <input class="freight__days" type="text" name="days" value="<?= $days; ?>"
                                           placeholder="<?= $days; ?>" autocomplete="off">
                                    <span class="freight__legend">dias</span>
                                </label>

                                <div class="freight__action">
                                    <button type="submit" class="freight__send j_freight_send">
                                        <i class="icon-share icon-notext"></i>
                                    </button>

                                    <label class="icon-blocked icon-notext freight__status j_status <?= $blocked; ?>">
                                        <input class="cities__check" type="checkbox" name="status" <?= $checked; ?>>
                                    </label>
                                </div>

                                <input class="freight__uf" type="text" name="uf" value="<?= $CITY['uf']; ?>">
                                <input class="freight__name" type="text" name="city" value="<?= $CITY['name']; ?>">
                            </form>

                        <?php
                        endforeach;
                    endif;
                    ?>
                </div>

                <div class="freight_rule j_districts">
                    <?php
                    $Read->ExeRead(DB_DISTRICTS, 'LIMIT :limit', 'limit=28');
                    if ($Read->getResult()):
                        foreach ($Read->getResult() as $DISTRICT):

                            $city = explode(' - ', $DISTRICT['name'])[1];
                            $district = explode(' - ', $DISTRICT['name'])[0];

                            $Read->ExeRead(DB_FREIGHTS, 'WHERE uf = :uf AND city = :city AND
                            district = :district', "uf={$DISTRICT['uf']}&city={$city}&district={$district}");

                            $days = ECOMMERCE_SHIPMENT_FIXED_DAYS;
                            $price = number_format(ECOMMERCE_SHIPMENT_FIXED_PRICE, 2, ',', '.');
                            $blocked = '';
                            $checked = '';

                            if ($Read->getResult()):
                                $days = $Read->getResult()[0]['days'];
                                $price = number_format($Read->getResult()[0]['price'], 2, ',', '.');

                                if ($Read->getResult()[0]['status'] == 0):
                                    $blocked = 'is-blocked';
                                    $checked = 'checked';
                                endif;
                            endif;
                            ?>

                            <form class="freight__form" name="freight" method="post">
                                <input type="hidden" name="callback" value="Freights">
                                <input type="hidden" name="callback_action" value="district">
                                <input type="hidden" name="city" value="<?= $city; ?>">
                                <input type="hidden" name="district" value="<?= $district; ?>">

                                <label class="freight__price">
                                    <input class="freight__field maskMoney" type="text" name="price"
                                           value="<?= $price; ?>" placeholder="R$ 12,00">
                                </label>

                                <label class="freight__term">
                                    <span class="freight__legend">Até</span>
                                    <input class="freight__days" type="text" name="days" value="<?= $days; ?>"
                                           placeholder="<?= $days; ?>" autocomplete="off">
                                    <span class="freight__legend">dias</span>
                                </label>

                                <div class="freight__action">
                                    <button type="submit" class="freight__send j_freight_send">
                                        <i class="icon-share icon-notext"></i>
                                    </button>

                                    <label class="icon-blocked icon-notext freight__status j_status <?= $blocked; ?>">
                                        <input class="districts__check" type="checkbox" name="status" <?= $checked; ?>>
                                    </label>
                                </div>

                                <input class="freight__uf" type="text" name="uf" value="<?= $DISTRICT['uf']; ?>">
                                <input class="freight__name" type="text" value="<?= $DISTRICT['name']; ?>">
                            </form>

                        <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>