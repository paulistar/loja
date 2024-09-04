<link rel="stylesheet" href="<?= BASE; ?>/_cdn/widgets/measurement-chart/measurement-chart.css"/>
<script src="<?= BASE; ?>/_cdn/widgets/measurement-chart/measurement-chart.js"></script>

<?php
$Read->FullRead("SELECT s.size_id, s.stock_inventory, a.attr_size_code FROM " . DB_PDT_STOCK . " s INNER JOIN " . DB_PDT_ATTR_SIZES . " a ON s.size_id = a.attr_size_id WHERE pdt_id = :id AND s.size_id IS NOT NULL AND s.stock_inventory >= :inventory", "id={$pdt_id}&inventory=1");
if ($Read->getResult() && !empty($pdt_type) && !empty($pdt_measurement)):
    $tripleColumns = [
        'pants', //Calça
        'short', //Short
        'skirt' //Saia
    ];

    $allColumns = [
        'blouse', //Blusa,
        'body', //Body
        'coat', //Casaco
        'cropped', //Cropped,
        'dress', //Vestido,
        'shirt', //Camisa
        'swimsuit', //Maiô
        'top' //Top
    ];
endif;


$pdtMeasures = [];
foreach ($Read->getResult() as $SIZE):
    $pdtMeasures[] = strtoupper($SIZE['attr_size_code']);
endforeach;

if ($Read->getResult() && !empty($pdt_type) && !empty($pdt_measurement)):
    ?>
    <div class="measurement-chart">
        <div class="measurement-chart__content">
            <div class="measurement-chart__content__header">
                <img class="measurement-chart__content__header__logo" src="<?= INCLUDE_PATH; ?>/images/logo_footer.png" alt="<?= SITE_NAME; ?>" title="<?= SITE_NAME; ?>"/>
                <div class="measurement-chart__content__header__close js_measurement_chart_close"><i class="fa fa-close"></i></div>
            </div>

            <div class="measurement-chart__content__body">
                <div class="measurement-chart__content__body__image">
                    <img class="measurement-chart__content__body__image__cover" src="<?= BASE; ?>/uploads/<?= $pdt_cover; ?>" alt="<?= $pdt_title; ?>" title="<?= $pdt_title; ?>"/>

                    <div class="measurement-chart__content__body__image__chest">
                        <img src="<?= BASE; ?>/_cdn/widgets/measurement-chart/images/<?= $pdt_genre; ?>/chest.png" alt="Peito" title="Peito"/>
                        <p>Contorne com a fita métrica seu peito e respire normalmente. Percerba as variações enquanto respira e registre o maior número.</p>
                    </div>

                    <div class="measurement-chart__content__body__image__waist">
                        <img src="<?= BASE; ?>/_cdn/widgets/measurement-chart/images/<?= $pdt_genre; ?>/waist.png" alt="Cintura" title="Cintura"/>
                        <p>Inclinando ligeiramente o troco para o lado, visualize a formação de um vinco, que indica a posição da cintura natural. Envolva a cintura.</p>
                    </div>

                    <div class="measurement-chart__content__body__image__hip">
                        <img src="<?= BASE; ?>/_cdn/widgets/measurement-chart/images/<?= $pdt_genre; ?>/hip.png" alt="Quadril" title="Quadril"/>
                        <p>Contorne com a fita métrica pela região mais 'larga' abaixo da cintura passando paralelamente pela parte mais alta dos glúteos.</p>
                    </div>

                    <div class="measurement-chart__content__body__image__thigh">
                        <img src="<?= BASE; ?>/_cdn/widgets/measurement-chart/images/<?= $pdt_genre; ?>/thigh.png" alt="Coxa" title="Coxa"/>
                        <p>Contorne a fita métrica ao redor da parte mais larga da sua coxa, localizada um pouco abaixo da região dos glúteos.</p>
                    </div>
                </div>

                <div class="measurement-chart__content__body__table">
                    <p class="measurement-chart__content__body__table__title">Compare as medidas do seu corpo com esta tabela. Passe o mouse sobre as medidas para aprender como tirá-las.</p>

                    <!-- Body, Maiô, Cropped, Casaco, Top, Vestido -->
                    <?php if (in_array($pdt_type, $allColumns) && $pdt_measurement == 'alphabetical'): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="j_thead_chest">Peito</th>
                                    <th class="j_thead_waist">Cintura</th>
                                    <th class="j_thead_hip">Quadril</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (in_array('P', $pdtMeasures)): ?>
                                    <tr>
                                        <td>P</td>
                                        <td class="j_tbody_chest"><?= ($pdt_genre == 'F' ? '84 - 92' : '94 - 96'); ?></td>
                                        <td class="j_tbody_waist"><?= ($pdt_genre == 'F' ? '66 - 74' : '84 - 90'); ?></td>
                                        <td class="j_tbody_hip"><?= ($pdt_genre == 'F' ? '88 - 96' : '88 - 90'); ?></td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (in_array('M', $pdtMeasures)): ?>
                                    <tr>
                                        <td>M</td>
                                        <td class="j_tbody_chest"><?= ($pdt_genre == 'F' ? '92 - 100' : '102 - 104'); ?></td>
                                        <td class="j_tbody_waist"><?= ($pdt_genre == 'F' ? '74 - 82' : '90 - 94'); ?></td>
                                        <td class="j_tbody_hip"><?= ($pdt_genre == 'F' ? '96 - 104' : '91 - 98'); ?></td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (in_array('G', $pdtMeasures)): ?>
                                    <tr>
                                        <td>G</td>
                                        <td class="j_tbody_chest"><?= ($pdt_genre == 'F' ? '96 - 100' : '106 - 108'); ?></td>
                                        <td class="j_tbody_waist"><?= ($pdt_genre == 'F' ? '78 - 82' : '94 - 96'); ?></td>
                                        <td class="j_tbody_hip"><?= ($pdt_genre == 'F' ? '106 - 110' : '99 - 100'); ?></td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (in_array('GG', $pdtMeasures)): ?>
                                    <tr>
                                        <td>GG</td>
                                        <td class="j_tbody_chest"><?= ($pdt_genre == 'F' ? '100 - 108' : '112 - 115'); ?></td>
                                        <td class="j_tbody_waist"><?= ($pdt_genre == 'F' ? '82 - 90' : '96 - 111'); ?></td>
                                        <td class="j_tbody_hip"><?= ($pdt_genre == 'F' ? '104 - 112' : '101 - 114'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <!-- Calça, Saia, Short -->
                    <?php if (in_array($pdt_type, $tripleColumns) && $pdt_measurement == 'alphabetical'): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="j_thead_waist">Cintura</th>
                                    <th class="j_thead_hip">Quadril</th>
                                    <th class="j_thead_thigh">Coxa</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (in_array('P', $pdtMeasures)): ?>
                                    <tr>
                                        <td>P</td>
                                        <td class="j_tbody_waist">66 - 74</td>
                                        <td class="j_tbody_hip">88 - 96</td>
                                        <td class="j_tbody_thigh">40 - 43</td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (in_array('M', $pdtMeasures)): ?>
                                    <tr>
                                        <td>M</td>
                                        <td class="j_tbody_waist">74 - 82</td>
                                        <td class="j_tbody_hip">96 - 104</td>
                                        <td class="j_tbody_thigh">44 - 53</td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (in_array('G', $pdtMeasures)): ?>
                                    <tr>
                                        <td>G</td>
                                        <td class="j_tbody_waist">82 - 90</td>
                                        <td class="j_tbody_hip">104 - 112</td>
                                        <td class="j_tbody_thigh">54 - 61</td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (in_array('GG', $pdtMeasures)): ?>
                                    <tr>
                                        <td>GG</td>
                                        <td class="j_tbody_waist">90 - 98</td>
                                        <td class="j_tbody_hip">112 - 120</td>
                                        <td class="j_tbody_thigh">62 - 64</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <p class="measurement-chart__content__body__table__legend">Medidas em centímetros</p>

                    <div class="measurement-chart__content__body__table__desc">
                        <img src="<?= BASE; ?>/_cdn/widgets/measurement-chart/images/dress.png" alt="Primeira troca é *gratuita. *Confira nossa politica de troca." title="Primeira troca é *gratuita. *Confira nossa politica de troca."/>
                        <p>Pode comprar sem medo, caso a roupa não fique do seu gosto, a primeira troca é gratuita.</p>
                    </div>

                    <div class="measurement-chart__content__body__table__chest">
                        <img src="<?= BASE; ?>/_cdn/widgets/measurement-chart/images/<?= $pdt_genre; ?>/chest.png" alt="Peito" title="Peito"/>
                        <p>Contorne com a fita métrica seu peito e respire normalmente. Percerba as variações enquanto respira e registre o maior número.</p>
                        <i class="fa fa-arrow-left j_chest_back"></i>
                    </div>

                    <div class="measurement-chart__content__body__table__waist">
                        <img src="<?= BASE; ?>/_cdn/widgets/measurement-chart/images/<?= $pdt_genre; ?>/waist.png" alt="Cintura" title="Cintura"/>
                        <p>Inclinando ligeiramente o troco para o lado, visualize a formação de um vinco, que indica a posição da cintura natural. Envolva a cintura.</p>
                        <i class="fa fa-arrow-left j_waist_back"></i>
                    </div>

                    <div class="measurement-chart__content__body__table__hip">
                        <img src="<?= BASE; ?>/_cdn/widgets/measurement-chart/images/<?= $pdt_genre; ?>/hip.png" alt="Quadril" title="Quadril"/>
                        <p>Contorne com a fita métrica pela região mais 'larga' abaixo da cintura passando paralelamente pela parte mais alta dos glúteos.</p>
                        <i class="fa fa-arrow-left j_hip_back"></i>
                    </div>

                    <div class="measurement-chart__content__body__table__thigh">
                        <img src="<?= BASE; ?>/_cdn/widgets/measurement-chart/images/<?= $pdt_genre; ?>/thigh.png" alt="Coxa" title="Coxa"/>
                        <p>Contorne a fita métrica ao redor da parte mais larga da sua coxa, localizada um pouco abaixo da região dos glúteos.</p>
                        <i class="fa fa-arrow-left j_thigh_back"></i>
                    </div>
                </div>
            </div>

            <div class="measurement-chart__content__footer">
                <a href="<?= BASE; ?>/_cdn/widgets/measurement-chart/images/tape.pdf" title="Fita Métrica" download>
                    <img src="<?= BASE; ?>/_cdn/widgets/measurement-chart/images/printer.png" alt="Fita Métrica" title="Fita Métrica"/> Fita Métrica
                </a>

                <div class="measurement-chart__content__footer__virtual-taster">
                    <p>Ainda possui dúvidas? Utilize nosso provador virtual.</p>

                    <button type="button">
                        <img src="<?= BASE; ?>/_cdn/widgets/measurement-chart/images/hanger.png" alt="Provador Virtual" title="Provador Virtual"/> Provador Virtual
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
endif;
?>
