<?php

// Colors
$Read->ExeRead(DB_TEMPLATE_COLORS, 'WHERE color_status = :status', "status=1");
if ($Read->getResult()):
    // Background Color
    $colorBackgroundOne = $Read->getResult()[0]['color_background_one'];
    $colorBackgroundTwo = $Read->getResult()[0]['color_background_two'];
    $colorBackgroundThree = $Read->getResult()[0]['color_background_three'];
    $colorBackgroundFour = $Read->getResult()[0]['color_background_four'];

    // Text Color
    $colorTextOne = $Read->getResult()[0]['color_text_one'];
    $colorTextTwo = $Read->getResult()[0]['color_text_two'];
    $colorTextThree = $Read->getResult()[0]['color_text_three'];
    $colorTextFour = $Read->getResult()[0]['color_text_four'];

    $colorBackgroundOneOpacity = $Read->getResult()[0]['color_background_one'] . 'cc';
    $colorBackgroundTwoOpacity = $Read->getResult()[0]['color_background_two'] . 'cc';
    $colorBackgroundThreeOpacity = $Read->getResult()[0]['color_background_three'] . 'cc';
    $colorBackgroundFourOpacity = $Read->getResult()[0]['color_background_four'] . 'cc';
else:
    // Background Color
    $colorBackgroundOne = '#5a2d82';
    $colorBackgroundTwo = '#7838a8';
    $colorBackgroundThree = '#36ba9b';
    $colorBackgroundFour = '#f5f5f5';

    // Background Color Opacity
    $colorBackgroundOneOpacity = '#5a2d82cc';
    $colorBackgroundTwoOpacity = '#7838a8cc';
    $colorBackgroundThreeOpacity = '#36ba9bcc';
    $colorBackgroundFourOpacity = '#f5f5f5cc';

    // Text Color
    $colorTextOne = '#5a2d82';
    $colorTextTwo = '#7838a8';
    $colorTextThree = '#36ba9b';
    $colorTextFour = '#f5f5f5';
endif;
?>

<style>
    :root {
        /* Background Color */
        --color-background-one: <?= $colorBackgroundOne; ?>;
        --color-background-two: <?= $colorBackgroundTwo; ?>;
        --color-background-three: <?= $colorBackgroundThree; ?>;
        --color-background-four: <?= $colorBackgroundFour; ?>;

        /* Background Color Opacity */
        --color-background-one-opacity: <?= $colorBackgroundOneOpacity; ?>;
        --color-background-two-opacity: <?= $colorBackgroundTwoOpacity; ?>;
        --color-background-three-opacity: <?= $colorBackgroundThreeOpacity; ?>;
        --color-background-four-opacity: <?= $colorBackgroundFourOpacity; ?>;

        /* Text Color */
        --color-text-one: <?= $colorTextOne; ?>;
        --color-text-two: <?= $colorTextTwo; ?>;
        --color-text-three: <?= $colorTextThree; ?>;
        --color-text-four: <?= $colorTextFour; ?>;
    }

    * {
        font-family: '<?= SITE_FONT_NAME; ?>', sans-serif;
    }
</style>