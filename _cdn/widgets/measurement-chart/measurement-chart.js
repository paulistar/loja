$(function () {
    /* measurement chart */
    $('html').on('click', '.js_measurement_chart_open', function () {
        $('body').css('overflow', 'hidden');

        $('.measurement-chart').fadeIn('slow', function () {
            $('.measurement-chart__content').fadeIn('fast').css({
                '-webkit-display': '-webkit-box',
                '-webkit-display': '-webkit-flex',
                display: '-webkit-box',
                display: '-webkit-flex',
                display: '-moz-box',
                display: '-ms-flexbox',
                display: 'flex'
            });
        }).css({
            '-webkit-display': '-webkit-box',
            '-webkit-display': '-webkit-flex',
            display: '-webkit-box',
            display: '-webkit-flex',
            display: '-moz-box',
            display: '-ms-flexbox',
            display: 'flex'
        });
    });

    $('html').on('mouseenter', '.j_thead_chest', function () {
        $(this).css({
            color: '#ffffff',
            'background-color': '#d1d7db'
        });

        $('.j_tbody_chest').css({
            color: '#333c4e'
        });

        if ($('.measurement-chart__content__body__image').is(":visible")) {
            $('.measurement-chart__content__body__image__chest').css('display', 'flex');
        } else {
            $('.measurement-chart__content__body__table__chest').css('display', 'flex');
        }
    });

    $('html').on('mouseout', '.j_thead_chest', function () {
        $(this).css({
            color: '#333c4e',
            'background-color': '#f4f5f8'
        });

        $('.j_tbody_chest').css({
            color: '#959fAd'
        });

        $('.measurement-chart__content__body__image__chest').css('display', 'none');
    });

    $('html').on('mouseenter', '.j_thead_waist', function () {
        $(this).css({
            color: '#ffffff',
            'background-color': '#d1d7db'
        });

        $('.j_tbody_waist').css({
            color: '#333c4e'
        });

        if ($('.measurement-chart__content__body__image').is(":visible")) {
            $('.measurement-chart__content__body__image__waist').css('display', 'flex');
        } else {
            $('.measurement-chart__content__body__table__waist').css('display', 'flex');
        }
    });

    $('html').on('mouseout', '.j_thead_waist', function () {
        $(this).css({
            color: '#333c4e',
            'background-color': '#f4f5f8'
        });

        $('.j_tbody_waist').css({
            color: '#959fAd'
        });

        $('.measurement-chart__content__body__image__waist').css('display', 'none');
    });

    $('html').on('mouseenter', '.j_thead_hip', function () {
        $(this).css({
            color: '#ffffff',
            'background-color': '#d1d7db'
        });

        $('.j_tbody_hip').css({
            color: '#333c4e'
        });

        if ($('.measurement-chart__content__body__image').is(":visible")) {
            $('.measurement-chart__content__body__image__hip').css('display', 'flex');
        } else {
            $('.measurement-chart__content__body__table__hip').css('display', 'flex');
        }
    });

    $('html').on('mouseout', '.j_thead_hip', function () {
        $(this).css({
            color: '#333c4e',
            'background-color': '#f4f5f8'
        });

        $('.j_tbody_hip').css({
            color: '#959fAd'
        });

        $('.measurement-chart__content__body__image__hip').css('display', 'none');
    });

    $('html').on('mouseenter', '.j_thead_thigh', function () {
        $(this).css({
            color: '#ffffff',
            'background-color': '#d1d7db'
        });

        $('.j_tbody_thigh').css({
            color: '#333c4e'
        });

        if ($('.measurement-chart__content__body__image').is(":visible")) {
            $('.measurement-chart__content__body__image__thigh').css('display', 'flex');
        } else {
            $('.measurement-chart__content__body__table__thigh').css('display', 'flex');
        }
    });

    $('html').on('mouseout', '.j_thead_thigh', function () {
        $(this).css({
            color: '#333c4e',
            'background-color': '#f4f5f8'
        });

        $('.j_tbody_thigh').css({
            color: '#959fAd'
        });

        $('.measurement-chart__content__body__image__thigh').css('display', 'none');
    });

    $('html').on('mouseenter', '.j_tbody_chest', function () {
        if ($('.measurement-chart__content__body__image').is(":visible")) {
            $('.measurement-chart__content__body__image__chest').css('display', 'flex');
        } else {
            $('.measurement-chart__content__body__table__chest').css('display', 'flex');
        }

        $('.j_tbody_chest').css({
            color: '#333c4e'
        });

        $('.j_thead_chest').css({
            color: '#ffffff',
            'background-color': '#d1d7db'
        });
    });

    $('html').on('mouseout', '.j_tbody_chest', function () {
        $('.measurement-chart__content__body__image__chest').css('display', 'none');

        $('.j_tbody_chest').css({
            color: '#959fAd'
        });

        $('.j_thead_chest').css({
            color: '#333c4e',
            'background-color': '#f4f5f8'
        });
    });

    $('html').on('mouseenter', '.j_tbody_waist', function () {
        if ($('.measurement-chart__content__body__image').is(":visible")) {
            $('.measurement-chart__content__body__image__waist').css('display', 'flex');
        } else {
            $('.measurement-chart__content__body__table__waist').css('display', 'flex');
        }

        $('.j_tbody_waist').css({
            color: '#333c4e'
        });

        $('.j_thead_waist').css({
            color: '#ffffff',
            'background-color': '#d1d7db'
        });
    });

    $('html').on('mouseout', '.j_tbody_waist', function () {
        $('.measurement-chart__content__body__image__waist').css('display', 'none');

        $('.j_tbody_waist').css({
            color: '#959fAd'
        });

        $('.j_thead_waist').css({
            color: '#333c4e',
            'background-color': '#f4f5f8'
        });
    });

    $('html').on('mouseenter', '.j_tbody_hip', function () {
        if ($('.measurement-chart__content__body__image').is(":visible")) {
            $('.measurement-chart__content__body__image__hip').css('display', 'flex');
        } else {
            $('.measurement-chart__content__body__table__hip').css('display', 'flex');
        }

        $('.j_tbody_hip').css({
            color: '#333c4e'
        });

        $('.j_thead_hip').css({
            color: '#ffffff',
            'background-color': '#d1d7db'
        });
    });

    $('html').on('mouseout', '.j_tbody_hip', function () {
        $('.measurement-chart__content__body__image__hip').css('display', 'none');

        $('.j_tbody_hip').css({
            color: '#959fAd'
        });

        $('.j_thead_hip').css({
            color: '#333c4e',
            'background-color': '#f4f5f8'
        });
    });

    $('html').on('mouseenter', '.j_tbody_thigh', function () {
        if ($('.measurement-chart__content__body__image').is(":visible")) {
            $('.measurement-chart__content__body__image__thigh').css('display', 'flex');
        } else {
            $('.measurement-chart__content__body__table__thigh').css('display', 'flex');
        }

        $('.j_tbody_thigh').css({
            color: '#333c4e'
        });

        $('.j_thead_thigh').css({
            color: '#ffffff',
            'background-color': '#d1d7db'
        });
    });

    $('html').on('mouseout', '.j_tbody_thigh', function () {
        $('.measurement-chart__content__body__image__thigh').css('display', 'none');

        $('.j_tbody_thigh').css({
            color: '#959fAd'
        });

        $('.j_thead_thigh').css({
            color: '#333c4e',
            'background-color': '#f4f5f8'
        });
    });

    $('html').on('click', '.j_chest_back', function () {
        $('.measurement-chart__content__body__table__chest').css('display', 'none');
    });

    $('html').on('click', '.j_waist_back', function () {
        $('.measurement-chart__content__body__table__waist').css('display', 'none');
    });

    $('html').on('click', '.j_hip_back', function () {
        $('.measurement-chart__content__body__table__hip').css('display', 'none');
    });

    $('html').on('click', '.j_thigh_back', function () {
        $('.measurement-chart__content__body__table__thigh').css('display', 'none');
    });

    $(window).resize(function () {
        var width = $(this).outerWidth();

        if (width >= 768) {
            $('.measurement-chart__content__body__table__chest').css('display', 'none');
            $('.measurement-chart__content__body__table__waist').css('display', 'none');
            $('.measurement-chart__content__body__table__hip').css('display', 'none');
            $('.measurement-chart__content__body__table__thigh').css('display', 'none');
        }
    });

    $('html').on('click', '.js_measurement_chart_close', function () {
        $('body').css('overflow', 'auto');

        $('.measurement-chart__content').fadeOut('fast', function () {
            $('.measurement-chart').fadeOut('slow');
        });
    });
});