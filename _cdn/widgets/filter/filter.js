$(function () {
    /*url ajax*/
    var action = $('link[rel="base"]').attr('href') + "/_cdn/widgets/filter/filter.ajax.php";

    /*filter access*/
    $('html, body').on('click', '.j_filter_access', function () {
        if ($('.workcontrol_filter').css('left') !== '-230px') {
            $('.workcontrol_filter').animate({left: '-230px'}, 300);
        } else {
            $('.workcontrol_filter').animate({left: '0'}, 300);
        }

        $.post(action, {history: $(this).attr('data-history'), action: 'filter_access'});

        return false;
    });

    /*label check*/
    $('.j_filter_check').click(function () {
        if (!$(this).find('input').is(':checked')) {
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }
    });

    /*slider jQuery UI*/
    (function () {
        var pdtPrice = $('form[name="workcontrol_filter"]').find('input[name="pdt_price"]');
        var pdtMinPrice = parseInt(pdtPrice.attr('data-min'));
        var pdtMaxPrice = parseInt(pdtPrice.attr('data-max'));

        var pdtStepPrice = parseInt(pdtPrice.attr('data-step'));
        var pdtRangePrice = parseInt(pdtPrice.attr('data-range'));

        $("#slider_price").slider({
            range: true,
            min: pdtMinPrice,
            max: pdtMaxPrice,
            values: [pdtStepPrice, pdtRangePrice],
            slide: function (event, ui) {
                $("#amount").val("R$ " + ui.values[0] + " - R$ " + ui.values[1]);
            },
            change: function (event, ui) {
                pdtPrice.val(ui.values[0] + ',' + ui.values[1]);
                $('form[name="workcontrol_filter"]').trigger('change');
            }
        });

        if (pdtMinPrice === pdtMaxPrice) {
            $("#slider_price").slider("option", "disabled", true);
        }

        $("#amount").val("R$ " + $("#slider_price").slider("values", 0) + " - R$ " + $("#slider_price").slider("values", 1));
    })();

    /*change form*/
    $('html').on('change', 'form[name="workcontrol_filter"]', function () {
        $.post(action, $(this).serialize(), function (data) {
            $('html, body').animate({scrollTop: 0}, 200, function () {
                window.location.href = BASE + '/' + data.redirect;
            });
        }, 'json');
    });
});