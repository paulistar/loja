<?php
$AdminLevel = LEVEL_WC_PRODUCTS;
if (!APP_PRODUCTS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

// AUTO INSTANCE OBJECT CREATE
if (empty($Create)):
    $Create = new Create;
endif;

$PdtId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($PdtId):
    $Read->ExeRead(DB_PDT, "WHERE pdt_id = :id", "id={$PdtId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um produto que não existe ou que foi removido recentemente!";
        header('Location: dashboard.php?wc=products/home');
        exit;
    endif;
else:
    $Read->FullRead("SELECT count(pdt_id) as Total FROM " . DB_PDT . " WHERE pdt_status = :st", "st=1");
    if (E_PDT_LIMIT && $Read->getResult()[0]['Total'] >= E_PDT_LIMIT):
        $_SESSION['trigger_controll'] = "<b>LIMITE ATINGIDO:</b>, Olá {$Admin['user_name']}, o limite de produtos para sua loja é " . E_PDT_LIMIT . ". Esse limite foi atingido!<p>Para cadastrar mais produtos entre em contato via " . AGENCY_EMAIL . " e solicite alteração de plano!</p><p><b>Atenciosamente " . AGENCY_NAME . "!</b></p>";
        header('Location: dashboard.php?wc=products/home');
    else:
        $PdtCreate = ['pdt_created' => date('Y-m-d H:i:s'), 'pdt_status' => 0, 'pdt_inventory' => 0, 'pdt_delivered' => 0];
        $Create->ExeCreate(DB_PDT, $PdtCreate);
        header('Location: dashboard.php?wc=products/create&id=' . $Create->getResult());
    endif;
endif;

$Search = filter_input_array(INPUT_POST);
if ($Search && $Search['s']):
    $S = urlencode($Search['s']);
    header("Location: dashboard.php?wc=posts/search&s={$S}");
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-new-tab"><?= $pdt_title ? $pdt_title : 'Novo Produto'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=products/home">Produtos</a>
            <span class="crumb">/</span>
            Gerenciar Produto
        </p>
    </div>

    <div class="dashboard_header_search">
        <a id="<?= $PdtId; ?>" title="Criar Variação Deste Produto" href="dashboard.php?wc=products/reply&id=<?= ($pdt_parent ? $pdt_parent : $PdtId); ?>" class="j_pdt_reply btn btn_blue icon-copy">Criar Variação!</a>
        <a target="_blank" title="Ver no site" href="<?= BASE; ?>/produto/<?= $pdt_name; ?>" class="wc_view btn btn_green icon-eye">Ver no Site!</a>
    </div>
</header>

<div class="workcontrol_imageupload none" id="post_control">
    <div class="workcontrol_imageupload_content">
        <form name="workcontrol_post_upload" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Products"/>
            <input type="hidden" name="callback_action" value="sendimage"/>
            <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>
            <div class="upload_progress none" style="padding: 5px; background: #00B594; color: #fff; width: 0%; text-align: center; max-width: 100%;">0%</div>
            <div style="overflow: auto; max-height: 300px;">
                <img class="image image_default" alt="Nova Imagem" title="Nova Imagem" src="../tim.php?src=admin/_img/no_image.jpg&w=<?= IMAGE_W; ?>&h=<?= IMAGE_H; ?>" default="../tim.php?src=admin/_img/no_image.jpg&w=<?= IMAGE_W; ?>&h=<?= IMAGE_H; ?>"/>
            </div>
            <div class="workcontrol_imageupload_actions">
                <input class="wc_loadimage" type="file" name="image" required/>
                <span class="workcontrol_imageupload_close icon-cancel-circle btn btn_red" id="post_control" style="margin-right: 8px;">Fechar</span>
                <button class="btn btn_green icon-image">Enviar e Inserir!</button>
                <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
            </div>
            <div class="clear"></div>
        </form>
    </div>
</div>

<!----------------------------------
######## CUSTOM BY ALISSON #########
----------------------------------->
<div class="workcontrol_pdt_stock">
    <div class="attr_box">
        <div class="attr_modal">
            <div class="attr_tab">
                <?php
                $Read->FullRead("SELECT attr_color_id FROM " . DB_PDT_ATTR_COLORS);
                $readAttrColor = (!empty($Read->getResult()) ? true : false);

                $Read->FullRead("SELECT attr_size_id FROM " . DB_PDT_ATTR_SIZES);
                $readAttrSize = (!empty($Read->getResult()) ? true : false);

                $Read->FullRead("SELECT attr_print_id FROM " . DB_PDT_ATTR_PRINTS);
                $readAttrPrint = (!empty($Read->getResult()) ? true : false);

                $Read->FullRead("SELECT pdt_id FROM " . DB_PDT_STOCK . " WHERE pdt_id = :pdt AND color_id IS NOT NULL", "pdt={$PdtId}");
                $readPdtColor = (!empty($Read->getResult()) ? true : false);

                if ($readAttrColor && $readAttrSize):
                    echo "<a class='wc_tab wc_active' href='#colors-sizes'><span class='icon-paint-format'>Cores</span><span class='icon-clubs'>Tamanhos</span></a>";
                endif;

                if ($readAttrPrint && $readAttrSize):
                    echo "<a class='wc_tab' href='#prints-sizes'><span class='icon-image'>Estampas</span><span class='icon-clubs'>Tamanhos</span></a>";
                endif;

                if ($readAttrColor):
                    echo "<a class='wc_tab' href='#colors'><span class='icon-paint-format'>Cores</span></a>";
                endif;

                if ($readAttrSize):
                    echo "<a class='wc_tab' href='#sizes'><span class='icon-clubs'>Tamanhos</span></a>";
                endif;

                if ($readAttrPrint):
                    echo "<a class='wc_tab' href='#prints'><span class='icon-image'>Estampas</span></a>";
                endif;

                echo "<a class='wc_tab' href='#stock'><span class='icon-plus'>Estoque</span></a>";
                ?>
            </div>

            <?php if ($readAttrColor && $readAttrSize): ?>
                <div class="wc_tab_target <?= ($readAttrColor && $readAttrSize ? 'wc_active' : 'ds_none'); ?>" id="colors-sizes">
                    <form class="auto_save" name="form_type_group" action="" method="post">
                        <input type="hidden" name="callback" value="Products"/>
                        <input type="hidden" name="callback_action" value="form_type_group"/>
                        <input type="hidden" name="callback_form" value="form_all_attr"/>
                        <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>

                        <label class="box box50">
                            <span>
                                <select name="group_type_color">
                                    <option value="">Todas as Cores</option>
                                    <?php
                                    $Read->FullRead("SELECT group_id, group_title FROM " . DB_PDT_GROUPS_ATTR . " WHERE group_type = :type ORDER BY group_order ASC, group_created DESC", "type=color");
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $COLOR):
                                            extract($COLOR);
                                            ?>
                                            <option value="<?= $group_id; ?>"><?= $group_title; ?></option>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </span>
                        </label><label class="box box50">
                            <span>
                                <select name="group_type_size">
                                    <option value="">Todos os Tamanhos</option>
                                    <?php
                                    $Read->FullRead("SELECT group_id, group_title FROM " . DB_PDT_GROUPS_ATTR . " WHERE group_type = :type ORDER BY group_order ASC, group_created DESC", "type=size");
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $SIZE):
                                            extract($SIZE);
                                            ?>
                                            <option value="<?= $group_id; ?>"><?= $group_title; ?></option>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </span>
                        </label>
                    </form>

                    <form name="all_attr" action="" method="post">
                        <input type="hidden" name="callback" value="Products"/>
                        <input type="hidden" name="callback_action" value="stock_manager"/>
                        <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>

                        <div class="inputs form_all_attr" style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                            <?php
                            //Ler atributos de cores da tabela ws_products_attributes_colors
                            $Read->FullRead("SELECT attr_color_id, attr_color_code, attr_color_title FROM " . DB_PDT_ATTR_COLORS);
                            if ($Read->getResult()):
                                foreach ($Read->getResult() as $COLOR):
                                    echo "<div style='flex-basis: calc(100% / 3 - 10px);'>";

                                    if (strpos($COLOR['attr_color_code'], ',')):
                                        $arrColors = explode(',', $COLOR['attr_color_code']);
                                        $bgColor = (count($arrColors) == 2 ? "background-image: -webkit-linear-gradient(45deg, {$arrColors[0]}  50%, {$arrColors[1]} 50%); background-image: -moz-linear-gradient(45deg, {$arrColors[0]}  50%, {$arrColors[1]} 50%); background-image: -ms-linear-gradient(45deg, {$arrColors[0]}  50%, {$arrColors[1]} 50%); background-image: -o-linear-gradient(45deg, {$arrColors[0]}  50%, {$arrColors[1]} 50%); background-image: linear-gradient(45deg, {$arrColors[0]}  50%, {$arrColors[1]} 50%);" : (count($arrColors) == 3 ? "background-image: -webkit-linear-gradient(45deg, {$arrColors[0]}  33%, {$arrColors[1]} 33%, {$arrColors[1]} 67%, {$arrColors[2]} 67%, {$arrColors[2]}); background-image: -moz-linear-gradient(45deg, {$arrColors[0]}  33%, {$arrColors[1]} 33%, {$arrColors[1]} 67%, {$arrColors[2]} 67%, {$arrColors[2]}); background-image: -ms-linear-gradient(45deg, {$arrColors[0]}  33%, {$arrColors[1]} 33%, {$arrColors[1]} 67%, {$arrColors[2]} 67%, {$arrColors[2]}); background-image: -o-linear-gradient(45deg, {$arrColors[0]}  33%, {$arrColors[1]} 33%, {$arrColors[1]} 67%, {$arrColors[2]} 67%, {$arrColors[2]}); background-image: linear-gradient(45deg, {$arrColors[0]}  33%, {$arrColors[1]} 33%, {$arrColors[1]} 67%, {$arrColors[2]} 67%, {$arrColors[2]});" : "background-image: -webkit-linear-gradient(45deg, {$arrColors[0]}  25%, {$arrColors[1]} 25%, {$arrColors[1]} 50%, {$arrColors[2]} 50%, {$arrColors[2]} 75%, {$arrColors[3]} 75%, {$arrColors[3]}); background-image: -moz-linear-gradient(45deg, {$arrColors[0]}  25%, {$arrColors[1]} 25%, {$arrColors[1]} 50%, {$arrColors[2]} 50%, {$arrColors[2]} 75%, {$arrColors[3]} 75%, {$arrColors[3]}); background-image: -ms-linear-gradient(45deg, {$arrColors[0]}  25%, {$arrColors[1]} 25%, {$arrColors[1]} 50%, {$arrColors[2]} 50%, {$arrColors[2]} 75%, {$arrColors[3]} 75%, {$arrColors[3]}); background-image: -o-linear-gradient(45deg, {$arrColors[0]}  25%, {$arrColors[1]} 25%, {$arrColors[1]} 50%, {$arrColors[2]} 50%, {$arrColors[2]} 75%, {$arrColors[3]} 75%, {$arrColors[3]}); background-image: linear-gradient(45deg, {$arrColors[0]}  25%, {$arrColors[1]} 25%, {$arrColors[1]} 50%, {$arrColors[2]} 50%, {$arrColors[2]} 75%, {$arrColors[3]} 75%, {$arrColors[3]});"));
                                    else:
                                        $bgColor = "background-color: {$COLOR['attr_color_code']};";
                                    endif;

                                    //Ler atributos de tamanhos da tabela ws_products_attributes_sizes
                                    $Read->FullRead("SELECT attr_size_id, attr_size_code, attr_size_title FROM " . DB_PDT_ATTR_SIZES);
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $SIZE):
                                            //Ler estoque do produto $PdtId na variação $SIZE['attr_size_id'] $COLOR['attr_color_id'] da tabela ws_products_stock
                                            $Read->FullRead("SELECT stock_inventory FROM " . DB_PDT_STOCK . " WHERE pdt_id = :pdt AND size_id = :size AND color_id = :color", "pdt={$PdtId}&size={$SIZE['attr_size_id']}&color={$COLOR['attr_color_id']}");
                                            if ($Read->getResult()):
                                                echo "<label><span style='{$bgColor}' title='{$COLOR['attr_color_title']}'><input type='hidden' name='color_id[]' value='{$COLOR['attr_color_id']}'/></span><span title='{$SIZE['attr_size_title']}'>{$SIZE['attr_size_code']}<input type='hidden' name='size_id[]' value='{$SIZE['attr_size_id']}'/></span><input name='stock_inventory[]' type='number' min='0' value='{$Read->getResult()[0]['stock_inventory']}'></label>";
                                            else:
                                                echo "<label><span style='{$bgColor}' title='{$COLOR['attr_color_title']}'><input type='hidden' name='color_id[]' value='{$COLOR['attr_color_id']}'/></span><span title='{$SIZE['attr_size_title']}'>{$SIZE['attr_size_code']}<input type='hidden' name='size_id[]' value='{$SIZE['attr_size_id']}'/></span><input name='stock_inventory[]' type='number' min='0' value='0'></label>";
                                            endif;
                                        endforeach;
                                    endif;

                                    echo "</div>";
                                endforeach;
                            endif;
                            ?>
                        </div>

                        <button class="btn btn_green icon-ungroup">Atualizar Estoque!</button>
                        <img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                        <div class="clear"></div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($readAttrPrint && $readAttrSize): ?>
                <div class="wc_tab_target <?= (!$readAttrColor && $readAttrSize && $readAttrPrint ? 'wc_active' : 'ds_none'); ?>" id="prints-sizes">
                    <form class="auto_save" name="form_type_group" action="" method="post">
                        <input type="hidden" name="callback" value="Products"/>
                        <input type="hidden" name="callback_action" value="form_type_group"/>
                        <input type="hidden" name="callback_form" value="form_attr_print"/>
                        <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>

                        <label class="box box50">
                            <span>
                                <select name="group_type_print">
                                    <option value="">Todas as Estampas</option>
                                    <?php
                                    $Read->FullRead("SELECT group_id, group_title FROM " . DB_PDT_GROUPS_ATTR . " WHERE group_type = :type ORDER BY group_order ASC, group_created DESC", "type=print");
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $PRINT):
                                            extract($PRINT);
                                            ?>
                                            <option value="<?= $group_id; ?>"><?= $group_title; ?></option>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </span>
                        </label><label class="box box50">
                            <span>
                                <select name="group_type_size">
                                    <option value="">Todos os Tamanhos</option>
                                    <?php
                                    $Read->FullRead("SELECT group_id, group_title FROM " . DB_PDT_GROUPS_ATTR . " WHERE group_type = :type ORDER BY group_order ASC, group_created DESC", "type=size");
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $SIZE):
                                            extract($SIZE);
                                            ?>
                                            <option value="<?= $group_id; ?>"><?= $group_title; ?></option>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </span>
                        </label>
                    </form>

                    <form name="attr_print" action="" method="post">
                        <input type="hidden" name="callback" value="Products"/>
                        <input type="hidden" name="callback_action" value="stock_manager"/>
                        <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>

                        <div class="inputs form_attr_print" style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                            <?php
                            //Ler atributos de estampas da tabela ws_products_attributes_prints
                            $Read->FullRead("SELECT attr_print_id, attr_print_code, attr_print_title FROM " . DB_PDT_ATTR_PRINTS);
                            if ($Read->getResult()):
                                foreach ($Read->getResult() as $PRINT):
                                    echo "<div style='flex-basis: calc(100% / 3 - 10px);'>";

                                    $bgPrint = "background: url(" . BASE . "/uploads/{$PRINT['attr_print_code']}) center / cover no-repeat;";

                                    //Ler atributos de tamanhos da tabela ws_products_attributes_sizes
                                    $Read->FullRead("SELECT attr_size_id, attr_size_code, attr_size_title FROM " . DB_PDT_ATTR_SIZES);
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $SIZE):
                                            $SIZE['attr_size_code'] = trim($SIZE['attr_size_code']);
                                            //Ler estoque do produto $PdtId na variação $SIZE['attr_size_id'] $PRINT['attr_print_id'] da tabela ws_products_stock
                                            $Read->FullRead("SELECT stock_inventory FROM " . DB_PDT_STOCK . " WHERE pdt_id = :pdt AND size_id = :size AND print_id = :print", "pdt={$PdtId}&size={$SIZE['attr_size_id']}&print={$PRINT['attr_print_id']}");
                                            if ($Read->getResult()):
                                                echo "<label><span style='{$bgPrint}' title='{$PRINT['attr_print_title']}'><input type='hidden' name='print_id[]' value='{$PRINT['attr_print_id']}'/></span><span title='{$SIZE['attr_size_title']}'>{$SIZE['attr_size_code']}<input type='hidden' name='size_id[]' value='{$SIZE['attr_size_id']}'/></span><input name='stock_inventory[]' type='number' min='0' value='{$Read->getResult()[0]['stock_inventory']}'></label>";
                                            else:
                                                echo "<label><span style='{$bgPrint}' title='{$PRINT['attr_print_title']}'><input type='hidden' name='print_id[]' value='{$PRINT['attr_print_id']}'/></span><span title='{$SIZE['attr_size_title']}'>{$SIZE['attr_size_code']}<input type='hidden' name='size_id[]' value='{$SIZE['attr_size_id']}'/></span><input name='stock_inventory[]' type='number' min='0' value='0'></label>";
                                            endif;
                                        endforeach;
                                    endif;

                                    echo "</div>";
                                endforeach;
                            endif;
                            ?>
                        </div>

                        <button class="btn btn_green icon-ungroup">Atualizar Estoque!</button>
                        <img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                        <div class="clear"></div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($readAttrColor): ?>
                <div class="wc_tab_target <?= ($readAttrColor && !$readAttrSize && !$readAttrPrint ? 'wc_active' : 'ds_none'); ?>" id="colors">
                    <form class="auto_save" name="form_type_group" action="" method="post">
                        <input type="hidden" name="callback" value="Products"/>
                        <input type="hidden" name="callback_action" value="form_type_group"/>
                        <input type="hidden" name="callback_form" value="form_attr_color"/>
                        <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>

                        <label class="box box50">
                            <span>
                                <select name="group_type_color">
                                    <option value="">Todas as Cores</option>
                                    <?php
                                    $Read->FullRead("SELECT group_id, group_title FROM " . DB_PDT_GROUPS_ATTR . " WHERE group_type = :type ORDER BY group_order ASC, group_created DESC", "type=color");
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $COLOR):
                                            extract($COLOR);
                                            ?>
                                            <option value="<?= $group_id; ?>"><?= $group_title; ?></option>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </span>
                        </label>
                    </form>

                    <form name="attr_color" action="" method="post">
                        <input type="hidden" name="callback" value="Products"/>
                        <input type="hidden" name="callback_action" value="stock_manager"/>
                        <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>

                        <div class="inputs form_attr_color" style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                            <?php
                            //Ler atributos de cores da tabela ws_products_attributes_colors
                            $Read->FullRead("SELECT attr_color_id, attr_color_code, attr_color_title FROM " . DB_PDT_ATTR_COLORS);
                            if ($Read->getResult()):
                                foreach ($Read->getResult() as $COLOR):
                                    echo "<div style='flex-basis: calc(100% / 3 - 10px);'>";

                                    if (strpos($COLOR['attr_color_code'], ',')):
                                        $arrColors = explode(',', $COLOR['attr_color_code']);
                                        $bgColor = (count($arrColors) == 2 ? "background-image: -webkit-linear-gradient(45deg, {$arrColors[0]}  50%, {$arrColors[1]} 50%); background-image: -moz-linear-gradient(45deg, {$arrColors[0]}  50%, {$arrColors[1]} 50%); background-image: -ms-linear-gradient(45deg, {$arrColors[0]}  50%, {$arrColors[1]} 50%); background-image: -o-linear-gradient(45deg, {$arrColors[0]}  50%, {$arrColors[1]} 50%); background-image: linear-gradient(45deg, {$arrColors[0]}  50%, {$arrColors[1]} 50%);" : (count($arrColors) == 3 ? "background-image: -webkit-linear-gradient(45deg, {$arrColors[0]}  33%, {$arrColors[1]} 33%, {$arrColors[1]} 67%, {$arrColors[2]} 67%, {$arrColors[2]}); background-image: -moz-linear-gradient(45deg, {$arrColors[0]}  33%, {$arrColors[1]} 33%, {$arrColors[1]} 67%, {$arrColors[2]} 67%, {$arrColors[2]}); background-image: -ms-linear-gradient(45deg, {$arrColors[0]}  33%, {$arrColors[1]} 33%, {$arrColors[1]} 67%, {$arrColors[2]} 67%, {$arrColors[2]}); background-image: -o-linear-gradient(45deg, {$arrColors[0]}  33%, {$arrColors[1]} 33%, {$arrColors[1]} 67%, {$arrColors[2]} 67%, {$arrColors[2]}); background-image: linear-gradient(45deg, {$arrColors[0]}  33%, {$arrColors[1]} 33%, {$arrColors[1]} 67%, {$arrColors[2]} 67%, {$arrColors[2]});" : "background-image: -webkit-linear-gradient(45deg, {$arrColors[0]}  25%, {$arrColors[1]} 25%, {$arrColors[1]} 50%, {$arrColors[2]} 50%, {$arrColors[2]} 75%, {$arrColors[3]} 75%, {$arrColors[3]}); background-image: -moz-linear-gradient(45deg, {$arrColors[0]}  25%, {$arrColors[1]} 25%, {$arrColors[1]} 50%, {$arrColors[2]} 50%, {$arrColors[2]} 75%, {$arrColors[3]} 75%, {$arrColors[3]}); background-image: -ms-linear-gradient(45deg, {$arrColors[0]}  25%, {$arrColors[1]} 25%, {$arrColors[1]} 50%, {$arrColors[2]} 50%, {$arrColors[2]} 75%, {$arrColors[3]} 75%, {$arrColors[3]}); background-image: -o-linear-gradient(45deg, {$arrColors[0]}  25%, {$arrColors[1]} 25%, {$arrColors[1]} 50%, {$arrColors[2]} 50%, {$arrColors[2]} 75%, {$arrColors[3]} 75%, {$arrColors[3]}); background-image: linear-gradient(45deg, {$arrColors[0]}  25%, {$arrColors[1]} 25%, {$arrColors[1]} 50%, {$arrColors[2]} 50%, {$arrColors[2]} 75%, {$arrColors[3]} 75%, {$arrColors[3]});"));
                                    else:
                                        $bgColor = "background-color: {$COLOR['attr_color_code']};";
                                    endif;

                                    //Ler estoque do produto $PdtId quando a coluna size_id da tabela ws_products_stock for null
                                    $Read->FullRead("SELECT stock_inventory FROM " . DB_PDT_STOCK . " WHERE pdt_id = :pdt AND size_id IS NULL AND color_id = :color", "pdt={$PdtId}&color={$COLOR['attr_color_id']}");
                                    if ($Read->getResult()):
                                        echo "<label><span style='{$bgColor}' title='{$COLOR['attr_color_title']}'><input type='hidden' name='color_id[]' value='{$COLOR['attr_color_id']}'/></span><span title='default'>default</span><input name='stock_inventory[]' type='number' min='0' value='{$Read->getResult()[0]['stock_inventory']}'></label>";
                                    else:
                                        echo "<label><span style='{$bgColor}' title='{$COLOR['attr_color_title']}'><input type='hidden' name='color_id[]' value='{$COLOR['attr_color_id']}'/></span><span title='default'>default</span><input name='stock_inventory[]' type='number' min='0' value='0'></label>";
                                    endif;

                                    echo "</div>";
                                endforeach;
                            endif;
                            ?>
                        </div>

                        <button class="btn btn_green icon-ungroup">Atualizar Estoque!</button>
                        <img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                        <div class="clear"></div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($readAttrSize): ?>
                <div class="wc_tab_target <?= (!$readAttrColor && $readAttrSize && !$readAttrPrint ? 'wc_active' : 'ds_none'); ?>" id="sizes">
                    <form class="auto_save" name="form_type_group" action="" method="post">
                        <input type="hidden" name="callback" value="Products"/>
                        <input type="hidden" name="callback_action" value="form_type_group"/>
                        <input type="hidden" name="callback_form" value="form_attr_size"/>
                        <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>

                        <label class="box box50">
                            <span>
                                <select name="group_type_size">
                                    <option value="">Todos os Tamanhos</option>
                                    <?php
                                    $Read->FullRead("SELECT group_id, group_title FROM " . DB_PDT_GROUPS_ATTR . " WHERE group_type = :type ORDER BY group_order ASC, group_created DESC", "type=size");
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $SIZE):
                                            extract($SIZE);
                                            ?>
                                            <option value="<?= $group_id; ?>"><?= $group_title; ?></option>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </span>
                        </label>
                    </form>

                    <form name="attr_size" action="" method="post">
                        <input type="hidden" name="callback" value="Products"/>
                        <input type="hidden" name="callback_action" value="stock_manager"/>
                        <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>

                        <div class="inputs form_attr_size" style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                            <?php
                            //Ler atributos de tamanhos da tabela ws_products_attributes_sizes
                            $Read->FullRead("SELECT attr_size_id, attr_size_code, attr_size_title FROM " . DB_PDT_ATTR_SIZES);
                            if ($Read->getResult()):
                                foreach ($Read->getResult() as $SIZE):
                                    echo "<div style='flex-basis: calc(100% / 3 - 10px);'>";

                                    //Ler estoque do produto $PdtId na variação $SIZE['attr_size_id'] da tabela ws_products_stock
                                    $Read->FullRead("SELECT stock_inventory, stock_sold FROM " . DB_PDT_STOCK . " WHERE pdt_id = :pdt AND size_id = :size", "pdt={$PdtId}&size={$SIZE['attr_size_id']}");
                                    if ($Read->getResult() && !$readPdtColor):
                                        echo "<label><span title='{$SIZE['attr_size_title']}'>{$SIZE['attr_size_code']}<input type='hidden' name='size_id[]' value='{$SIZE['attr_size_id']}'/></span><input name='stock_inventory[]' type='number' min='0' value='{$Read->getResult()[0]['stock_inventory']}'><span class='cart'><b class='icon-cart'>" . str_pad($Read->getResult()[0]['stock_sold'], 2, 0, 0) . "</b></span></label>";
                                    else:
                                        echo "<label><span title='{$SIZE['attr_size_title']}'>{$SIZE['attr_size_code']}<input type='hidden' name='size_id[]' value='{$SIZE['attr_size_id']}'/></span><input name='stock_inventory[]' type='number' min='0' value='0'><span class='cart'><b class='icon-cart'>00</b></span></label>";
                                    endif;

                                    echo "</div>";
                                endforeach;
                            endif;
                            ?>
                        </div>

                        <button class="btn btn_green icon-ungroup">Atualizar Estoque!</button>
                        <img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                        <div class="clear"></div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($readAttrPrint): ?>
                <div class="wc_tab_target <?= (!$readAttrColor && !$readAttrSize && $readAttrPrint ? 'wc_active' : 'ds_none'); ?>" id="prints">
                    <form class="auto_save" name="form_type_group" action="" method="post">
                        <input type="hidden" name="callback" value="Products"/>
                        <input type="hidden" name="callback_action" value="form_type_group"/>
                        <input type="hidden" name="callback_form" value="form_attr_only_print"/>
                        <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>

                        <label class="box box50">
                            <span>
                                <select name="group_type_print">
                                    <option value="">Todas as Estampas</option>
                                    <?php
                                    $Read->FullRead("SELECT group_id, group_title FROM " . DB_PDT_GROUPS_ATTR . " WHERE group_type = :type ORDER BY group_order ASC, group_created DESC", "type=print");
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $PRINT):
                                            extract($PRINT);
                                            ?>
                                            <option value="<?= $group_id; ?>"><?= $group_title; ?></option>
                                        <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </span>
                        </label>
                    </form>

                    <form name="attr_print" action="" method="post">
                        <input type="hidden" name="callback" value="Products"/>
                        <input type="hidden" name="callback_action" value="stock_manager"/>
                        <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>

                        <div class="inputs form_attr_only_print" style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                            <?php
                            //Ler atributos de estampas da tabela ws_products_attributes_prints
                            $Read->FullRead("SELECT attr_print_id, attr_print_code, attr_print_title FROM " . DB_PDT_ATTR_PRINTS);
                            if ($Read->getResult()):
                                foreach ($Read->getResult() as $PRINT):
                                    echo "<div style='flex-basis: calc(100% / 3 - 10px);'>";

                                    $bgPrint = "background: url(" . BASE . "/uploads/{$PRINT['attr_print_code']}) center / cover no-repeat;";

                                    //Ler estoque do produto $PdtId quando a coluna size_id da tabela ws_products_stock for null
                                    $Read->FullRead("SELECT stock_inventory FROM " . DB_PDT_STOCK . " WHERE pdt_id = :pdt AND size_id IS NULL AND print_id = :print", "pdt={$PdtId}&print={$PRINT['attr_print_id']}");
                                    if ($Read->getResult()):
                                        echo "<label><span style='{$bgPrint}' title='{$PRINT['attr_print_title']}'><input type='hidden' name='print_id[]' value='{$PRINT['attr_print_id']}'/></span><span title='default'>default</span><input name='stock_inventory[]' type='number' min='0' value='{$Read->getResult()[0]['stock_inventory']}'></label>";
                                    else:
                                        echo "<label><span style='{$bgPrint}' title='{$PRINT['attr_print_title']}'><input type='hidden' name='print_id[]' value='{$PRINT['attr_print_id']}'/></span><span title='default'>default</span><input name='stock_inventory[]' type='number' min='0' value='0'></label>";
                                    endif;

                                    echo "</div>";
                                endforeach;
                            endif;
                            ?>
                        </div>

                        <button class="btn btn_green icon-ungroup">Atualizar Estoque!</button>
                        <img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                        <div class="clear"></div>
                    </form>
                </div>
            <?php endif; ?>

            <form class="wc_tab_target <?= (!$readAttrColor && !$readAttrSize && !$readAttrPrint ? 'wc_active' : 'ds_none'); ?>" name="all_stock" action="" method="post" id="stock">
                <input type="hidden" name="callback" value="Products"/>
                <input type="hidden" name="callback_action" value="stock_manager"/>
                <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>

                <div class="inputs">
                    <?php
                    //Ler estoque do produto $PdtId quando as colunas size_id, color_id e print_id da tabela ws_products_stock forem null
                    $Read->FullRead("SELECT stock_inventory, stock_sold FROM " . DB_PDT_STOCK . " WHERE pdt_id = :pdt AND size_id IS NULL", "pdt={$PdtId}");
                    if ($Read->getResult() && !$readPdtColor):
                        echo "<label><span title='default'>default</span><input name='stock_inventory[]' type='number' min='0' value='{$Read->getResult()[0]['stock_inventory']}'><span class='cart'><b class='icon-cart'>" . str_pad($Read->getResult()[0]['stock_sold'], 2, 0, 0) . "</b></span></label>";
                    else:
                        echo "<label><span title='default'>default</span><input name='stock_inventory[]' type='number' min='0' value='0'><span class='cart'><b class='icon-cart'>00</b></span></label>";
                    endif;
                    ?>
                </div>

                <button class="btn btn_green icon-ungroup">Atualizar Estoque!</button>
                <img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                <div class="clear"></div>
            </form>

            <div class="workcontrol_pdt_stock_close">X</div>
        </div>
    </div>
</div>

<!----------------------------------
######## CUSTOM BY ALISSON #########
----------------------------------->
<div class="combo">
    <div class="combo_box">
        <form class="combo_box_filter">
            <div class="combo_box_filter_close">
                <button class="j_combo_close" type="button">
                    <i class="icon-cross icon-notext"></i>
                </button>
            </div>

            <div class="combo_box_filter_search">
                <input type="search" name="search" placeholder="Pesquisar"/>
                <button type="button">
                    <i class="icon-search icon-notext"></i>
                </button>
            </div>

            <div class="combo_box_filter_discount">
                <input type="text" name="discount" placeholder="10"/>
                <button type="button">
                    <i class="icon-coin-dollar icon-notext"></i>
                </button>
            </div>

            <div class="combo_box_filter_selected">
                <button type="button">
                    <i class="icon-checkmark icon-notext"></i>
                </button>
            </div>

            <div class="combo_box_filter_pagination">
                <button type="button" data-offset="0">
                    <i class="icon-arrow-left icon-notext"></i>
                </button>

                <button type="button" data-offset="0">
                    <i class="icon-radio-checked icon-notext"></i>
                </button>

                <button type="button" data-offset="0">
                    <i class="icon-arrow-right icon-notext"></i>
                </button>
            </div>

            <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>
        </form>

        <div class="combo_box_content">
            <?php
            $Read->FullRead("SELECT combo_list FROM " . DB_PDT_COMBO . " WHERE pdt_id = :id", "id={$PdtId}");
            $comboList = ($Read->getResult() && $Read->getResult()[0]['combo_list'] ? explode(',', $Read->getResult()[0]['combo_list']) : false);
            $comboCount = ($comboList ? count($comboList) : 0);

            $Read->FullRead("SELECT pdt_id, pdt_title, pdt_name, pdt_cover, pdt_price, pdt_offer_price, pdt_offer_start, pdt_offer_end FROM " . DB_PDT . " WHERE pdt_id != :id AND pdt_status = :status AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) LIMIT :limit OFFSET :offset", "id={$PdtId}&status=1&limit=16&offset=0");
            if ($Read->getResult()):
                foreach ($Read->getResult() as $PDT):
                    $active = ($comboList && in_array($PDT['pdt_id'], $comboList) ? ' active' : '');

                    if ($PDT['pdt_offer_price'] && $PDT['pdt_offer_start'] <= date('Y-m-d H:i:s') && $PDT['pdt_offer_end'] >= date('Y-m-d H:i:s')):
                        $PdtPrice = $PDT['pdt_offer_price'];
                        $discount = (int) ((($PDT['pdt_price'] - $PDT['pdt_offer_price']) * 100) / $PDT['pdt_price']);
                    else:
                        $PdtPrice = $PDT['pdt_price'];
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
                    ?><article class="combo_box_item wc_normalize_height" data-pdt-combo-id="<?= $PDT['pdt_id']; ?>">
                        <div class="combo_box_item_image">
                            <img src="<?= BASE; ?>/uploads/<?= $PDT['pdt_cover']; ?>" alt="<?= $PDT['pdt_title']; ?>" title="<?= $PDT['pdt_title']; ?>"/>

                            <?php if ($discount): ?>
                                <div class="combo_box_item_image_discount" style="width: <?= $discount; ?>%;">
                                    <?= $discount; ?>% off
                                </div>
                            <?php endif; ?>

                            <div class="combo_box_item_image_selected<?= $active; ?>">
                                <div class="combo_box_item_image_selected_box">
                                    <i class="icon-checkmark icon-notext"></i>
                                </div>
                            </div>
                        </div>

                        <div class="combo_box_item_title">
                            <?= $PDT['pdt_title']; ?>
                        </div>

                        <div class="combo_box_item_price">
                            <?php if ($discount): ?>
                                <span class="old_price">de: R$ <?= number_format($PDT['pdt_price'], 2, ',', '.'); ?></span> por: R$ <?= number_format($PdtPrice, 2, ',', '.'); ?>
                            <?php else: ?>
                                por: R$ <?= number_format($PdtPrice, 2, ',', '.'); ?>
                            <?php endif; ?>
                            <span class="installment"><?= $NumSplit; ?>x de R$ <?= $SplitPrice; ?></span>
                        </div>
                    </article><?php
                endforeach;
            endif;
            ?>
        </div>
    </div>
</div>

<div class="dashboard_content single_pdt_form">
    <form class="auto_save" name="manage_pdt" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="callback" value="Products"/>
        <input type="hidden" name="callback_action" value="manager"/>
        <input type="hidden" name="pdt_id" value="<?= $PdtId; ?>"/>

        <div class="box box70">
            <div class="box_content">
                <label class="label">
                    <span class="legend">Produto:</span>
                    <input style="font-size: 1.4em;" type="text" name="pdt_title" value="<?= $pdt_title; ?>" placeholder="Nome do Produto:" required/>
                </label>

                <label class="label">
                    <span class="legend">Breve Descrição:</span>
                    <textarea style="font-size: 1.2em;" name="pdt_subtitle" rows="3" required><?= $pdt_subtitle; ?></textarea>
                </label>

                <?php if (APP_LINK_PRODUCTS): ?>
                    <label class="label">
                        <span class="legend">Link Alternativo (Opcional):</span>
                        <input type="text" name="pdt_name" value="<?= $pdt_name; ?>" placeholder="Link do Produto:"/>
                    </label>
                <?php endif; ?>

                <label class="label">
                    <span class="legend">Capa (JPG <?= THUMB_W; ?>x<?= THUMB_H; ?>px):</span>
                    <input type="file" class="wc_loadimage" name="pdt_cover"/>
                </label>

                <!----------------------------------
                ######## CUSTOM BY ALISSON #########
                ----------------------------------->
                <div class="label_50">
                    <label class="label">
                        <span class="legend">Código:</span>
                        <input type="text" name="pdt_code" value="<?= ($pdt_code ? $pdt_code : str_pad($pdt_id, 4, 0, STR_PAD_LEFT)); ?>"/>
                    </label>

                    <label class="label">
                        <span class="legend">Marca/Fabricante:</span>
                        <?php
                        $Read->ExeRead(DB_PDT_BRANDS, "ORDER BY brand_title ASC");
                        if (!$Read->getResult()):
                            echo Erro("<span class='icon-warning'>Cadastre algumas marcas ou fabricantes antes de começar!</span>", E_USER_WARNING);
                        else:
                            echo "<select name='pdt_brand' required>";
                            echo "<option value=''>Selecione um Fabricante</option>";
                            foreach ($Read->getResult() as $Brand):
                                echo "<option";
                                if ($pdt_brand == $Brand['brand_id']):
                                    echo " selected='selected'";
                                endif;
                                echo " value='{$Brand['brand_id']}'>{$Brand['brand_title']}</option>";
                            endforeach;

                            echo "</select>";
                        endif;
                        ?>
                    </label>
                </div>

                <div class="category_selection">
                    <span class="category_selection_legend">Categoria:</span>
                    <span class="category_selection_title j_open_category_selection">
                        <?php
                        if (empty($pdt_subcategory)):
                            echo 'Selecione a(s) Categoria(s)';
                        else:
                            $Read->FullRead("SELECT cat_title FROM " . DB_PDT_CATS . " WHERE FIND_IN_SET(cat_id, :category) ORDER BY cat_title ASC", "category={$pdt_subcategory}");
                            if ($Read->getResult()):
                                $cats = array();
                                foreach ($Read->getResult() as $PDT):
                                    $cats[] = $PDT['cat_title'];
                                endforeach;

                                echo implode(', ', $cats);
                            endif;
                        endif;
                        ?>
                    </span>

                    <div class="category_selection_content j_category_selection_content">
                        <?php
                        $Read->FullRead("SELECT cat_id, cat_parent, cat_title FROM " . DB_PDT_CATS . " WHERE cat_parent IS NULL ORDER BY cat_title ASC");

                        function loopCat() {
                            global $Read, $pdt_subcategory;
                            if ($Read->getResult()):
                                foreach ($Read->getResult() as $CAT):
                                    $Read->FullRead("SELECT cat_id, cat_parent, cat_title FROM " . DB_PDT_CATS . " WHERE cat_parent = :parent ORDER BY cat_title ASC", "parent={$CAT['cat_id']}");
                                    $checked = (!empty($pdt_subcategory) && in_array($CAT['cat_id'], explode(',', $pdt_subcategory)) ? ' checked="checked"' : '');

                                    echo "<li>";
                                    echo "<span>";
                                    echo "<i class='" . ($Read->getResult() ? "icon-plus icon-notext" : "icon-minus icon-notext") . "'></i>";
                                    echo "<input id='checkbox-category-{$CAT['cat_id']}' class='j_category_selection multiple' type='checkbox' name='pdt_subcategory[]' value='{$CAT['cat_id']}' data-title='{$CAT['cat_title']}'" . (empty($CAT['cat_parent']) ? ' disabled="disabled"' : '') . $checked . "/>";
                                    echo "<label" . (empty($CAT['cat_parent']) ? ' class="disabled"' : '') . " for='checkbox-category-{$CAT['cat_id']}'>{$CAT['cat_title']}</label>";
                                    echo "</span>";

                                    if ($Read->getResult()):
                                        echo "<ul>";
                                        loopCat();
                                        echo "</ul>";
                                    endif;
                                    echo "</li>";
                                endforeach;
                            endif;
                        }

                        echo "<ul>";
                        loopCat();
                        echo "</ul>";
                        ?>
                    </div>
                </div>

                <div class="label_33">
                    <label class="label">
                        <span class="legend">Tipo:</span>
                        <select name="pdt_type">
                            <option value="">Selecione o Tipo</option>
                            <option value="blouse"<?= ($pdt_type == 'blouse' ? ' selected="selected"' : ''); ?>>Blusa</option>
                            <option value="body"<?= ($pdt_type == 'body' ? ' selected="selected"' : ''); ?>>Body</option>
                            <option value="coat"<?= ($pdt_type == 'coat' ? ' selected="selected"' : ''); ?>>Casaco</option>
                            <option value="cropped"<?= ($pdt_type == 'cropped' ? ' selected="selected"' : ''); ?>>Cropped</option>
                            <option value="dress"<?= ($pdt_type == 'dress' ? ' selected="selected"' : ''); ?>>Vestido</option>
                            <option value="pants"<?= ($pdt_type == 'pants' ? ' selected="selected"' : ''); ?>>Calça</option>
                            <option value="shirt"<?= ($pdt_type == 'shirt' ? ' selected="selected"' : ''); ?>>Camisa</option>
                            <option value="short"<?= ($pdt_type == 'short' ? ' selected="selected"' : ''); ?>>Short</option>
                            <option value="skirt"<?= ($pdt_type == 'skirt' ? ' selected="selected"' : ''); ?>>Saia</option>
                            <option value="swimsuit"<?= ($pdt_type == 'swimsuit' ? ' selected="selected"' : ''); ?>>Maiô</option>
                            <option value="top"<?= ($pdt_type == 'top' ? ' selected="selected"' : ''); ?>>Top</option>
                        </select>
                    </label>

                    <label class="label">
                        <span class="legend">Medida:</span>
                        <select name="pdt_measurement">
                            <option value="">Selecione a Medida</option>
                            <option value="alphabetical"<?= ($pdt_measurement == 'alphabetical' ? ' selected="selected"' : ''); ?>>Alfabético</option>
                        </select>
                    </label>

                    <label class="label">
                        <span class="legend">Gênero:</span>
                        <select name="pdt_genre">
                            <option value="">Selecione o Sexo</option>
                            <option value="F"<?= ($pdt_genre == 'F' ? ' selected="selected"' : ''); ?>>Feminino</option>
                            <option value="M"<?= ($pdt_genre == 'M' ? ' selected="selected"' : ''); ?>>Masculino</option>
                        </select>
                    </label>
                </div>

                <label class="label">
                    <span class="legend">Descrição do Produto:</span>
                    <textarea name="pdt_content" class="work_mce" rows="10"><?= $pdt_content; ?></textarea>
                </label>

                <label class="label">
                    <span class="legend">Descrição de Garantia:</span>
                    <textarea name="pdt_warranty" class="work_mce_basic" rows="10"><?= $pdt_warranty; ?></textarea>
                </label>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Preço R$ (1.000,00):</span>
                        <input style="font-size: 1.4em;" type="text" name="pdt_price" value="<?= $pdt_price ? number_format($pdt_price, '2', ',', '.') : "0,00"; ?>" placeholder="Preço do Produto:" required/>
                    </label>

                    <!----------------------------------
                    ######## CUSTOM BY ALISSON #########
                    ----------------------------------->
                    <label class="label">
                        <span class="legend">Estoque:</span>
                        <span class="wc_pdt_stock btn btn_blue">
                            <span class="j_pdt_inventory"><?= $pdt_inventory; ?></span> EM ESTOQUE!
                        </span>
                    </label>
                </div>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Passo:</span>
                        <input type="text" name="pdt_step" value="<?= ($pdt_step ? number_format($pdt_step, '1', ',', '.') : 1); ?>" placeholder="Passo de Quantidade:"/>
                    </label>

                    <label class="label">
                        <span class="legend">Unidade de Medida:</span>
                        <input type="text" name="pdt_unity" value="<?= $pdt_unity; ?>" placeholder="Unidade de Medida:"/>
                    </label>
                </div>

                <span class="section icon-box-remove">DIMENSÕES DO PRODUTO:</span>
                <div class="label_50">
                    <label class="label">
                        <span class="legend">Altura Em Centímetros:</span>
                        <input type="number" name="pdt_dimension_heigth" value="<?= $pdt_dimension_heigth; ?>" placeholder="Altura em Centímetros:" required/>
                    </label>

                    <label class="label">
                        <span class="legend">Largura Em Centímetros:</span>
                        <input type="number" name="pdt_dimension_width" value="<?= $pdt_dimension_width; ?>" placeholder="Largura em Centímetros:" required/>
                    </label>
                    <div class="clear"></div>
                </div>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Profundidade Em Centímetros:</span>
                        <input type="number" name="pdt_dimension_depth" value="<?= $pdt_dimension_depth; ?>" placeholder="Profundidade em Centímetros:" required/>
                    </label>

                    <label class="label">
                        <span class="legend">Peso Em Gramas:</span>
                        <input type="number" name="pdt_dimension_weight" value="<?= $pdt_dimension_weight; ?>" placeholder="Peso em Gramas:" required/>
                    </label>
                    <div class="clear"></div>
                </div>

                <div class="clear"></div>
            </div>
        </div>

        <div class="box box30">
            <?php
            $Image = (file_exists("../uploads/{$pdt_cover}") && !is_dir("../uploads/{$pdt_cover}") ? "uploads/{$pdt_cover}" : 'admin/_img/no_image.jpg');
            ?>
            <img class="pdt_cover" alt="Capa do Produto" title="Capa do Produto" src="../tim.php?src=<?= $Image; ?>&w=<?= THUMB_W; ?>&h=<?= THUMB_H; ?>" default="../tim.php?src=<?= $Image; ?>&w=<?= THUMB_W; ?>&h=<?= THUMB_H; ?>">
            <?php
            $Read->ExeRead(DB_PDT_GALLERY, "WHERE product_id = :id", "id={$pdt_id}");
            if ($Read->getResult()):
                echo '<div class="pdt_images gallery pdt_single_image">';
                foreach ($Read->getResult() as $Image):
                    $ImageUrl = ($Image['image'] && file_exists("../uploads/{$Image['image']}") && !is_dir("../uploads/{$Image['image']}") ? "../uploads/{$Image['image']}" : '_img/no_image.jpg');
                    echo "<img rel='Products' id='{$Image['id']}' alt='Imagem em {$pdt_title}' title='Imagem em {$pdt_title}' src='{$ImageUrl}'/>";
                endforeach;
                echo '</div>';
            else:
                echo '<div class="pdt_images gallery pdt_single_image"></div>';
            endif;
            ?>

            <div class="box_content">
                <label class="label">
                    <span class="legend">Fotos Adicionais (JPG <?= THUMB_W; ?>x<?= THUMB_H; ?>px):</span>
                    <input type="file" name="image[]" multiple/>
                </label>

                <label class="label">
                    <button class="btn btn_blue j_combo_open" type="button"><span class="j_combo_count_target">(<?= $comboCount; ?>)</span> Compre Junto</button>
                </label>

                <p class="section">Oferta:</p>

                <label class="label">
                    <span class="legend">Promoção: (860,00)</span>
                    <input type="text" name="pdt_offer_price" value="<?= $pdt_offer_price ? number_format($pdt_offer_price, '2', ',', '.') : "0,00"; ?>" placeholder="Preço Promocional:"/>
                </label>

                <label class="label">
                    <span class="legend">Início da Promoção:</span>
                    <input type="text" class="formTime" name="pdt_offer_start" value="<?= ($pdt_offer_start ? date('d/m/Y H:i', strtotime($pdt_offer_start)) : null); ?>"/>
                </label>

                <label class="label">
                    <span class="legend">Fim da Promoção:</span>
                    <input type="text" class="formTime" name="pdt_offer_end" value="<?= ($pdt_offer_end ? date('d/m/Y H:i', strtotime($pdt_offer_end)) : null); ?>"/>
                </label>

                <label class="label">
                    <span class="legend">Hotsite (opcional):</span>
                    <input type="url" name="pdt_hotlink" value="<?= $pdt_hotlink; ?>" placeholder="https://"/>
                </label>

                <div class="m_top">&nbsp;</div>
                <div class="wc_actions" style="text-align: center">
                    <label class="label_check label_publish <?= ($pdt_status == 1 ? 'active' : ''); ?>"><input style="margin-top: -1px;" type="checkbox" value="1" name="pdt_status" <?= ($pdt_status == 1 ? 'checked' : ''); ?>> Publicar Agora!</label>
                    <button name="public" value="1" class="btn btn_green icon-share">ATUALIZAR</button>
                    <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                </div>
                <div class="clear"></div>
                <?php
                $URLSHARE = "/produto/{$pdt_name}";
                require '_tpl/Share.wc.php';
                ?>
            </div>
        </div>
        <div class="clear"></div>
    </form>
</div>