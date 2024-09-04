$(function () {
    // GENERAL
    var URL = $('link[rel="base"]').attr('href') + '/_cdn/widgets/virtual-taster';
    var AJAX = URL + '/virtual-taster.ajax.php';

    // VALIDATE FIELDS
    $('html').on('keyup', '.js_height', function () {
        var height = $(this).val();
        var weight = $('.js_weight').val();
        var age = $('.js_age').val();

        if (height && weight && age) {
            $('.js_next_step').removeAttr('disabled').css({
                opacity: 1,
                cursor: 'pointer'
            });
        } else {
            $('.js_next_step').attr('disabled', 'disabled').css({
                opacity: 0.4,
                cursor: 'not-allowed'
            });
        }
    });

    $('html').on('keyup', '.js_weight', function () {
        var height = $('.js_height').val();
        var weight = $(this).val();
        var age = $('.js_age').val();

        if (height && weight && age) {
            $('.js_next_step').removeAttr('disabled').css({
                opacity: 1,
                cursor: 'pointer'
            });
        } else {
            $('.js_next_step').attr('disabled', 'disabled').css({
                opacity: 0.4,
                cursor: 'not-allowed'
            });
        }
    });

    $('html').on('keyup', '.js_age', function () {
        var height = $('.js_height').val();
        var weight = $('.js_weight').val();
        var age = $(this).val();

        if (height && weight && age) {
            $('.js_next_step').removeAttr('disabled').css({
                opacity: 1,
                cursor: 'pointer'
            });
        } else {
            $('.js_next_step').attr('disabled', 'disabled').css({
                opacity: 0.4,
                cursor: 'not-allowed'
            });
        }
    });

    // NEXT STEP
    $('html').on('click', '.js_next_step', function () {
        $(this).removeClass('js_next_step').addClass('js_check_result');

        $('.virtual-taster__content__body__measures').fadeOut('fast', function () {
            $.post(AJAX, $('form[name="measures"]').serialize() + '&action=measures', function (data) {

                $('.virtual-taster__content__body__settings').html(data.content).fadeIn('fast').css('display', 'flex');

                // DECREASE BUST
                $('html').on('click', '.js_decrease_bust', function () {
                    var button = $(this);

                    if ($('.virtual-taster__content__body__settings__image__bust').attr('src') === URL + '/images/' + data.gender + '/' + data.shape + '/01_02.svg') {
                        $('.virtual-taster__content__body__settings__image__bust').attr('src', URL + '/images/' + data.gender + '/' + data.shape + '/01_01.svg');
                        $('form[name="settings"]').find('input[name="bust"]').val('P');

                        button.attr('disabled', 'disabled').css({
                            opacity: '0.4'
                        });

                    }

                    if ($('.virtual-taster__content__body__settings__image__bust').attr('src') === URL + '/images/' + data.gender + '/' + data.shape + '/01_03.svg') {
                        $('.virtual-taster__content__body__settings__image__bust').attr('src', URL + '/images/' + data.gender + '/' + data.shape + '/01_02.svg');
                        $('form[name="settings"]').find('input[name="bust"]').val('M');
                    }

                    if ($('.js_increase_bust').attr('disabled')) {
                        $('.js_increase_bust').removeAttr('disabled').css({
                            opacity: '1'
                        });
                    }
                });

                // INCREASE BUST
                $('html').on('click', '.js_increase_bust', function () {
                    var button = $(this);

                    if ($('.virtual-taster__content__body__settings__image__bust').attr('src') === URL + '/images/' + data.gender + '/' + data.shape + '/01_02.svg') {
                        $('.virtual-taster__content__body__settings__image__bust').attr('src', URL + '/images/' + data.gender + '/' + data.shape + '/01_03.svg');
                        $('form[name="settings"]').find('input[name="bust"]').val('G');

                        button.attr('disabled', 'disabled').css({
                            opacity: '0.4'
                        });

                    }

                    if ($('.virtual-taster__content__body__settings__image__bust').attr('src') === URL + '/images/' + data.gender + '/' + data.shape + '/01_01.svg') {
                        $('.virtual-taster__content__body__settings__image__bust').attr('src', URL + '/images/' + data.gender + '/' + data.shape + '/01_02.svg');
                        $('form[name="settings"]').find('input[name="bust"]').val('M');
                    }

                    if ($('.js_decrease_bust').attr('disabled')) {
                        $('.js_decrease_bust').removeAttr('disabled').css({
                            opacity: '1'
                        });
                    }
                });

                // DECREASE WAIST
                $('html').on('click', '.js_decrease_waist', function () {
                    var button = $(this);

                    if ($('.virtual-taster__content__body__settings__image__waist').attr('src') === URL + '/images/' + data.gender + '/' + data.shape + '/02_02.svg') {
                        $('.virtual-taster__content__body__settings__image__waist').attr('src', URL + '/images/' + data.gender + '/' + data.shape + '/02_01.svg');
                        $('form[name="settings"]').find('input[name="waist"]').val('P');

                        button.attr('disabled', 'disabled').css({
                            opacity: '0.4'
                        });

                    }

                    if ($('.virtual-taster__content__body__settings__image__waist').attr('src') === URL + '/images/' + data.gender + '/' + data.shape + '/02_03.svg') {
                        $('.virtual-taster__content__body__settings__image__waist').attr('src', URL + '/images/' + data.gender + '/' + data.shape + '/02_02.svg');
                        $('form[name="settings"]').find('input[name="waist"]').val('M');
                    }

                    if ($('.js_increase_waist').attr('disabled')) {
                        $('.js_increase_waist').removeAttr('disabled').css({
                            opacity: '1'
                        });
                    }
                });

                // INCREASE WAIST
                $('html').on('click', '.js_increase_waist', function () {
                    var button = $(this);

                    if ($('.virtual-taster__content__body__settings__image__waist').attr('src') === URL + '/images/' + data.gender + '/' + data.shape + '/02_02.svg') {
                        $('.virtual-taster__content__body__settings__image__waist').attr('src', URL + '/images/' + data.gender + '/' + data.shape + '/02_03.svg');
                        $('form[name="settings"]').find('input[name="waist"]').val('G');

                        button.attr('disabled', 'disabled').css({
                            opacity: '0.4'
                        });

                    }

                    if ($('.virtual-taster__content__body__settings__image__waist').attr('src') === URL + '/images/' + data.gender + '/' + data.shape + '/02_01.svg') {
                        $('.virtual-taster__content__body__settings__image__waist').attr('src', URL + '/images/' + data.gender + '/' + data.shape + '/02_02.svg');
                        $('form[name="settings"]').find('input[name="waist"]').val('M');
                    }

                    if ($('.js_decrease_waist').attr('disabled')) {
                        $('.js_decrease_waist').removeAttr('disabled').css({
                            opacity: '1'
                        });
                    }
                });

                // DECREASE HIP
                $('html').on('click', '.js_decrease_hip', function () {
                    var button = $(this);

                    if ($('.virtual-taster__content__body__settings__image__hip').attr('src') === URL + '/images/' + data.gender + '/' + data.shape + '/03_02.svg') {
                        $('.virtual-taster__content__body__settings__image__hip').attr('src', URL + '/images/' + data.gender + '/' + data.shape + '/03_01.svg');
                        $('form[name="settings"]').find('input[name="hip"]').val('P');

                        button.attr('disabled', 'disabled').css({
                            opacity: '0.4'
                        });

                    }

                    if ($('.virtual-taster__content__body__settings__image__hip').attr('src') === URL + '/images/' + data.gender + '/' + data.shape + '/03_03.svg') {
                        $('.virtual-taster__content__body__settings__image__hip').attr('src', URL + '/images/' + data.gender + '/' + data.shape + '/03_02.svg');
                        $('form[name="settings"]').find('input[name="hip"]').val('M');
                    }

                    if ($('.js_increase_hip').attr('disabled')) {
                        $('.js_increase_hip').removeAttr('disabled').css({
                            opacity: '1'
                        });
                    }
                });

                // INCREASE HIP
                $('html').on('click', '.js_increase_hip', function () {
                    var button = $(this);

                    if ($('.virtual-taster__content__body__settings__image__hip').attr('src') === URL + '/images/' + data.gender + '/' + data.shape + '/03_02.svg') {
                        $('.virtual-taster__content__body__settings__image__hip').attr('src', URL + '/images/' + data.gender + '/' + data.shape + '/03_03.svg');
                        $('form[name="settings"]').find('input[name="hip"]').val('G');

                        button.attr('disabled', 'disabled').css({
                            opacity: '0.4'
                        });

                    }

                    if ($('.virtual-taster__content__body__settings__image__hip').attr('src') === URL + '/images/' + data.gender + '/' + data.shape + '/03_01.svg') {
                        $('.virtual-taster__content__body__settings__image__hip').attr('src', URL + '/images/' + data.gender + '/' + data.shape + '/03_02.svg');
                        $('form[name="settings"]').find('input[name="hip"]').val('M');
                    }

                    if ($('.js_decrease_hip').attr('disabled')) {
                        $('.js_decrease_hip').removeAttr('disabled').css({
                            opacity: '1'
                        });
                    }
                });

            }, 'json');

            $('.virtual-taster__content__footer__previous').fadeIn('fast');
        });
    });

    // PREVIOUS STEP
    $('html').on('click', '.js_previous_step', function () {
        $('.js_check_result').removeClass('js_check_result').addClass('js_next_step');

        $('.virtual-taster__content__body__settings').fadeOut('fast', function () {
            $('.virtual-taster__content__body__measures').fadeIn('fast').css('display', 'flex');
            $('.virtual-taster__content__footer__previous').fadeOut('fast');
        });
    });

    // CHECK RESULT
    $('html').on('click', '.js_check_result', function () {
        $('.js_previous_step').html("<i class='fa fa-undo'></i> REINICIAR PROVADOR").removeClass('js_previous_step').addClass('js_restart').css('display', 'flex');
        $(this).html('FECHAR').removeClass('js_check_result').addClass('js_close');

        $('.virtual-taster__content__body__settings').fadeOut('fast', function () {

            // SUBMIT SETTINGS
            $.post(AJAX, $('form[name="settings"]').serialize() + '&action=settings', function (data) {

                $('.virtual-taster__content__body__result').html(data.content).fadeIn('fast').css('display', 'flex');

            }, 'json');

        });
    });

    // RESTART
    $('html').on('click', '.js_restart', function () {
        $(this).fadeOut('fast', function () {
            $(this).html('VOLTAR').removeClass('js_restart').addClass('js_previous_step');
        });

        $('form[name="measures"]').find('input[name="height"]').val('');
        $('form[name="measures"]').find('input[name="weight"]').val('');
        $('form[name="measures"]').find('input[name="age"]').val('');
        $('form[name="settings"]').find('input[name="height"]').val('');
        $('form[name="settings"]').find('input[name="weight"]').val('');
        $('form[name="settings"]').find('input[name="age"]').val('');
        $('form[name="settings"]').find('input[name="bust"]').val('M');
        $('form[name="settings"]').find('input[name="waist"]').val('M');
        $('form[name="settings"]').find('input[name="hip"]').val('M');

        $('.virtual-taster__content__body__result').fadeOut('fast', function () {
            $('.virtual-taster__content__body__measures').fadeIn('fast', function () {
                $('.virtual-taster__content__body__settings').empty();
                $('.virtual-taster__content__body__result').empty();
            }).css('display', 'flex');
        });

        $('button.js_close').html('PRÓXIMO').removeClass('js_close').addClass('js_next_step').css({
            opacity: 0.4,
            cursor: 'not-allowed'
        }).attr('disabled', 'disabled');
    });

    // OPEN
    $('html').on('click', '.js_open', function () {
        $('.virtual-taster').fadeIn('slow', function () {
            $('.virtual-taster__content').fadeIn('fast').css('display', 'flex');
        }).css('display', 'flex');
    });

    // CLOSE
    $('html').on('click', '.js_close', function () {
        $('.virtual-taster__content').fadeOut('fast', function () {
            $('.virtual-taster').fadeOut('slow', function () {
                $('.virtual-taster__content__body__result').fadeOut('fast', function () {
                    $('.virtual-taster__content__body__measures').fadeIn('fast', function () {
                        $('.virtual-taster__content__body__settings').fadeOut('fast').empty();
                        $('.virtual-taster__content__body__result').empty();
                    }).css('display', 'flex');
                });

                $('.js_restart').fadeOut('fast', function () {
                    $(this).html('VOLTAR').removeClass('js_restart').addClass('js_previous_step');
                });

                $('.js_previous_step').fadeOut('fast');

                $('button.js_close').html('PRÓXIMO').removeClass('js_close').addClass('js_next_step').css({
                    opacity: 0.4,
                    cursor: 'not-allowed'
                }).attr('disabled', 'disabled');

                $('.js_next_step').css({
                    opacity: 0.4,
                    cursor: 'not-allowed'
                }).attr('disabled', 'disabled');

                $('form[name="measures"]').find('input[name="height"]').val('');
                $('form[name="measures"]').find('input[name="weight"]').val('');
                $('form[name="measures"]').find('input[name="age"]').val('');
                $('form[name="settings"]').find('input[name="height"]').val('');
                $('form[name="settings"]').find('input[name="weight"]').val('');
                $('form[name="settings"]').find('input[name="age"]').val('');
                $('form[name="settings"]').find('input[name="bust"]').val('M');
                $('form[name="settings"]').find('input[name="waist"]').val('M');
                $('form[name="settings"]').find('input[name="hip"]').val('M');
            });
        });
    });
});