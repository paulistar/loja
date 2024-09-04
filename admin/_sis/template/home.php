<?php

/**
 * Permission Validation
 */
$AdminLevel = LEVEL_WC_TEMPLATE;
if (!APP_PRODUCTS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

/**
 * Object Read
 */
if (empty($Read)):
    $Read = new Read;
endif;

/**
 * colors Default
 */
$Read->ExeRead(DB_TEMPLATE_COLORS, 'WHERE color_type = :type', 'type=default');
if (!$Read->getResult()):
    $colorsDefault = [
        [
            'color_background_one' => '#5a2d82',
            'color_text_one' => '#5a2d82',
            'color_background_two' => '#7838a8',
            'color_text_two' => '#7838a8',
            'color_background_three' => '#36ba9b',
            'color_text_three' => '#36ba9b',
            'color_background_four' => '#f5f5f5',
            'color_text_four' => '#f5f5f5',
            'color_type' => 'default',
            'color_status' => '1'
        ],
        [
            'color_background_one' => '#35477d',
            'color_text_one' => '#35477d',
            'color_background_two' => '#6c5b7b',
            'color_text_two' => '#6c5b7b',
            'color_background_three' => '#c06c84',
            'color_text_three' => '#c06c84',
            'color_background_four' => '#f67280',
            'color_text_four' => '#f67280',
            'color_type' => 'default',
            'color_status' => '0'
        ],
        [
            'color_background_one' => '#fffeec',
            'color_text_one' => '#fffeec',
            'color_background_two' => '#aedddc',
            'color_text_two' => '#aedddc',
            'color_background_three' => '#e4508f',
            'color_text_three' => '#e4508f',
            'color_background_four' => '#556fb5',
            'color_text_four' => '#556fb5',
            'color_type' => 'default',
            'color_status' => '0'
        ],
        [
            'color_background_one' => '#183661',
            'color_text_one' => '#183661',
            'color_background_two' => '#1c4b82',
            'color_text_two' => '#1c4b82',
            'color_background_three' => '#dd6b4d',
            'color_text_three' => '#dd6b4d',
            'color_background_four' => '#dae1e7',
            'color_text_four' => '#dae1e7',
            'color_type' => 'default',
            'color_status' => '0'
        ]
    ];

    $Create = new Create;
    foreach ($colorsDefault as $KEY => $COLORS):
        $Create->ExeCreate(DB_TEMPLATE_COLORS, $COLORS);
        $colorsDefault[$KEY]['color_id'] = $Create->getResult();
    endforeach;
else:
    $colorsDefault = $Read->getResult();
endif;

/**
 * colors Custom
 */
$Read->ExeRead(DB_TEMPLATE_COLORS, 'WHERE color_type = :type', 'type=custom');
$colorsCustom = ($Read->getResult() ? $Read->getResult() : []);
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-hammer2">Template</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            Template
        </p>
    </div>
</header>

<div class="dashboard_content">
    <article class="colors">
        <header>
            <h1>
                <i class="icon-sun"></i> Cores
            </h1>
        </header>

        <div class="colors_content j_colors_target">
            <?php foreach ($colorsCustom as $COLORS): ?>
                <form class="colors_content_box" name="colors" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="color_id" value="<?= $COLORS['color_id']; ?>">
                    <input class="j_color_background_one" type="hidden" name="color_background_one"
                           value="<?= $COLORS['color_background_one']; ?>">
                    <input class="j_color_text_one" type="hidden" name="color_text_one"
                           value="<?= $COLORS['color_text_one']; ?>">
                    <input class="j_color_background_two" type="hidden" name="color_background_two"
                           value="<?= $COLORS['color_background_two']; ?>">
                    <input class="j_color_text_two" type="hidden" name="color_text_two"
                           value="<?= $COLORS['color_text_two']; ?>">
                    <input class="j_color_background_three" type="hidden" name="color_background_three"
                           value="<?= $COLORS['color_background_three']; ?>">
                    <input class="j_color_text_three" type="hidden" name="color_text_three"
                           value="<?= $COLORS['color_text_three']; ?>">
                    <input class="j_color_background_four" type="hidden" name="color_background_four"
                           value="<?= $COLORS['color_background_four']; ?>">
                    <input class="j_color_text_four" type="hidden" name="color_text_four"
                           value="<?= $COLORS['color_text_four']; ?>">
                    <input type="hidden" name="color_type" value="custom">

                    <div class="colors_content_box_header">
                        <div class="colors_content_box_header_switch">
                            <input id="switch-id-<?= $COLORS['color_id']; ?>" class="wc_switch_input" type="checkbox"
                                   name="color_status"<?= ($COLORS['color_status'] ? ' checked="checked"' : ''); ?>>
                            <label for="switch-id-<?= $COLORS['color_id']; ?>" class="wc_switch_label wc_tooltip">
                                <span class="wc_tooltip_balloon">
                                    <?= ($COLORS['color_status'] ? 'Ativar' : 'Desativar'); ?>
                                </span>
                            </label>
                        </div>

                        <div class="colors_content_box_header_manager">
                            <button class="colors_content_box_header_manager_duplicate wc_tooltip j_colors_duplicate"
                                    type="button">
                                <span class="wc_tooltip_balloon">Duplicar</span>
                                <i class="icon-copy icon-notext"></i>
                            </button>

                            <button class="colors_content_box_header_manager_remove wc_tooltip j_colors_remove"
                                    type="button">
                                <span class="wc_tooltip_balloon">Excluir</span>
                                <i class="icon-minus icon-notext"></i>
                            </button>
                        </div>
                    </div>

                    <div class="colors_content_box_wrapper">
                        <p>
                            <i class="icon-paint-format"></i> Cor de Fundo
                        </p>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_one_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_background_one']; ?>"
                             data-target=".j_color_background_one">
                            <span class="wc_tooltip_balloon j_tooltip_one_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_background_one']); ?>
                            </span>
                            <div class="j_pickr"></div>
                        </div>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_two_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_background_two']; ?>"
                             data-target=".j_color_background_two">
                            <span class="wc_tooltip_balloon j_tooltip_two_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_background_two']); ?>
                            </span>
                            <div class="j_pickr"></div>
                        </div>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_three_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_background_three']; ?>"
                             data-target=".j_color_background_three">
                            <span class="wc_tooltip_balloon j_tooltip_three_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_background_three']); ?>
                            </span>
                            <div class="j_pickr"></div>
                        </div>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_four_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_background_four']; ?>"
                             data-target=".j_color_background_four">
                            <span class="wc_tooltip_balloon j_tooltip_four_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_background_four']); ?>
                            </span>
                            <div class="j_pickr"></div>
                        </div>
                    </div>

                    <div class="colors_content_box_wrapper">
                        <p>
                            <i class="icon-pencil"></i> Cor do Texto
                        </p>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_one_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_text_one']; ?>"
                             data-target=".j_color_text_one">
                            <span class="wc_tooltip_balloon j_tooltip_one_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_text_one']); ?>
                            </span>
                            <div class="j_pickr"></div>
                        </div>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_two_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_text_two']; ?>"
                             data-target=".j_color_text_two">
                            <span class="wc_tooltip_balloon j_tooltip_two_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_text_two']); ?>
                            </span>
                            <div class="j_pickr"></div>
                        </div>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_three_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_text_three']; ?>"
                             data-target=".j_color_text_three">
                            <span class="wc_tooltip_balloon j_tooltip_three_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_text_three']); ?>
                            </span>
                            <div class="j_pickr"></div>
                        </div>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_four_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_text_four']; ?>"
                             data-target=".j_color_text_four">
                            <span class="wc_tooltip_balloon j_tooltip_four_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_text_four']); ?>
                            </span>
                            <div class="j_pickr"></div>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>

            <?php foreach ($colorsDefault as $COLORS): ?>
                <form class="colors_content_box j_colors" name="colors" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="color_id" value="<?= $COLORS['color_id']; ?>">
                    <input class="j_color_background_one" type="hidden" name="color_background_one"
                           value="<?= $COLORS['color_background_one']; ?>">
                    <input class="j_color_text_one" type="hidden" name="color_text_one"
                           value="<?= $COLORS['color_text_one']; ?>">
                    <input class="j_color_background_two" type="hidden" name="color_background_two"
                           value="<?= $COLORS['color_background_two']; ?>">
                    <input class="j_color_text_two" type="hidden" name="color_text_two"
                           value="<?= $COLORS['color_text_two']; ?>">
                    <input class="j_color_background_three" type="hidden" name="color_background_three"
                           value="<?= $COLORS['color_background_three']; ?>">
                    <input class="j_color_text_three" type="hidden" name="color_text_three"
                           value="<?= $COLORS['color_text_three']; ?>">
                    <input class="j_color_background_four" type="hidden" name="color_background_four"
                           value="<?= $COLORS['color_background_four']; ?>">
                    <input class="j_color_text_four" type="hidden" name="color_text_four"
                           value="<?= $COLORS['color_text_four']; ?>">
                    <input type="hidden" name="color_type" value="default">

                    <div class="colors_content_box_header">
                        <div class="colors_content_box_header_switch">
                            <input id="switch-id-<?= $COLORS['color_id']; ?>" class="wc_switch_input" type="checkbox"
                                   name="color_status"<?= ($COLORS['color_status'] ? ' checked="checked"' : ''); ?>>
                            <label class="wc_switch_label wc_tooltip" for="switch-id-<?= $COLORS['color_id']; ?>">
                                <span class="wc_tooltip_balloon">
                                    <?= ($COLORS['color_status'] ? 'Desativar' : 'Ativar'); ?>
                                </span>
                            </label>
                        </div>

                        <div class="colors_content_box_header_manager">
                            <button class="colors_content_box_header_manager_duplicate wc_tooltip j_colors_duplicate"
                                    type="button">
                                <span class="wc_tooltip_balloon">Duplicar</span>
                                <i class="icon-copy icon-notext"></i>
                            </button>
                        </div>
                    </div>

                    <div class="colors_content_box_wrapper">
                        <p>
                            <i class="icon-paint-format"></i> Cor de Fundo
                        </p>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_one_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_background_one']; ?>"
                             data-target=".j_color_background_one">
                            <span class="wc_tooltip_balloon j_tooltip_one_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_background_one']); ?>
                            </span>
                        </div>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_two_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_background_two']; ?>"
                             data-target=".j_color_background_two">
                            <span class="wc_tooltip_balloon j_tooltip_two_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_background_two']); ?>
                            </span>
                        </div>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_three_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_background_three']; ?>"
                             data-target=".j_color_background_three">
                            <span class="wc_tooltip_balloon j_tooltip_three_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_background_three']); ?>
                            </span>
                        </div>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_four_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_background_four']; ?>"
                             data-target=".j_color_background_four">
                            <span class="wc_tooltip_balloon j_tooltip_four_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_background_four']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="colors_content_box_wrapper">
                        <p>
                            <i class="icon-pencil"></i> Cor do Texto
                        </p>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_one_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_text_one']; ?>" data-target=".j_color_text_one">
                            <span class="wc_tooltip_balloon j_tooltip_one_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_text_one']); ?>
                            </span>
                        </div>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_two_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_text_two']; ?>" data-target=".j_color_text_two">
                            <span class="wc_tooltip_balloon j_tooltip_two_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_text_two']); ?>
                            </span>
                        </div>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_three_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_text_three']; ?>" data-target=".j_color_text_three">
                            <span class="wc_tooltip_balloon j_tooltip_three_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_text_three']); ?>
                            </span>
                        </div>

                        <div class="colors_content_box_wrapper_item wc_tooltip j_colors_item"
                             data-tooltip=".j_tooltip_four_<?= $COLORS['color_id']; ?>"
                             data-background="<?= $COLORS['color_text_four']; ?>" data-target=".j_color_text_four">
                            <span class="wc_tooltip_balloon j_tooltip_four_<?= $COLORS['color_id']; ?>">
                                <?= strtoupper($COLORS['color_text_four']); ?>
                            </span>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </article>
</div>