<script>
    $(function () {
        //GENERAL
        var path = '<?= BASE; ?>/_cdn/widgets/ecommerce/Moip';
        var ajax = path + '/Ajax.workcontrol.php';

        //INCLUDE MOIP-SDK
        $.getScript(path + '/moip-sdk-js.js');

        //TAB PAYMMENT
        $('.workcontrol_pay_tabs li').click(function () {
            var WorkControlPayTab = $(this).attr('id');
            var WorkControlTab = $(this);
            $('.workcontrol_pay_tabs li').removeClass('active');
            $("form[id!='" + WorkControlPayTab + "']").slideUp(200, function () {
                $("form#" + WorkControlPayTab).slideDown(200);
                WorkControlTab.addClass('active');
            });
        });

        //CARTÃO
        $('#cartao').keyup(function () {
            var Input = $(this);
            var nLEN = $(this).val().length;
            if (parseInt(nLEN) === 4 || parseInt(nLEN) === 10 || parseInt(nLEN) === 16) {
                Input.val(Input.val() + "  ");
            }
        });

        $('#cartao').focusin(function () {
            $('.wc_order_error').fadeOut(100, function () {
                $(this).remove();
            });
        });

        $('#cartao').change(function () {
            var Card = $(this).val().replace(/ /g, '');

            if (Card.length !== parseInt(15) && Card.length !== parseInt(16)) {
                $('#cardInstallmentQuantity').html('<option value="" disabled selected>PARCELAMENTO:</option>');
                $('#cartao').after("<p class='wc_order_error'>&#10008; Numero do cartão inválido. Informe o número do cartão!</p>");
                $('.wc_order_error').fadeIn();
            } else {
                var cc = new Moip.CreditCard({
                    number: Card
                });

                if (cc.cardType()) {
                    $('.workcontrol_cardnumber').css('background-image', 'url(' + path + '/images/' + cc.cardType().toLowerCase() + '.png)');
                } else {
                    $('#cardInstallmentQuantity').html('<option value="" disabled selected>PARCELAMENTO:</option>');
                    $('#cartao').after("<p class='wc_order_error'>&#10008; Cartão inválido. Informe o número do cartão!</p>");
                    $('.wc_order_error').fadeIn();
                }
            }
        });

        //CARDNAME
        $('#nome').keyup(function () {
            $(this).val(function (i, val) {
                return val.toUpperCase();
            });
        });

        //INSTALLMENT
        $('html').on('change', '#cardInstallmentQuantity', function () {
            var installments = $(this).val();
            var Form = $("form#card");

            $.post(ajax, {workcontrol: 'installments', installments: installments}, function (data) {
                if (data.installment) {
                    Form.prepend('<input type="hidden" name="cardInstallmentValue" value="' + data.installment + '"/>');
                } else {
                    $(".workcontrol_load").fadeIn(function () {
                        $('.workcontrol_load_ajax').html("<p class='big'>&#10008; Erro ao processar pagamento!</p><p class='min'>A quantidade de parcelas é maior do que o permitido!</p>");
                        $('.workcontrol_load_content').fadeIn();
                    });
                }
            }, 'json');
        });

        //CREDIT CARD SUBMIT
        $("form#card").submit(function () {
            var Form = $(this);
            var Data = Form.serialize() + "&workcontrol=creditCardData";

            $.ajax({
                url: ajax,
                data: Data,
                type: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $('.wc_order_error').fadeOut(1, function () {
                        $(this).remove();
                    });
                    $('.workcontrol_load').fadeIn(100);
                },
                success: function (data) {
                    if (data.error) {
                        $('.workcontrol_load').fadeOut(100);

                        if (data.field) {
                            Form.find("input[name='" + data.field + "']").after(data.error);
                        } else {
                            var Inputs = Form.find('input, select');
                            Inputs.each(function (index, elem) {
                                if (!elem.value) {
                                    $(this).after(data.error);
                                }
                            });
                        }

                        $('.wc_order_error').fadeIn();
                    } else if (data.triggerError) {
                        $('.workcontrol_load_ajax').html(data.triggerError);
                        $('.workcontrol_load_content').fadeIn(400);
                    } else if (data.success) {
                        $.post(ajax, {workcontrol: 'publicKey'}, function (data) {
                            if (data.publicKey) {
                                var cc = new Moip.CreditCard({
                                    number: $("#cartao").val(),
                                    cvc: $("#cvv").val(),
                                    expMonth: $("#validadeMes").val(),
                                    expYear: $("#validadeAno").val(),
                                    pubKey: data.publicKey
                                });

                                if (cc.isValid()) {
                                    Form.prepend('<input type="hidden" name="cardHash" value="' + cc.hash() + '"/>');
                                }

                                var Data = Form.serialize() + "&workcontrol=creditCard";

                                $.post(ajax, Data, function (data) {
                                    //MODAL ERRORS
                                    if (data.triggerError) {
                                        $('.workcontrol_load_ajax').html(data.triggerError);
                                        $('.workcontrol_load_content').fadeIn();
                                    } else if (data.resume) {
                                        window.location.href = data.resume;
                                    } else {
                                        $('.workcontrol_load').fadeOut(100, function () {
                                            $('.workcontrol_load_ajax').html('');
                                        });
                                    }
                                }, 'json');
                            } else {
                                $(".workcontrol_load").fadeIn(function () {
                                    $('.workcontrol_load_ajax').html("<p class='big'>&#10008; Erro ao processar pagamento!</p><p class='min'>Você pode tentar novamente. Ou entre em contato pelo nosso telefone <?= SITE_ADDR_PHONE_A; ?> e informe que o número do pedido é <?= $order_id; ?>!</p>");
                                    $('.workcontrol_load_content').fadeIn();
                                });
                            }
                        }, 'json');
                    } else {
                        $(".workcontrol_load").fadeIn(function () {
                            $('.workcontrol_load_ajax').html("<p class='big'>&#10008; Erro ao processar pagamento!</p><p class='min'>Você pode tentar novamente. Ou entre em contato pelo nosso telefone <?= SITE_ADDR_PHONE_A; ?> e informe que o número do pedido é <?= $order_id; ?>!</p>");
                            $('.workcontrol_load_content').fadeIn();
                        });
                    }
                }
            });
            return false;
        });

        //BILLET SUBMITE
        $("form#billet").submit(function () {
            var Data = "workcontrol=billet";

            $.ajax({
                url: ajax,
                data: Data,
                type: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $('.wc_order_error').fadeOut(1, function () {
                        $(this).remove();
                    });
                    $('.workcontrol_load').fadeIn(100);
                },
                success: function (data) {
                    if (data.billet) {
                        window.open(data.billet, "popupWindow", "width=960,height=600,scrollbars=yes");
                    }
                    if (data.resume) {
                        window.location.href = data.resume;
                    }
                }
            });

            return false;
        });

        //CLOSE MODAL
        $('html').on('click', '.workcontrol_load_close', function () {
            $('.workcontrol_load').fadeOut(100, function () {
                $('.workcontrol_load_ajax').html('');
                $('.workcontrol_load_content').fadeOut();
            });
        });
    });

    //NUMBER HIT VALID
    function wcIsNumericHit(evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }
</script>