$(function () {
    $(document).ready(function () {
        var campos_max = 5; //max de 5 campos
        var x = 1; // campos iniciais
        $('#add_field').click(function (e) {
            e.preventDefault(); //prevenir novos clicks
            if (x < campos_max) {
                $('#volume').append('<div class="label_25">\
                                <label class="label">\
                                <input type="text" name="width[]" class="number" value="" placeholder="Largura do volume" />\
                                </label>\
                                <label class="label">\
                                <input type="text" name="length[]" class="number" value="" placeholder="Comprimento do volume" />\
                                </label>\
                                <label class="label">\
                                <input type="text" name="height[]" class="number" value="" placeholder="Altura do volume" />\
                                </label>\
                                <label class="label">\
                                <input type="number" name="qtd[]" value="" placeholder="Quantidade de volumes" />\
                                </label>\
                                <a href="#" class="remover_campo icon-cross icon-notext"></a>\
                                </div>');
                x++;
                $(".number").maskMoney({allowNegative: false, thousands: '.', decimal: ',', affixesStay: false});
            }
        });
        // Remover o div anterior
        $('#volume').on("click", ".remover_campo", function (e) {
            e.preventDefault();
            $(this).parent('div').remove();
            x--;
        });
    });

    $('.jwc_quotation_form').submit(function () {
        var Form = $(this);
        var callback = Form.find('input[name="callback"]').val();
        var callback_action = Form.find('input[name="callback_action"]').val();
        Form.find('img').fadeIn(400);
        Form.ajaxSubmit({
            url: './_ajax/' + callback + '.ajax.php',
            data: {callback_action: callback_action},
            dataType: 'json',
            beforeSubmit: function () {
                Form.find('.form_load').fadeIn('fast');
                $('.trigger_ajax').fadeOut('fast');
            }, success: function (data) {
                //REMOVE LOAD
                Form.find('.form_load').fadeOut('slow', function () {
                    Form.find('img').fadeOut(400);
                    if (data.shipping) {
                        $('.cotacao').fadeIn();
                        $('.frete').html(data.shipping).fadeIn();
                    } else {
                        $('.frete').fadeOut();
                    }
                });
            }
        });
        return false;
    });

    $('.cep').on('input', function () {
        var cep = $(this).val().replace(/[^0-9]/g, '');
        if (cep.length === 8) {
            $('.wc_actions button').html('COTAR FRETE');
            $('.cotacao h2').html('Cotação');
            $.get("https://viacep.com.br/ws/" + cep + "/json", function (data) {
                if (!data.erro) {
                    $('.wc_actions button').html('COTAR FRETE (' + data.localidade + ' / ' + data.uf + ')');
                    $('.cotacao h2').html('Cotação com destino para ' + data.localidade + ' / ' + data.uf);
                }
            }, 'json');
        }
    });
});