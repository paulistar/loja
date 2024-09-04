<script>
    $(function () {
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

        var cartTag = null;

        //CARTÃO
        var card = new Card({
            // a selector or DOM element for the form where users will
            // be entering their information
            form: 'form#card', // *required*
            // a selector or DOM element for the container
            // where you want the card to appear
            container: '.card-wrapper', // *required*

            formSelectors: {
                numberInput: '.workcontrol_cardnumber', // optional — default input[name="number"]
//                expiryInput: 'input#expiry', // optional — default input[name="expiry"]
                cvcInput: 'input#cvv', // optional — default input[name="cvc"]
                nameInput: 'input#nome' // optional - defaults input[name="name"]
            },

            width: 200, // optional — default 350px
            formatting: true, // optional - default true

            // Strings for translation - optional
            messages: {
                validDate: 'valid\ndate', // optional - default 'valid\nthru'
                monthYear: 'mm/yyyy', // optional - default 'month/year'
            },

            // Default placeholders for rendered fields - optional
            placeholders: {
                number: '•••• •••• •••• ••••',
                name: 'Nome impresso no cartão',
                expiry: '••/••',
                cvc: '•••'
            },

            masks: {
                cardNumber: '•' // optional - mask card number
            },

            // if true, will log helpful messages for setting up Card
            debug: false // optional - default false
        });

        //CARDNAME
        $('#nome').keyup(function () {
            $(this).val(function (i, val) {
                return val.toUpperCase();
            });
        });

        //CREDIT CARD SUBMIT
        $("form#card").submit(function () {
            var Form = $(this);
            var Data = Form.serialize() + "&workcontrol=creditCardData";

            $.ajax({
                url: '<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/Ajax.workcontrol.php',
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
                        //ID DO CARTÃO
                        var Data = Form.serialize() + "&workcontrol=creditCard";
                        $.post('<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/Ajax.workcontrol.php', Data, function (data) {
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
                }
            });
            return false;
        });

        //BILLET SUBMITE
        $("form#billet").submit(function () {
            var Data = "workcontrol=billet";

            $.ajax({
                url: '<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/Ajax.workcontrol.php',
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

        $('.pagarme_card label').click(function () {
            $('.pagarme_options').remove();
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

    //NUMBER FORMAT - PHP SIMILAR
    function number_format(numero, decimal, decimal_separador, milhar_separador) {
        numero = (numero + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+numero) ? 0 : +numero,
                prec = !isFinite(+decimal) ? 0 : Math.abs(decimal),
                sep = (typeof milhar_separador === 'undefined') ? ',' : milhar_separador,
                dec = (typeof decimal_separador === 'undefined') ? '.' : decimal_separador,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
</script>