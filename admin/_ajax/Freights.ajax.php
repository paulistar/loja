<?php

session_start();
require '../../_app/Config.inc.php';
$NivelAcess = LEVEL_WC_PRODUCTS_FREIGHTS;

if (!APP_FREIGHTS || empty($_SESSION['userLogin']) || empty($_SESSION['userLogin']['user_level']) || $_SESSION['userLogin']['user_level'] < $NivelAcess):
    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!',
        E_USER_ERROR);
    echo json_encode($jSON);
    die;
endif;

usleep(50000);

//DEFINE O CALLBACK E RECUPERA O POST
$jSON = null;
$CallBack = 'Freights';
$PostData = filter_input_array(INPUT_POST, FILTER_DEFAULT);

//VALIDA AÇÃO
if ($PostData && $PostData['callback_action'] && $PostData['callback'] == $CallBack):
    //PREPARA OS DADOS
    $Case = $PostData['callback_action'];
    unset($PostData['callback'], $PostData['callback_action']);

    // AUTO INSTANCE OBJECT READ
    if (empty($Read)):
        $Read = new Read;
    endif;

    // AUTO INSTANCE OBJECT CREATE
    if (empty($Create)):
        $Create = new Create;
    endif;

    // AUTO INSTANCE OBJECT UPDATE
    if (empty($Update)):
        $Update = new Update;
    endif;

    // AUTO INSTANCE OBJECT DELETE
    if (empty($Delete)):
        $Delete = new Delete;
    endif;

    //SELECIONA AÇÃO
    switch ($Case):
        case 'state':
            $PostData['city'] = null;
            $PostData['district'] = null;
            $PostData['price'] = (!empty($PostData['price']) ? str_replace(',', '.',
                str_replace('.', '', $PostData['price'])) : str_replace(',', '.',
                str_replace('.', '', ECOMMERCE_SHIPMENT_FIXED_PRICE)));
            $PostData['days'] = (!empty($PostData['days']) ? $PostData['days'] : ECOMMERCE_SHIPMENT_FIXED_DAYS);
            $PostData['status'] = (!empty($PostData['status']) ? '0' : 1);

            $Read->ExeRead(DB_FREIGHTS, 'WHERE uf = :uf AND city IS NULL AND district IS NULL',
                "uf={$PostData['uf']}");

            if (!$Read->getResult()):
                $Create->ExeCreate(DB_FREIGHTS, $PostData);
            else:
                $Update->ExeUpdate(DB_FREIGHTS, $PostData, 'WHERE uf = :uf AND city IS NULL AND district IS NULL',
                    "uf={$PostData['uf']}");
            endif;

            $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>TUDO CERTO: </b> A regra de frete foi atualizada com sucesso!");
            break;

        case 'city':
            $PostData['district'] = null;
            $PostData['price'] = (!empty($PostData['price']) ? str_replace(',', '.',
                str_replace('.', '', $PostData['price'])) : str_replace(',', '.',
                str_replace('.', '', ECOMMERCE_SHIPMENT_FIXED_PRICE)));
            $PostData['days'] = (!empty($PostData['days']) ? $PostData['days'] : ECOMMERCE_SHIPMENT_FIXED_DAYS);
            $PostData['status'] = (!empty($PostData['status']) ? '0' : 1);

            $Read->ExeRead(DB_FREIGHTS, 'WHERE uf = :uf AND city = :city AND district IS NULL',
                "uf={$PostData['uf']}&city={$PostData['city']}");

            if (!$Read->getResult()):
                $Create->ExeCreate(DB_FREIGHTS, $PostData);
            else:
                $Update->ExeUpdate(DB_FREIGHTS, $PostData, 'WHERE uf = :uf AND city = :city AND district IS NULL',
                    "uf={$PostData['uf']}&city={$PostData['city']}");
            endif;

            $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>TUDO CERTO: </b> A regra de frete foi atualizada com sucesso!");
            break;

        case 'district':
            $PostData['price'] = (!empty($PostData['price']) ? str_replace(',', '.',
                str_replace('.', '', $PostData['price'])) : str_replace(',', '.',
                str_replace('.', '', ECOMMERCE_SHIPMENT_FIXED_PRICE)));
            $PostData['days'] = (!empty($PostData['days']) ? $PostData['days'] : ECOMMERCE_SHIPMENT_FIXED_DAYS);
            $PostData['status'] = (!empty($PostData['status']) ? '0' : 1);

            $Read->ExeRead(
                DB_FREIGHTS, 'WHERE uf = :uf AND city = :city AND district = :district',
                "uf={$PostData['uf']}&city={$PostData['city']}&district={$PostData['district']}"
            );

            if (!$Read->getResult()):
                $Create->ExeCreate(DB_FREIGHTS, $PostData);
            else:
                $Update->ExeUpdate(
                    DB_FREIGHTS, $PostData,
                    'WHERE uf = :uf AND city = :city AND district = :district',
                    "uf={$PostData['uf']}&city={$PostData['city']}&district={$PostData['district']}"
                );
            endif;

            $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>TUDO CERTO: </b> A regra de frete foi atualizada com sucesso!");
            break;

        case 'content':
            $jSON['content'] = null;

            /* states */
            if ($PostData['type'] == 'Estados'):
                $condSearch = (!empty($PostData['search']) ? "WHERE name LIKE '%' :search '%' OR uf LIKE '%' :search '%' " : "");
                $parseSearch = (!empty($PostData['search']) ? "search={$PostData['search']}" : "");

                $Read->ExeRead(DB_STATES, "{$condSearch}", "{$parseSearch}");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $STATE):
                        $Read->ExeRead(
                            DB_FREIGHTS,
                            'WHERE uf = :uf AND city IS NULL AND district IS NULL',
                            "uf={$STATE['uf']}"
                        );

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

                        $jSON['content'] .= "<form class='freight__form' name='freight' method='post'>";
                        $jSON['content'] .= "<input type='hidden' name='callback' value='Freights'>";
                        $jSON['content'] .= "<input type='hidden' name='callback_action' value='state'>";

                        $jSON['content'] .= "<label class='freight__price'>";
                        $jSON['content'] .= "<input class='freight__field maskMoney' type='text' name='price' value='{$price}' placeholder='R$ 12,00'>";
                        $jSON['content'] .= "</label>";

                        $jSON['content'] .= "<label class='freight__term'>";
                        $jSON['content'] .= "<span class='freight__legend'>Até</span>";
                        $jSON['content'] .= "<input class='freight__days' type='text' name='days' value='{$days}' placeholder='{$days}' autocomplete='off'>";
                        $jSON['content'] .= "<span class='freight__legend'>dias</span>";
                        $jSON['content'] .= "</label>";

                        $jSON['content'] .= "<div class='freight__action'>";
                        $jSON['content'] .= "<button type='submit' class='freight__send j_freight_send'>";
                        $jSON['content'] .= "<i class='icon-share icon-notext'></i>";
                        $jSON['content'] .= "</button>";

                        $jSON['content'] .= "<label class='icon-blocked icon-notext freight__status j_status {$blocked}'>";
                        $jSON['content'] .= "<input class='cities__check' type='checkbox' name='status' {$checked}>";
                        $jSON['content'] .= "</label>";
                        $jSON['content'] .= "</div>";

                        $jSON['content'] .= "<input class='freight__uf' type='text' name='uf' value='{$STATE['uf']}'>";
                        $jSON['content'] .= "<input class='freight__name' type='text' value='{$STATE['name']}'>";
                        $jSON['content'] .= "</form>";
                    endforeach;
                endif;
            endif;

            /* cities */
            if ($PostData['type'] == 'Cidades'):
                $condSearch = (!empty($PostData['search']) ? "WHERE name LIKE '%' :search '%' " : "");
                $parseSearch = (!empty($PostData['search']) ? "search={$PostData['search']}&" : "");

                $condOffset = (!empty($PostData['offset']) ? " OFFSET :offset" : "");
                $parseOffset = (!empty($PostData['offset']) ? "&offset={$PostData['offset']}" : "");

                $Read->ExeRead(DB_CITIES, "{$condSearch}LIMIT :limit{$condOffset}",
                    "{$parseSearch}limit=28{$parseOffset}");

                if ($Read->getResult()):
                    foreach ($Read->getResult() as $CITY):
                        $Read->ExeRead(
                            DB_FREIGHTS,
                            'WHERE uf = :uf AND city = :city AND district IS NULL',
                            "uf={$CITY['uf']}&city={$CITY['name']}"
                        );

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

                        $jSON['content'] .= "<form class='freight__form' name='freight' method='post'>";
                        $jSON['content'] .= "<input type='hidden' name='callback' value='Freights'>";
                        $jSON['content'] .= "<input type='hidden' name='callback_action' value='city'>";

                        $jSON['content'] .= "<label class='freight__price'>";
                        $jSON['content'] .= "<input class='freight__field maskMoney' type='text' name='price' value='{$price}' placeholder='R$ 12,00'>";
                        $jSON['content'] .= "</label>";

                        $jSON['content'] .= "<label class='freight__term'>";
                        $jSON['content'] .= "<span class='freight__legend'>Até</span>";
                        $jSON['content'] .= "<input class='freight__days' type='text' name='days' value='{$days}' placeholder='{$days}' autocomplete='off'>";
                        $jSON['content'] .= "<span class='freight__legend'>dias</span>";
                        $jSON['content'] .= "</label>";

                        $jSON['content'] .= "<div class='freight__action'>";
                        $jSON['content'] .= "<button type='submit' class='freight__send j_freight_send'>";
                        $jSON['content'] .= "<i class='icon-share icon-notext'></i>";
                        $jSON['content'] .= "</button>";

                        $jSON['content'] .= "<label class='icon-blocked icon-notext freight__status j_status {$blocked}'>";
                        $jSON['content'] .= "<input class='cities__check' type='checkbox' name='status' {$checked}>";
                        $jSON['content'] .= "</label>";
                        $jSON['content'] .= "</div>";

                        $jSON['content'] .= "<input class='freight__uf' type='text' name='uf' value='{$CITY['uf']}'>";
                        $jSON['content'] .= '<input class="freight__name" type="text" name="city" value="' . $CITY['name'] . '">';
                        $jSON['content'] .= "</form>";
                    endforeach;
                endif;
            endif;

            /* districts */
            if ($PostData['type'] == 'Bairros'):
                $condSearch = (!empty($PostData['search']) ? "WHERE name LIKE '%' :search '%' " : "");
                $parseSearch = (!empty($PostData['search']) ? "search={$PostData['search']}&" : "");

                $condOffset = (!empty($PostData['offset']) ? " OFFSET :offset" : "");
                $parseOffset = (!empty($PostData['offset']) ? "&offset={$PostData['offset']}" : "");

                $Read->ExeRead(DB_DISTRICTS, "{$condSearch}LIMIT :limit{$condOffset}",
                    "{$parseSearch}limit=28{$parseOffset}");

                if ($Read->getResult()):
                    foreach ($Read->getResult() as $DISTRICT):
                        $city = explode(' - ', $DISTRICT['name'])[1];
                        $district = explode(' - ', $DISTRICT['name'])[0];

                        $Read->ExeRead(DB_FREIGHTS, 'WHERE uf = :uf AND city = :city AND district = 
                        :district', "uf={$DISTRICT['uf']}&city={$city}&district={$district}");

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

                        $jSON['content'] .= "<form class='freight__form' name='freight' method='post'>";
                        $jSON['content'] .= "<input type='hidden' name='callback' value='Freights'>";
                        $jSON['content'] .= "<input type='hidden' name='callback_action' value='district'>";
                        $jSON['content'] .= "<input type='hidden' name='city' value='{$city}'>";
                        $jSON['content'] .= "<input type='hidden' name='district' value='{$district}'>";

                        $jSON['content'] .= "<label class='freight__price'>";
                        $jSON['content'] .= "<input class='freight__field maskMoney' type='text' name='price' value='{$price}' placeholder='R$ 12,00'>";
                        $jSON['content'] .= "</label>";

                        $jSON['content'] .= "<label class='freight__term'>";
                        $jSON['content'] .= "<span class='freight__legend'>Até</span>";
                        $jSON['content'] .= "<input class='freight__days' type='text' name='days' value='{$days}' placeholder='{$days}' autocomplete='off'>";
                        $jSON['content'] .= "<span class='freight__legend'>dias</span>";
                        $jSON['content'] .= "</label>";

                        $jSON['content'] .= "<div class='freight__action'>";
                        $jSON['content'] .= "<button type='submit' class='freight__send j_freight_send'>";
                        $jSON['content'] .= "<i class='icon-share icon-notext'></i>";
                        $jSON['content'] .= "</button>";

                        $jSON['content'] .= "<label class='icon-blocked icon-notext freight__status j_status {$blocked}'>";
                        $jSON['content'] .= "<input class='cities__check' type='checkbox' name='status' {$checked}>";
                        $jSON['content'] .= "</label>";
                        $jSON['content'] .= "</div>";

                        $jSON['content'] .= "<input class='freight__uf' type='text' name='uf' value='{$DISTRICT['uf']}'>";
                        $jSON['content'] .= '<input class="freight__name" type="text" value="' . $DISTRICT['name'] . '">';
                        $jSON['content'] .= "</form>";
                    endforeach;
                endif;
            endif;
            break;

        case 'selected':
            $jSON['content'] = null;

            /* states */
            if ($PostData['type'] == 'Estados'):
                $Read->ExeRead(
                    DB_FREIGHTS,
                    'WHERE uf IS NOT NULL AND city IS NULL AND district IS NULL AND status = :status',
                    "status={$PostData['status']}"
                );

                if ($Read->getResult()):
                    foreach ($Read->getResult() as $STATE):
                        $days = $STATE['days'];
                        $price = number_format($STATE['price'], 2, ',', '.');
                        $blocked = ($STATE['status'] == 0 ? 'is-blocked' : '');
                        $checked = ($STATE['status'] == 0 ? 'checked' : '');

                        $Read->LinkResult(DB_STATES, 'uf', $STATE['uf'], 'name');
                        $STATE['name'] = $Read->getResult()[0]['name'];

                        $jSON['content'] .= "<form class='freight__form' name='freight' method='post'>";
                        $jSON['content'] .= "<input type='hidden' name='callback' value='Freights'>";
                        $jSON['content'] .= "<input type='hidden' name='callback_action' value='state'>";

                        $jSON['content'] .= "<label class='freight__price'>";
                        $jSON['content'] .= "<input class='freight__field maskMoney' type='text' name='price' value='{$price}' placeholder='R$ 12,00'>";
                        $jSON['content'] .= "</label>";

                        $jSON['content'] .= "<label class='freight__term'>";
                        $jSON['content'] .= "<span class='freight__legend'>Até</span>";
                        $jSON['content'] .= "<input class='freight__days' type='text' name='days' value='{$days}' placeholder='{$days}' autocomplete='off'>";
                        $jSON['content'] .= "<span class='freight__legend'>dias</span>";
                        $jSON['content'] .= "</label>";

                        $jSON['content'] .= "<div class='freight__action'>";
                        $jSON['content'] .= "<button type='submit' class='freight__send j_freight_send'>";
                        $jSON['content'] .= "<i class='icon-share icon-notext'></i>";
                        $jSON['content'] .= "</button>";

                        $jSON['content'] .= "<label class='icon-blocked icon-notext freight__status j_status {$blocked}'>";
                        $jSON['content'] .= "<input class='cities__check' type='checkbox' name='status' {$checked}>";
                        $jSON['content'] .= "</label>";
                        $jSON['content'] .= "</div>";

                        $jSON['content'] .= "<input class='freight__uf' type='text' name='uf' value='{$STATE['uf']}'>";
                        $jSON['content'] .= "<input class='freight__name' type='text' value='{$STATE['name']}'>";
                        $jSON['content'] .= "</form>";
                    endforeach;
                endif;
            endif;

            /* cities */
            if ($PostData['type'] == 'Cidades'):
                $Read->ExeRead(
                    DB_FREIGHTS,
                    'WHERE uf IS NOT NULL AND city IS NOT NULL AND district IS NULL AND status = :status',
                    "status={$PostData['status']}"
                );

                if ($Read->getResult()):
                    foreach ($Read->getResult() as $CITY):
                        $days = $CITY['days'];
                        $price = number_format($CITY['price'], 2, ',', '.');
                        $blocked = ($CITY['status'] == 0 ? 'is-blocked' : '');
                        $checked = ($CITY['status'] == 0 ? 'checked' : '');

                        $jSON['content'] .= "<form class='freight__form' name='freight' method='post'>";
                        $jSON['content'] .= "<input type='hidden' name='callback' value='Freights'>";
                        $jSON['content'] .= "<input type='hidden' name='callback_action' value='city'>";

                        $jSON['content'] .= "<label class='freight__price'>";
                        $jSON['content'] .= "<input class='freight__field maskMoney' type='text' name='price' value='{$price}' placeholder='R$ 12,00'>";
                        $jSON['content'] .= "</label>";

                        $jSON['content'] .= "<label class='freight__term'>";
                        $jSON['content'] .= "<span class='freight__legend'>Até</span>";
                        $jSON['content'] .= "<input class='freight__days' type='text' name='days' value='{$days}' placeholder='{$days}' autocomplete='off'>";
                        $jSON['content'] .= "<span class='freight__legend'>dias</span>";
                        $jSON['content'] .= "</label>";

                        $jSON['content'] .= "<div class='freight__action'>";
                        $jSON['content'] .= "<button type='submit' class='freight__send j_freight_send'>";
                        $jSON['content'] .= "<i class='icon-share icon-notext'></i>";
                        $jSON['content'] .= "</button>";

                        $jSON['content'] .= "<label class='icon-blocked icon-notext freight__status j_status {$blocked}'>";
                        $jSON['content'] .= "<input class='cities__check' type='checkbox' name='status' {$checked}>";
                        $jSON['content'] .= "</label>";
                        $jSON['content'] .= "</div>";

                        $jSON['content'] .= "<input class='freight__uf' type='text' name='uf' value='{$CITY['uf']}'>";
                        $jSON['content'] .= '<input class="freight__name" type="text" name="city" value="' . $CITY['city'] . '">';
                        $jSON['content'] .= "</form>";
                    endforeach;
                endif;
            endif;

            /* districts */
            if ($PostData['type'] == 'Bairros'):
                $Read->ExeRead(
                    DB_FREIGHTS,
                    'WHERE uf IS NOT NULL AND city IS NOT NULL AND district IS NOT NULL AND status = :status',
                    "status={$PostData['status']}"
                );

                if ($Read->getResult()):
                    foreach ($Read->getResult() as $DISTRICT):
                        $days = $DISTRICT['days'];
                        $price = number_format($DISTRICT['price'], 2, ',', '.');
                        $blocked = ($DISTRICT['status'] == 0 ? 'is-blocked' : '');
                        $checked = ($DISTRICT['status'] == 0 ? 'checked' : '');

                        $jSON['content'] .= "<form class='freight__form' name='freight' method='post'>";
                        $jSON['content'] .= "<input type='hidden' name='callback' value='Freights'>";
                        $jSON['content'] .= "<input type='hidden' name='callback_action' value='district'>";
                        $jSON['content'] .= "<input type='hidden' name='city' value='{$DISTRICT['city']}'>";
                        $jSON['content'] .= "<input type='hidden' name='district' value='{$DISTRICT['district']}'>";

                        $jSON['content'] .= "<label class='freight__price'>";
                        $jSON['content'] .= "<input class='freight__field maskMoney' type='text' name='price' value='{$price}' placeholder='R$ 12,00'>";
                        $jSON['content'] .= "</label>";

                        $jSON['content'] .= "<label class='freight__term'>";
                        $jSON['content'] .= "<span class='freight__legend'>Até</span>";
                        $jSON['content'] .= "<input class='freight__days' type='text' name='days' value='{$days}' placeholder='{$days}' autocomplete='off'>";
                        $jSON['content'] .= "<span class='freight__legend'>dias</span>";
                        $jSON['content'] .= "</label>";

                        $jSON['content'] .= "<div class='freight__action'>";
                        $jSON['content'] .= "<button type='submit' class='freight__send j_freight_send'>";
                        $jSON['content'] .= "<i class='icon-share icon-notext'></i>";
                        $jSON['content'] .= "</button>";

                        $jSON['content'] .= "<label class='icon-blocked icon-notext freight__status j_status {$blocked}'>";
                        $jSON['content'] .= "<input class='cities__check' type='checkbox' name='status' {$checked}>";
                        $jSON['content'] .= "</label>";
                        $jSON['content'] .= "</div>";

                        $jSON['content'] .= "<input class='freight__uf' type='text' name='uf' value='{$DISTRICT['uf']}'>";
                        $jSON['content'] .= '<input class="freight__name" type="text" value="' . $DISTRICT['district'] . ' - ' . $DISTRICT['city'] . '">';
                        $jSON['content'] .= "</form>";
                    endforeach;
                endif;
            endif;
            break;
    endswitch;

    //RETORNA O CALLBACK
    if ($jSON):
        echo json_encode($jSON);
    else:
        $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPSS:</b> Desculpe. Mas uma ação do sistema não respondeu corretamente. Ao persistir, contate o desenvolvedor!',
            E_USER_ERROR);
        echo json_encode($jSON);
    endif;
else:
    //ACESSO DIRETO
    die('<br><br><br><center><h1>Acesso Restrito!</h1></center>');
endif;
