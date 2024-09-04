<?php

session_start();
require '../../../_app/Config.inc.php';

usleep(50000);

//DEFINE O JSON
$jSON = null;

//RECUPERA O POST
$PostData = filter_input_array(INPUT_POST, FILTER_DEFAULT);
$Action = $PostData['action'];

//PREPARA OS DADOS
unset($PostData['action']);

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
switch ($Action):
    case 'measures':
        $jSON['content'] = null;
        $jSON['gender'] = 'F';
        $jSON['shape'] = null;
        
        $height = $PostData['height'];
        $weight = $PostData['weight'];
        $age = $PostData['age'];

        if ($weight / pow($height, 2) < 18.5):
            $jSON['shape'] = 'A';
        elseif (($weight / pow($height, 2) >= 18.5) && ($weight / pow($height, 2) < 25)):
            $jSON['shape'] = 'B';
        elseif (($weight / pow($height, 2) >= 25) && ($weight / pow($height, 2) < 30)):
            $jSON['shape'] = 'C';
        elseif (($weight / pow($height, 2) >= 30) && ($weight / pow($height, 2) < 35)):
            $jSON['shape'] = 'D';
        elseif ($weight / pow($height, 2) >= 35):
            $jSON['shape'] = 'E';
        endif;
        
        $jSON['content'] = "<form name='settings' method='post' action='' enctype='multipart/form-data'>";
            $jSON['content'] .= "<input type='hidden' name='height' value='{$height}'/>";
            $jSON['content'] .= "<input type='hidden' name='weight' value='{$weight}'/>";
            $jSON['content'] .= "<input type='hidden' name='age' value='{$age}'/>";
            $jSON['content'] .= "<input type='hidden' name='bust' value='M'/>";
            $jSON['content'] .= "<input type='hidden' name='waist' value='M'/>";
            $jSON['content'] .= "<input type='hidden' name='hip' value='M'/>";
        $jSON['content'] .= "</form>";
        
        $jSON['content'] .= "<div class='virtual-taster__content__body__settings__desc'>";
            $jSON['content'] .= "<h1>Ajuste <span>seu</span> corpo</h1>";
            $jSON['content'] .= "<p>Este é o formato aproximado do seu corpo. Ajuste se for necessário.</p>";
        $jSON['content'] .= "</div>";

        $jSON['content'] .= "<div class='virtual-taster__content__body__settings__decrease'>";
            $jSON['content'] .= "<button class='js_decrease_bust' type='button'>";
                $jSON['content'] .= "<i class='fa fa-minus'></i>";
                $jSON['content'] .= "<span>Busto</span>";
            $jSON['content'] .= "</button>";

            $jSON['content'] .= "<button class='js_decrease_waist' type='button'>";
                $jSON['content'] .= "<i class='fa fa-minus'></i>";
                $jSON['content'] .= "<span>Cintura</span>";
            $jSON['content'] .= "</button>";

            $jSON['content'] .= "<button class='js_decrease_hip' type='button'>";
                $jSON['content'] .= "<i class='fa fa-minus'></i>";
                $jSON['content'] .= "<span>Quadril</span>";
            $jSON['content'] .= "</button>";
        $jSON['content'] .= "</div>";

        $jSON['content'] .= "<div class='virtual-taster__content__body__settings__image'>";
            $jSON['content'] .= "<img class='virtual-taster__content__body__settings__image__bust' src='" . BASE . "/_cdn/widgets/virtual-taster/images/{$jSON['gender']}/{$jSON['shape']}/01_02.svg' alt='Busto' title='Busto'/>";
            $jSON['content'] .= "<img class='virtual-taster__content__body__settings__image__waist' src='" . BASE . "/_cdn/widgets/virtual-taster/images/{$jSON['gender']}/{$jSON['shape']}/02_02.svg' alt='Cintura' title='Cintura'/>";
            $jSON['content'] .= "<img class='virtual-taster__content__body__settings__image__hip' src='" . BASE . "/_cdn/widgets/virtual-taster/images/{$jSON['gender']}/{$jSON['shape']}/03_02.svg' alt='Quadril' title='Quadril'/>";
        $jSON['content'] .= "</div>";

        $jSON['content'] .= "<div class='virtual-taster__content__body__settings__increase'>";
            $jSON['content'] .= "<button class='js_increase_bust' type='button'>";
                $jSON['content'] .= "<span>Busto</span>";
                $jSON['content'] .= "<i class='fa fa-plus'></i>";
            $jSON['content'] .= "</button>";

            $jSON['content'] .= "<button class='js_increase_waist' type='button'>";
                $jSON['content'] .= "<span>Cintura</span>";
                $jSON['content'] .= "<i class='fa fa-plus'></i>";
            $jSON['content'] .= "</button>";

            $jSON['content'] .= "<button class='js_increase_hip' type='button'>";
                $jSON['content'] .= "<span>Quadril</span>";
                $jSON['content'] .= "<i class='fa fa-plus'></i>";
            $jSON['content'] .= "</button>";
        $jSON['content'] .= "</div>";
        break;

    case 'settings':        
            $jSON['content'] = null;

            $height = $PostData['height'];
            $weight = $PostData['weight'];
            $age = $PostData['age'];
            $bust = $PostData['bust'];
            $waist = $PostData['waist'];
            $hip = $PostData['hip'];
            
            $size = null;
            $countBust = ($bust == 'P' ? 1 : ($bust == 'M' ? 2 : 3));
            $countWaist = ($bust == 'P' ? 1 : ($bust == 'M' ? 2 : 3));
            $countHip = ($hip == 'P' ? 0.5 : ($hip == 'M' ? 1 : 1.5));

            // 48 - 53 [P]
            if (($weight * $height >= 30) && ($weight * $height <= 33.50)):
                
            endif;
            
            // 54 - 63 [P, M]
            if (($weight * $height > 33.50) && ($weight * $height <= 40)):
                
            endif;
            
            // 64 - 74 [M, G]
            if (($weight * $height > 40) && ($weight * $height <= 47)):
                
            endif;
            
            // 75 - 85 [G, GG]
            if (($weight * $height > 47) && ($weight * $height <= 53.50)):
                
            endif;
            
            // 85 - 94 [GG]
            if (($weight * $height > 53.50) && ($weight * $height <= 59.50)):
                
            endif;
            
            $jSON['content'] = "<div class='virtual-taster__content__body__result__image'>";
                $jSON['content'] .= "<img src='" . BASE . "/_cdn/widgets/virtual-taster/images/product.jpg' alt='Body Fitness Frente Única Brilho BY029' title='Body Fitness Frente Única Brilho BY029'/>";
            $jSON['content'] .= "</div>";
            
            $jSON['content'] .= "<div class='virtual-taster__content__body__result__recommendation'>";
                $jSON['content'] .= "<h1>Sugerimos tamanho <span>{$size}</span></h1>";
            
                $jSON['content'] .= "<div class='virtual-taster__content__body__result__recommendation__wrapper'>";
                    $jSON['content'] .= "<div class='virtual-taster__content__body__result__recommendation__wrapper__pointer'>";
                        $jSON['content'] .= "<div class='virtual-taster__content__body__result__recommendation__wrapper__pointer__text'>";
                            $jSON['content'] .= "<span>Apertado</span>";
                            $jSON['content'] .= "<span>Busto</span>";
                            $jSON['content'] .= "<span>Largo</span>";
                        $jSON['content'] .= "</div>";
                        
                        $jSON['content'] .= "<div class='virtual-taster__content__body__result__recommendation__wrapper__pointer__indicator'>";
                            $jSON['content'] .= "<span></span>";
                            $jSON['content'] .= "<span>";
                                $jSON['content'] .= "<img src='" . BASE . "/_cdn/widgets/virtual-taster/images/mark.svg' alt='Indicator' title='Indicator'/>";
                            $jSON['content'] .= "</span>";
                            $jSON['content'] .= "<span></span>";
                        $jSON['content'] .= "</div>";
                    $jSON['content'] .= "</div>";
                    
                    $jSON['content'] .= "<div class='virtual-taster__content__body__result__recommendation__wrapper__pointer'>";
                        $jSON['content'] .= "<div class='virtual-taster__content__body__result__recommendation__wrapper__pointer__text'>";
                            $jSON['content'] .= "<span>Apertado</span>";
                            $jSON['content'] .= "<span>Cintura</span>";
                            $jSON['content'] .= "<span>Largo</span>";
                        $jSON['content'] .= "</div>";
                        
                        $jSON['content'] .= "<div class='virtual-taster__content__body__result__recommendation__wrapper__pointer__indicator'>";
                            $jSON['content'] .= "<span></span>";
                            $jSON['content'] .= "<span>";
                                $jSON['content'] .= "<img src='" . BASE . "/_cdn/widgets/virtual-taster/images/mark.svg' alt='Indicator' title='Indicator'/>";
                            $jSON['content'] .= "</span>";
                            $jSON['content'] .= "<span></span>";
                        $jSON['content'] .= "</div>";
                    $jSON['content'] .= "</div>";
                $jSON['content'] .= "</div>";
            $jSON['content'] .= "</div>";
        break;
endswitch;

echo json_encode($jSON);
