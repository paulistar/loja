/* CUSTOM BY ALISSON */
$(document).ready(function () {
    /*
     PRODUCT
     */

    /*loading colors*/
    if ($('.wc_cart_add .color_content.no_relatives').length) {
        $('.wc_cart_add .color_content.no_relatives .image_loading').fadeOut('slow', function () {
            $('.wc_cart_add .color_content.no_relatives .boxing_loading').fadeIn('slow');
        });
    }

    /*loading prints*/
    if ($('.wc_cart_add .print_content.no_relatives').length) {
        $('.wc_cart_add .print_content.no_relatives .image_loading').fadeOut('slow', function () {
            $('.wc_cart_add .print_content.no_relatives .boxing_loading').fadeIn('slow');
        });
    }

    /*loading sizes*/
    if ($('.wc_cart_add .size_content').length && !$('.wc_cart_add .size_content .image_loading').hasClass('invisible')) {
        $('.wc_cart_add .size_content .image_loading').fadeOut('slow', function () {
            $('.wc_cart_add .size_content .boxing_loading').fadeIn('slow');
        });
    }

    /*loading result*/
    if (!$('.wc_cart_add .result_content .image_loading').hasClass('invisible')) {
        $('.wc_cart_add .result_content .image_loading').fadeOut('slow', function () {
            $('.wc_cart_add .result_content .boxing_loading').fadeIn('slow');
        });
    }
});

$(function () {
    var action = $('link[rel="base"]').attr('href') + "/_cdn/widgets/ecommerce/cart.ajax.php";

    // Verifica se houve redirecionamento de abandono de carrinho
    if ($('.redirect').length) {
        var name = $('.redirect_name').attr('data-name');

        if ($('.redirect_coupon').length) {
            var coupon = $('.redirect_coupon').attr('data-coupon');
            wcCartTrigger("<div class='trigger trigger_info'><b>Bem-vindo " + name + ":</b><br/> Obrigado por retornar ao nosso site.<br/> Seu <strong>CUPOM</strong> de <strong>" + coupon + "%</strong> de desconto já foi ativado!</div>");
        } else {
            wcCartTrigger("<div class='trigger trigger_info'><b>Bem-vindo " + name + ":</b><br/> Obrigado por retornar ao nosso site.<br/> Finalize sua compra em nosso site!</div>");
        }
    }

    /* SELECT COLOR */
    /* CUSTOM BY ALISSON */
    $('html').on('click', '.wc_cart_add .wc_select_color:not(.active)', function () {
        var pdtId = $(this).data('pdt-id');

        if (!$('.wc_cart_add .size_content').length) {
            $('input[name="stock_id"]').prop('checked', false);
            $(this).find('input[name="stock_id"]').prop('checked', true);
        }

        var maxInventory = $(this).attr('id');
        $('input[name="item_amount"]').val('1').attr('max', maxInventory);

        $.post(action, {
            action: 'get_total',
            id: pdtId,
            amount: '1',
            price: $(this).attr('data-pdt-price')
        }, function (data) {
            if (data.total) {
                $('.wc_cart_add .wc_target_total').html(data.total);
            }
        }, 'json');

        $('.wc_cart_add .wc_select_color').removeClass('active');
        $(this).addClass('active');

        $.post(action, {
            action: 'get_sizes',
            id: $(this).attr('data-pdt-id'),
            price: $(this).attr('data-pdt-price'),
            color: $(this).attr('data-stock-color')
        }, function (data) {
            if (data.sizes) {
                $('.wc_cart_add .wc_target_sizes').html(data.sizes);

                if ($('.wc_cart_add .size_content .boxing_loading').css('display') === 'none') {
                    $('.wc_cart_add .size_content .image_loading').fadeIn('slow', function () {
                        $(this).fadeOut('slow', function () {
                            $('.wc_cart_add .size_content .boxing_loading').fadeIn('slow');
                        });
                    });
                }

                if ($('.wc_cart_add .result_content .boxing_loading').css('display') !== 'none') {
                    $('.wc_cart_add .result_content .boxing_loading').fadeOut('fast');
                }
            }
        }, 'json');

        if (!$('.wc_cart_add .size_content').length && $('.wc_cart_add .result_content .boxing_loading').css('display') === 'none') {
            $('.wc_cart_add .result_content .image_loading').fadeIn('slow', function () {
                $(this).fadeOut('slow', function () {
                    $('.wc_cart_add .result_content .boxing_loading').fadeIn('slow');
                });
            });
        }

        return false;
    });

    /* SELECT PRINT */
    /* CUSTOM BY ALISSON */
    $('html').on('click', '.wc_cart_add .wc_select_print:not(.active)', function () {
        var pdtId = $(this).data('pdt-id');

        if (!$('.wc_cart_add .size_content').length) {
            $('input[name="stock_id"]').prop('checked', false);
            $(this).find('input[name="stock_id"]').prop('checked', true);
        }

        var maxInventory = $(this).attr('id');
        $('input[name="item_amount"]').val('1').attr('max', maxInventory);

        $.post(action, {
            action: 'get_total',
            id: pdtId,
            amount: '1',
            price: $(this).attr('data-pdt-price')
        }, function (data) {
            if (data.total) {
                $('.wc_cart_add .wc_target_total').html(data.total);
            }
        }, 'json');

        $('.wc_cart_add .wc_select_print').removeClass('active');
        $(this).addClass('active');

        $.post(action, {
            action: 'get_sizes',
            id: $(this).attr('data-pdt-id'),
            price: $(this).attr('data-pdt-price'),
            print: $(this).attr('data-stock-print')
        }, function (data) {
            if (data.sizes) {
                $('.wc_cart_add .wc_target_sizes').html(data.sizes);

                if ($('.wc_cart_add .size_content .boxing_loading').css('display') === 'none') {
                    $('.wc_cart_add .size_content .image_loading').fadeIn('slow', function () {
                        $(this).fadeOut('slow', function () {
                            $('.wc_cart_add .size_content .boxing_loading').fadeIn('slow');
                        });
                    });
                }

                if ($('.wc_cart_add .result_content .boxing_loading').css('display') !== 'none') {
                    $('.wc_cart_add .result_content .boxing_loading').fadeOut('fast');
                }
            }
        }, 'json');

        if (!$('.wc_cart_add .size_content').length && $('.wc_cart_add .result_content .boxing_loading').css('display') === 'none') {
            $('.wc_cart_add .result_content .image_loading').fadeIn('slow', function () {
                $(this).fadeOut('slow', function () {
                    $('.wc_cart_add .result_content .boxing_loading').fadeIn('slow');
                });
            });
        }

        return false;
    });

    /* SELECT SIZE */
    /* CUSTOM BY ALISSON */
    $('html').on('click', '.wc_cart_add .wc_select_size:not(.active)', function () {
        var pdtId = $(this).data('pdt-id');

        $('input[name="stock_id"]').prop('checked', false);
        $(this).find('input[name="stock_id"]').prop('checked', true);

        var maxInventory = $(this).attr('id');
        $('input[name="item_amount"]').val('1').attr('max', maxInventory);

        $.post(action, {
            action: 'get_total',
            id: pdtId,
            amount: '1',
            price: $(this).attr('data-pdt-price')
        }, function (data) {
            if (data.total) {
                $('.wc_cart_add .wc_target_total').html(data.total);
            }
        }, 'json');

        $('.wc_cart_add .wc_select_size').removeClass('active');
        $(this).addClass('active');

        if ($('.wc_cart_add .result_content .boxing_loading').css('display') === 'none') {
            $('.wc_cart_add .result_content .image_loading').fadeIn('fast', function () {
                $(this).fadeOut('fast', function () {
                    $('.wc_cart_add .result_content .boxing_loading').fadeIn('fast');
                });
            });
        }

        return false;
    });

    /* LESS ITEM AMOUNT */
    /* CUSTOM BY ALISSON */
    $('html').on('click', '.wc_cart_less', function () {
        var PdtId = $(this).attr('id');
        var Form = $('.wc_cart_add[id="' + PdtId + '"]');
        var Input = Form.find('input[name="item_amount"]');
        var Step = Input.attr('step');
        var Amount = parseFloat(Input.val()) - parseFloat(Step);

        if (parseFloat(Amount) > 0) {
            Input.val(Amount);

            $.post(action, {
                action: 'get_total',
                id: PdtId,
                amount: Amount,
                price: $(this).attr('data-pdt-price')
            }, function (data) {
                if (data.total) {
                    $('.wc_cart_add .wc_target_total').html(data.total);
                }
            }, 'json');
        }

        return false;
    });

    /* plus ITEM AMOUNT */
    /* CUSTOM BY ALISSON */
    $('html').on('click', '.wc_cart_plus', function () {
        var PdtId = $(this).attr('id');
        var Form = $('.wc_cart_add[id="' + PdtId + '"]');
        var Input = Form.find('input[name="item_amount"]');
        var Step = Input.attr('step');
        var Amount = parseFloat(Input.val()) + parseFloat(Step);

        if (parseFloat(Amount) <= parseFloat(Input.attr('max')) || !Input.attr('max')) {
            Input.val(Amount);

            $.post(action, {
                action: 'get_total',
                id: PdtId,
                amount: Amount,
                price: $(this).attr('data-pdt-price')
            }, function (data) {
                if (data.total) {
                    $('.wc_cart_add .wc_target_total').html(data.total);
                }
            }, 'json');
        }

        return false;
    });

    /* ADD ITEM TO CART */
    /* CUSTOM BY ALISSON */
    $('html').on('submit', '.wc_cart_add', function () {
        var data = $(this).serialize() + "&action=wc_cart_add";
        $.post(action, data, function (data) {
            if (data.cart_amount) {
                $('.cart_count').html(data.cart_amount);

                if (!$('.header_mobile_nav .cart_count').hasClass('active')) {
                    $('.header_mobile_nav .cart_count').addClass('active');
                }
            }
            if (data.cart_product) {
                $('.wc_cart_manager_info b').html(data.cart_product);
                $('.wc_cart_manager').fadeIn(200, function () {
                    $('.wc_cart_manager_content').fadeIn(200);
                });

                $('.wc_cart_close').click(function () {
                    $('.wc_cart_manager_content').fadeOut(200, function () {
                        $('.wc_cart_manager').fadeOut();
                    });
                });
            }

            if (data.cart_price) {
                $('.wc_cart_price span').html(data.cart_price);
            }

            if (data.trigger) {
                wcCartTrigger(data.trigger);
            }
        }, 'json');
        return false;
    });

    /*$('.wc_cart_add').submit(function () {
     var data = $(this).serialize() + "&action=wc_cart_add";
     $.post(action, data, function (data) {
     if (data.cart_amount) {
     $('.cart_count').html(data.cart_amount);
     }
     if (data.cart_product) {
     $('.wc_cart_manager_info b').html(data.cart_product);
     $('.wc_cart_manager').fadeIn(200, function () {
     $('.wc_cart_manager_content').fadeIn(200);
     });
     
     $('.wc_cart_close').click(function () {
     $('.wc_cart_manager_content').fadeOut(200, function () {
     $('.wc_cart_manager').fadeOut();
     });
     });
     }
     
     if (data.cart_price) {
     $('.wc_cart_price span').html(data.cart_price);
     }
     
     if (data.trigger) {
     wcCartTrigger(data.trigger);
     }
     }, 'json');
     return false;
     });*/

    /*$('.wc_cart_size_select').click(function () {
     var maxInventory = $(this).attr('id');
     $('input[name="item_amount"]').val('1').attr('max', maxInventory);
     
     $('.wc_cart_size_select').removeClass('wc_cart_size_select_true');
     $(this).addClass('wc_cart_size_select_true');
     });
     
     if ($('.wc_cart_size_select_true').length) {
     $('input[name="item_amount"]').attr('max', $('.wc_cart_size_select_true').attr('id'));
     }*/

    //CART REMOVE
    $('.wc_cart_remove').click(function () {
        var stock_id = $(this).attr('id');
        $.post(action, {action: 'wc_cart_remove', stock_id: stock_id}, function (data) {
            if (data.trigger) {
                wcCartTrigger(data.trigger);
            }

            if (data.cart_amount) {
                $('.cart_count').html(data.cart_amount);
            }

            $('.workcontrol_cart_list_item_' + stock_id).fadeOut(200, function () {
                $(this).remove();
                if (!$('.workcontrol_cart_list_item').length) {
                    window.location.reload();
                }
            });

            $('.wc_cart_total span').html(data.cart_total);
            $('.wc_cart_price span').html(data.cart_price);
        }, 'json');

        wcZipRecalculate();
    });

    //CART UPDATE CHANGE
    $('.wc_cart_change').focusout(function () {
        alert('focusout');
        var InputChange = $(this);
        InputChange.val(parseInt($(this).val()) - 1);
        $('.wc_cart_change_plus[id="' + InputChange.attr('id') + '"]').click();
    });

    //CART UPDATE PLUS
    $('.wc_cart_change_plus').click(function () {
        var stock_id = $(this).attr('id');
        var input = $('.workcontrol_cart_list_item_' + stock_id).find('input');
        var step = $('.wc_cart_change').attr('step');
        var amount = parseFloat(input.val()) + parseFloat(step);

        if (parseFloat(amount) <= parseFloat(input.attr('max'))) {
            input.val(amount);
        } else {
            input.val(input.attr('max'));
            wcCartTrigger("<div class='trigger trigger_info'><b>OPPSSS:</b> Desculpe, mas só existem " + input.attr('max') + " unidades deste produto em estoque!</div>");
        }

        if (parseFloat(input.val()) < 0 || !parseFloat(input.val())) {
            input.val(step);
        }

        $.post(action, {action: 'wc_cart_change', stock_id: stock_id, item_amount: amount}, function (data) {
            if (data.trigger) {
                wcCartTrigger(data.trigger);
            }

            $('.wc_cart_total span').html(data.cart_total);
            $('.wc_cart_price span').html(data.cart_price);
            $('.wc_item_price_' + stock_id).html(data.cart_item);
        }, 'json');

        wcZipRecalculate();
    });

    //CART UPDATE LESS
    $('.wc_cart_change_less').click(function () {
        var stock_id = $(this).attr('id');
        var input = $('.workcontrol_cart_list_item_' + stock_id).find('input');
        var step = $('.wc_cart_change').attr('step');
        var amount = parseFloat(input.val()) - parseFloat(step);

        if (parseFloat(amount) > 0) {
            input.val(amount);
            $.post(action, {action: 'wc_cart_change', stock_id: stock_id, item_amount: amount}, function (data) {
                if (data.trigger) {
                    wcCartTrigger(data.trigger);
                }

                $('.wc_cart_total span').html(data.cart_total);
                $('.wc_cart_price span').html(data.cart_price);
                $('.wc_item_price_' + stock_id).html(data.cart_item);
            }, 'json');
        }

        wcZipRecalculate();
    });

    /*//PLUS ITEM AMOUNT
     $('.wc_cart_plus').click(function () {
     var Form = $('.wc_cart_add[id="' + $(this).attr('id') + '"]');
     var Input = Form.find('input[name="item_amount"]');
     var Amount = parseInt(Input.val()) + parseInt(1);

     Form.find('.trigger').remove();

     if (parseInt(Amount) <= parseInt(Input.attr('max')) || !Input.attr('max')) {
     Input.val(Amount);
     } else {
     Input.val(Input.attr('max'));
     Form.find('button').last().after("<p class='m_top ds_none trigger trigger_info none'><b>OPPSSS:</b> Temos " + Input.attr('max') + " unidades em estoque!");
     Form.find('.trigger').fadeIn(400);
     }
     return false;
     });
     
     //LESS ITEM AMOUNT
     $('.wc_cart_less').click(function () {
     var Form = $('.wc_cart_add[id="' + $(this).attr('id') + '"]');
     var Input = Form.find('input[name="item_amount"]');
     var Amount = parseInt(Input.val()) - parseInt(1);
     
     Form.find('.trigger').fadeOut(1, function () {
     $(this).remove();
     });
     
     if (parseInt(Amount) >= 1) {
     Input.val(Amount);
     }
     return false;
     });
     
     //CONTROL CART ITEM AMOUNT
     $('.wc_cart_add input[name="item_amount"]').change(function () {
     var wcInputControl = $(this);
     var wcFormControl = $('.wc_cart_add');
     if (wcInputControl.val() > parseInt(wcInputControl.attr('max'))) {
     wcFormControl.find('button:last').after("<p class='m_top ds_none trigger trigger_info'><b>OPPSSS:</b> Temos " + wcInputControl.attr('max') + " unidades em estoque!");
     wcFormControl.find('.trigger').fadeIn(400);
     wcInputControl.val(wcInputControl.attr('max'));
     } else if (wcInputControl.val() < 1) {
     wcFormControl.find('button:last').after("<p class='m_top ds_none trigger trigger_alert'><b>OPPSSS:</b> Você pode adicionar 1 ou mais produtos!");
     wcFormControl.find('.trigger').fadeIn(400);
     wcInputControl.val('1');
     } else if (!$.isNumeric(wcInputControl.val())) {
     wcFormControl.find('button:last').after("<p class='m_top ds_none trigger trigger_error'><b>OPPSSS:</b> Somente números são aceitos neste campo!");
     wcFormControl.find('.trigger').fadeIn(400);
     wcInputControl.val('1');
     }
     });*/

    //CUPOM CALC
    $('.wc_cart_cupom').click(function () {
        var input = $('.wc_cart_cupom_val');
        if (!input.val()) {
            wcCartTrigger("<div class='trigger trigger_info'><b>OPPSSS:</b> Informe o código do cupom para aplicar e aproveitar seu desconto especial!</div>");
        } else {
            $('.wc_cart_total_cupom img').fadeIn();
            $.post(action, {action: 'cart_cupom', cupom_id: input.val()}, function (data) {
                $('.wc_cart_total_cupom img').fadeOut();
                $('.wc_cart_discount span').html(data.cart_cupom);
                $('.wc_cart_price span').html(data.cart_price);

                if (data.trigger) {
                    wcCartTrigger(data.trigger);
                }
            }, 'json');
        }
    });

    //SHIPMENT CALC
    wcZipRecalculate();

    $('.wc_cart_ship').click(function () {
        var input = $('.wc_cart_ship_val');
        if (!input.val()) {
            wcCartTrigger("<div class='trigger trigger_info'><b>OPPSSS:</b> É preciso informar o CEP de destino da encomenda para calcular o frete!</div>");
        } else {
            $('.wc_cart_total_shipment img').fadeIn();
            wcZipRecalculate();
        }
    });

    //SHIPMENT SELECT
    $('html').on('click', '.wc_shipment', function () {
        $('.wc_cart_manager').fadeIn();
        var shipprice = $(this).val();
        var shipcode = $(this).attr('id');

        $.post(action, {
            action: 'cart_shipment_select',
            wc_shipcode: shipcode,
            wc_shipprice: shipprice
        }, function (data) {
            $('.wc_cart_total span').html(data.cart_total);
            $('.wc_cart_shiping span').html(data.cart_ship);
            $('.wc_cart_price span').html(data.cart_price);
            $('.wc_cart_manager').fadeOut();
        }, 'json');
    });

    //USER DATA CAPTURE
    $('.wc_order_email').change(function () {
        var Form = $('.wc_order_login');
        var Input = $(this);

        Form.find('img').fadeIn(400);
        $('.wc_order_error').remove();

        $.post(action, {action: 'wc_order_email', user_email: Input.val()}, function (data) {
            Form.find('img').fadeOut(400);
            if (data.user) {
                Form.find('input[name="user_name"]').val(data.user_name);
                Form.find('input[name="user_lastname"]').val(data.user_lastname);
                Form.find('input[name="user_cell"]').val(data.user_cell);
                if (data.user_document) {
                    Form.find('.labeldocument input').attr('disabled', true);
                    Form.find('.labeldocument').fadeOut(200);
                } else {
                    Form.find('.labeldocument input').attr('disabled', false);
                    Form.find('.labeldocument').fadeIn(200);
                }
            } else {
                Form.find('.labeldocument input').attr('disabled', false);
                Form.find('.labeldocument').fadeIn(200);
            }

            setTimeout(function () {
                Form.find('.btn').click();
            }, 200);

            if (data.error) {
                Input.after(data.error);
                $('.wc_order_error').fadeIn();
            }
        }, 'json');
    });

    //USER FORM SEND
    $('.wc_order_login').submit(function () {
        var Form = $(this);
        var Data = Form.serialize() + "&action=wc_order_user";

        Form.find('img').fadeIn(400);
        $('.wc_order_error').remove();
        $('.wc_order_email_false').val(Form.find('input[name="user_email"]').val());

        $.post(action, Data, function (data) {
            Form.find('img').fadeOut(400);
            if (data.error) {
                if (data.field) {
                    Form.find("input[name='" + data.field + "']").after(data.error);
                } else {
                    var Inputs = Form.find('input');
                    Inputs.each(function (index, elem) {
                        if (!elem.value) {
                            $(this).after(data.error);
                        }
                    });
                }
                $('.wc_order_error').fadeIn();
            }
            if (data.success) {
                Form.find('input').remove();
                window.location.href = data.success;
            }
        }, 'json');
        return false;
    });

    //USER ADDR SET
    $('.wc_order_user_addr').click(function () {
        $('.wc_cart_manager').fadeIn();
        var AddrInput = $(this);
        $('.workcontrol_order_newaddr_form').find('input').val('').removeAttr('checked').attr('disabled', true);
        $.post(action, {action: 'wc_addr_select', addr_id: AddrInput.val()}, function () {
            wcZipRecalculate(AddrInput.attr('id'));
        });
    });

    //USER ADDR GET
    $('.wc_order_zipcode').change(function () {
        var Form = $('.wc_order_create');
        setTimeout(function () {
            Form.find('button').click();
        }, 200);
        wcZipRecalculate();
    });

    //SLIDE FORMS
    $('.wc_addr_form_open').click(function () {
        $('.wc_order_create').find('input[type="text"]').val('');
        $('.wc_order_create').find('input').removeAttr('checked').removeAttr('disabled');
        $('.workcontrol_order_addrs').slideUp(400, function () {
            $('.workcontrol_order_addrs').find('input[type="text"]').val('');
            $('.workcontrol_order_addrs').find('input').removeAttr('checked').attr('disabled', true);
            $('.workcontrol_order_newaddr_form').slideDown(400);
        });
    });

    $('.wc_addr_form_close').click(function () {
        $('.wc_order_create').find('input[type="text"]').val('');
        $('.wc_order_create').find('input').removeAttr('checked').removeAttr('disabled');
        $('.workcontrol_order_newaddr_form').slideUp(400, function () {
            $('.workcontrol_order_newaddr_form').find('input[type="text"]').val('');
            $('.workcontrol_order_newaddr_form').find('input').removeAttr('checked').attr('disabled', true);
            $('.workcontrol_order_addrs').slideDown(400);
        });
    });

    //ORDER CREATE
    $('.wc_order_create').submit(function () {
        var Form = $(this);
        var Data = Form.serialize() + "&action=wc_order_create";
        Form.find('img').fadeIn();

        $.post(action, Data, function (data) {
            if (data.form_error) {
                if (data.field) {
                    Form.find("input[name='" + data.field + "']").after(data.form_error);
                } else {
                    var Inputs = Form.find('input[type="text"][required]');
                    Inputs.each(function (index, elem) {
                        if (!elem.value) {
                            $(this).after(data.form_error);
                        }
                    });
                }
                $('.wc_order_error').fadeIn();
            }

            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                Form.find('img').fadeOut();
            }

            if (data.trigger) {
                wcCartTrigger(data.trigger);
            }

        }, 'json');
        return false;
    });

    //TRIGGERS ALERT
    function wcCartTrigger(Message) {
        if (Message) {
            $('.wc_cart_callback').html('').fadeOut(1).css('right', '-400px');

            $('.wc_cart_callback').html(Message).fadeIn(400);
            $('.wc_cart_callback').animate({'right': '0'}, 100);

            $('.wc_cart_callback').click(function () {
                $(this).fadeOut(400, function () {
                    $(this).html('').css('right', '-400px');
                });
            });
        } else {
            $('.wc_cart_callback').fadeOut(1, function () {
                $(this).html('').css('right', '-400px');
            });
        }
    }

    //SHIPMENT CALC
    function wcZipRecalculate(Shipvalue) {
        var Shipment = (Shipvalue ? Shipvalue : $('.wc_cart_ship_val').val());
        if (Shipment) {
            var input = $('.wc_cart_ship_val');
            $.post(action, {action: 'cart_shipment', zipcode: Shipment}, function (data) {
                $('.wc_cart_total_shipment_result').html(data.cart_shipment);
                $('.wc_cart_total_shipment img').fadeOut();
                $('.wc_cart_total_shipment_tag').fadeIn(0);

                if (data.trigger) {
                    wcCartTrigger(data.trigger);
                }

                if (data.reset) {
                    input.val('');
                }
                $('.wc_cart_manager').fadeOut();
            }, 'json');
        }
    }
});