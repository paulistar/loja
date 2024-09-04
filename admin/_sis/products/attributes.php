<?php
$AdminLevel = LEVEL_WC_PRODUCTS;
if (!APP_PRODUCTS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-crop">Atributos</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=products/home">Produtos</a>
            <span class="crumb">/</span>
            Atributos
        </p>
    </div>

    <div class="dashboard_header_search">
        <span class="j_open_modal btn btn_green icon-plus" title="Novo Grupo" data-type-modal="modal_group">Novo Grupo</span>
        <span class="btn btn_blue icon-spinner9 wc_drag_active" title="Organizar">Organizar</span>
    </div>
</header>

<div class="attr_modal modal_group">
    <div class="modal_area">
        <header>
            <h1>
                <i class="icon-make-group"></i> Novo Grupo
            </h1>
        </header>

        <ul class="modal_area_tab">
            <li>
                <a class="wc_tab wc_active icon-crop" href="#attr_group_size" title="Tamanhos">Tamanhos</a>
            </li>

            <li>
                <a class="wc_tab icon-paint-format" href="#attr_group_color">Cores</a>
            </li>

            <li>
                <a class="wc_tab icon-image" href="#attr_group_print" title="Estampas">Estampas</a>
            </li>
        </ul>

        <form id="attr_group_size" class="modal_area_form wc_tab_target wc_active" name="form_attr_group_size" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Products"/>
            <input type="hidden" name="callback_action" value="group_create"/>

            <div class="modal_area_form_wrapper">
                <label class="modal_area_form_wrapper_item" data-type="group">
                    <input type="text" name="group_title" placeholder="Tamanhos :: Roupas"/>
                    <input type="hidden" name="group_type" value="size"/>
                </label>
            </div>

            <div class="al_right">
                <button class="btn btn_green icon-sun">Cadastrar</button>
                <img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
            </div>
        </form>

        <form id="attr_group_color" class="modal_area_form wc_tab_target ds_none" name="form_attr_group_color" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Products"/>
            <input type="hidden" name="callback_action" value="group_create"/>

            <div class="modal_area_form_wrapper">
                <label class="modal_area_form_wrapper_item" data-type="group">
                    <input type="text" name="group_title" placeholder="Cores :: Roupas"/>
                    <input type="hidden" name="group_type" value="color"/>
                </label>
            </div>

            <div class="al_right">
                <button class="btn btn_green icon-sun">Cadastrar</button>
                <img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
            </div>
        </form>

        <form id="attr_group_print" class="modal_area_form wc_tab_target ds_none" name="form_attr_group_print" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Products"/>
            <input type="hidden" name="callback_action" value="group_create"/>

            <div class="modal_area_form_wrapper">
                <label class="modal_area_form_wrapper_item" data-type="group">
                    <input type="text" name="group_title" placeholder="Estampas :: Roupas"/>
                    <input type="hidden" name="group_type" value="print"/>
                </label>
            </div>

            <div class="al_right">
                <button class="btn btn_green icon-sun">Cadastrar</button>
                <img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
            </div>
        </form>

        <div class="attr_modal_close j_close_modal" data-type-modal="modal_group">X</div>
    </div>
</div>

<div class="attr_modal modal_attr_size">
    <div class="modal_area">
        <header>
            <h1>
                <i class="icon-crop"></i> Tamanhos
            </h1>
        </header>

        <form class="modal_area_form" name="form_attr_size" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Products"/>
            <input type="hidden" name="callback_action" value="attr_size_create"/>
            <input type="hidden" name="group_id" value=""/>

            <div class="modal_area_form_wrapper">
                <label class="modal_area_form_wrapper_item" data-type="size">
                    <input type="text" name="attr_size_code" placeholder="G">
                </label>

                <label class="modal_area_form_wrapper_item" data-type="size">
                    <input type="text" name="attr_size_title" placeholder="Grande">
                </label>
            </div>

            <div class="al_right">
                <button class="btn btn_green icon-sun">Cadastrar</button>
                <img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
            </div>

            <div class="attr_modal_close j_close_modal" data-type-modal="modal_attr_size">X</div>
        </form>
    </div>
</div>

<div class="attr_modal modal_attr_color">
    <div class="modal_area">
        <header>
            <h1>
                <i class="icon-paint-format"></i> Cores
            </h1>
        </header>

        <form class="modal_area_form" name="form_attr_color" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Products"/>
            <input type="hidden" name="callback_action" value="attr_color_create"/>
            <input type="hidden" name="group_id" value=""/>

            <input class="j_attr_color_one" type="hidden" name="attr_color_code[]">
            <input class="j_attr_color_two" type="hidden" name="attr_color_code[]">
            <input class="j_attr_color_three" type="hidden" name="attr_color_code[]">
            <input class="j_attr_color_four" type="hidden" name="attr_color_code[]">

            <div class="modal_area_form_wrapper">
                <div class="modal_area_form_wrapper_item j_colors_item" data-background="transparent"
                     data-target=".j_attr_color_one" data-type="color">
                    <div class="j_pickr"></div>
                </div>

                <div class="modal_area_form_wrapper_item j_colors_item" data-background="transparent"
                     data-target=".j_attr_color_two" data-type="color">
                    <div class="j_pickr"></div>
                </div>

                <div class="modal_area_form_wrapper_item j_colors_item" data-background="transparent"
                     data-target=".j_attr_color_three" data-type="color">
                    <div class="j_pickr"></div>
                </div>

                <div class="modal_area_form_wrapper_item j_colors_item" data-background="transparent"
                     data-target=".j_attr_color_four" data-type="color">
                    <div class="j_pickr"></div>
                </div>
            </div>

            <div class="modal_area_form_wrapper">
                <label class="modal_area_form_wrapper_item" data-type="color">
                    <input type="text" name="attr_color_title[]" placeholder="Amarelo">
                </label>

                <label class="modal_area_form_wrapper_item" data-type="color">
                    <input type="text" name="attr_color_title[]" placeholder="Preto">
                </label>

                <label class="modal_area_form_wrapper_item" data-type="color">
                    <input type="text" name="attr_color_title[]" placeholder="Azul">
                </label>

                <label class="modal_area_form_wrapper_item" data-type="color">
                    <input type="text" name="attr_color_title[]" placeholder="Branco">
                </label>
            </div>

            <div class="al_right">
                <button class="btn btn_green icon-sun">Cadastrar</button>
                <button class="btn btn_red icon-bin j_clear_colors">Limpar</button>
                <img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
            </div>

            <div class="attr_modal_close j_close_modal" data-type-modal="modal_attr_color">X</div>
        </form>
    </div>
</div>

<div class="attr_modal modal_attr_print">
    <div class="modal_area">
        <header>
            <h1>
                <i class="icon-image"></i> Estampas
            </h1>
        </header>

        <form class="modal_area_form" name="form_attr_size" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Products"/>
            <input type="hidden" name="callback_action" value="attr_print_create"/>
            <input type="hidden" name="group_id" value=""/>

            <div class="modal_area_form_wrapper">
                <label class="modal_area_form_wrapper_item" data-type="size">
                    <input type="file" name="attr_print_code"/>
                </label>

                <label class="modal_area_form_wrapper_item" data-type="size">
                    <input type="text" name="attr_print_title" placeholder="Estampa Arco-íris">
                </label>
            </div>

            <div class="al_right">
                <button class="btn btn_green icon-sun">Cadastrar</button>
                <img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
            </div>

            <div class="attr_modal_close j_close_modal" data-type-modal="modal_attr_print">X</div>
        </form>
    </div>
</div>

<div class="dashboard_content j_group_target">
    <?php
    $Read->FullRead("SELECT * FROM " . DB_PDT_GROUPS_ATTR . " ORDER BY group_order ASC, group_created DESC");
    if ($Read->getResult()):
        foreach ($Read->getResult() as $GROUP):
            ?>
            <div class="attr_group box box25 wc_draganddrop" callback="Products" callback_action="group_order" id="<?= $GROUP['group_id']; ?>">
                <div class="panel_header default al_center">
                    <h2><?= $GROUP['group_title']; ?></h2>
                </div>

                <div class="attr_group_actions">
                    <span class="j_open_modal btn btn_green icon-plus icon-notext" title="Novo Atributo" data-type-modal="modal_attr_<?= $GROUP['group_type']; ?>" data-group-id="<?= $GROUP['group_id']; ?>"></span>
                    <span rel="attr_group" class="j_delete_action btn btn_red icon-bin icon-notext" title="Remover Grupo" id="<?= $GROUP['group_id']; ?>"></span>
                    <span rel="attr_group" callback="Products" callback_action="group_remove" class="j_delete_action_confirm btn btn_yellow icon-bin icon-notext" title="Remover Grupo" style="display: none;" id="<?= $GROUP['group_id']; ?>"></span>
                </div>

                <div class="attr_group_body">
                    <div class="attr_group_scroll j_attr_item_target_<?= $GROUP['group_id']; ?>"><?php
                        $Read->FullRead("SELECT attr_" . $GROUP['group_type'] . "_id, attr_" . $GROUP['group_type'] . "_code, attr_" . $GROUP['group_type'] . "_title FROM " . ($GROUP['group_type'] == 'size' ? DB_PDT_ATTR_SIZES : ($GROUP['group_type'] == 'color' ? DB_PDT_ATTR_COLORS : DB_PDT_ATTR_PRINTS)) . " WHERE group_id = :group ORDER BY attr_" . $GROUP['group_type'] . "_created DESC", "group={$GROUP['group_id']}");
                        if ($Read->getResult()):
                            foreach ($Read->getResult() as $ATTR):
                                extract($ATTR);
                                if ($GROUP['group_type'] == 'color'):
                                    if (strpos($attr_color_code, ',')):
                                        $arr_color = explode(',', $attr_color_code);
                                        $bg_color = (count($arr_color) == 2 ? "style='background-image: linear-gradient(45deg, {$arr_color[0]}  50%, {$arr_color[1]} 50%);'" : (count($arr_color) == 3 ? "style='background-image: linear-gradient(45deg, {$arr_color[0]}  33%, {$arr_color[1]} 33%, {$arr_color[1]} 67%, {$arr_color[2]} 67%, {$arr_color[2]});'" : "style='background-image: linear-gradient(45deg, {$arr_color[0]}  25%, {$arr_color[1]} 25%, {$arr_color[1]} 50%, {$arr_color[2]} 50%, {$arr_color[2]} 75%, {$arr_color[3]} 75%, {$arr_color[3]});'"));
                                    else:
                                        $bg_color = "style='background-color: {$attr_color_code};'";
                                    endif;
                                elseif ($GROUP['group_type'] == 'print'):
                                    $bg_print = "style='background: url(" . BASE . "/uploads/{$attr_print_code}) center / cover no-repeat;'";
                                endif;
                                ?><div class="attr_group_item <?= "attr_{$GROUP['group_type']}_" . ($GROUP['group_type'] == 'size' ? $attr_size_id : ($GROUP['group_type'] == 'color' ? $attr_color_id : $attr_print_id)); ?>" id="<?= ($GROUP['group_type'] == 'size' ? $attr_size_id : ($GROUP['group_type'] == 'color' ? $attr_color_id : $attr_print_id)); ?>">
                                    <div<?= ($GROUP['group_type'] == 'color' ? " {$bg_color}" : ($GROUP['group_type'] == 'print' ? " {$bg_print}" : '')); ?> class='attr_group_item_code' title="<?= ($GROUP['group_type'] == 'size' ? $attr_size_title : ($GROUP['group_type'] == 'color' ? $attr_color_title : $attr_print_title)); ?>">
                                        <?php if ($GROUP['group_type'] == 'size'): ?>
                                            <span class="attr_group_item_code_size"><?= $attr_size_code; ?></span>
                                        <?php endif; ?>
                                        <span rel="<?= "attr_{$GROUP['group_type']}_" . ($GROUP['group_type'] == 'size' ? $attr_size_id : ($GROUP['group_type'] == 'color' ? $attr_color_id : $attr_print_id)); ?>" class="j_delete_action attr_group_item_remove icon-cancel-circle icon-notext" id="<?= ($GROUP['group_type'] == 'size' ? $attr_size_id : ($GROUP['group_type'] == 'color' ? $attr_color_id : $attr_print_id)); ?>"></span>
                                        <span rel="<?= "attr_{$GROUP['group_type']}_" . ($GROUP['group_type'] == 'size' ? $attr_size_id : ($GROUP['group_type'] == 'color' ? $attr_color_id : $attr_print_id)); ?>" callback="Products" callback_action="attr_<?= $GROUP['group_type']; ?>_remove" class="j_delete_action_confirm attr_group_item_remove icon-cancel-circle icon-notext" style="display: none;" id="<?= ($GROUP['group_type'] == 'size' ? $attr_size_id : ($GROUP['group_type'] == 'color' ? $attr_color_id : $attr_print_id)); ?>"></span>
                                    </div>
                                </div><?php
                            endforeach;
                        endif;
                        ?></div>
                </div>
            </div>
            <?php
        endforeach;
    endif;
    ?>
</div>
