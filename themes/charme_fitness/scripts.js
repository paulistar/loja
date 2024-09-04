$(function () {
    /* url */
    var url = BASE + '/themes/charme_fitness/_ajax/control.ajax.php';

    /* image zoom */
    zoom();

    /* highlight */
    if ($('*[class="brush: php;"]').length) {
        $("head").append('<link rel="stylesheet" href="../../_cdn/highlight.min.css">');
        $.getScript('../../_cdn/highlight.min.js', function () {
            $('*[class="brush: php;"]').each(function (i, block) {
                hljs.highlightBlock(block);
            });
        });
    }

    function HeaderRender(Class) {
        var maxHeight = 0;
        $("." + Class + ":visible").each(function () {
            if ($(this).height() > maxHeight) {
                maxHeight = $(this).height();
            }
        }).height(maxHeight);
    }

    $(window).load(function () {
        HeaderRender('auto_height');
        HeaderRender('combo_cart_add');
    });

    /* search */
    $('html').on('keyup focus', '.j_search input', function () {
        var form = $('.j_search');
        var search = $(this).val();

        if (search.length) {
            $.post(url, {action: 'search', search: search}, function (data) {
                if (data.search) {
                    form.find('i').attr('class', 'fa fa-spinner fa-spin');
                    setTimeout(function () {
                        form.find('.realtime_search').html(data.search);
                        if (form.find('.realtime_search').css('display') === 'none') {
                            form.find('.realtime_search').slideDown('fast');
                        }
                        form.find('i').attr('class', 'fa fa-search');
                    }, '500');
                } else {
                    if (form.find('.realtime_search').css('display') !== 'none') {
                        form.find('.realtime_search').slideUp('fast');
                    }
                }
            }, 'json');
        } else {
            if (form.find('.realtime_search').css('display') !== 'none') {
                form.find('.realtime_search').slideUp('fast');
            }
        }
    });

    $('html').on('blur', '.j_search input', function () {
        var form = $('.j_search');

        if (form.find('.realtime_search').css('display') !== 'none') {
            form.find('.realtime_search').slideUp('fast');
        }
    });

    /* owl.carousel.js */
    $('.carousel .owl-carousel').owlCarousel({
        items: 1,
        nav: true,
        loop: true,
        autoplay: true,
        autoplayTimeout: 5000,
        animateOut: 'fadeOut',
        smartSpeed: 1000
    });

    $('.products .owl-carousel').owlCarousel({
        nav: true,
        smartSpeed: 1000,
        responsive: {
            0: {
                items: 1
            },
            550: {
                items: 2,
                margin: 5
            },
            650: {
                items: 2,
                margin: 40
            },
            850: {
                items: 3,
                margin: 10
            },
            1100: {
                items: 4,
                margin: 10
            },
            1400: {
                items: 5,
                margin: 10
            }
        }
    });

    $('.options .owl-carousel').owlCarousel({
        nav: true,
        items: 8,
        margin: 10,
        smartSpeed: 1000,
        responsive: {
            0: {
                items: 2
            },
            550: {
                items: 3
            },
            700: {
                items: 4
            },
            850: {
                items: 5
            },
            1000: {
                items: 6
            },
            1150: {
                items: 7
            },
            1300: {
                items: 8
            }
        }
    });


    /* countdown */
    setInterval(function () {
        $('.countdown').each(function () {
            var data = $(this).data('expire').split(' ');
            var date = data[0].split('-');
            var hour = data[1].split(':');

            var dateEnd = new Date(date[0], parseInt(date[1]) - 1, date[2], hour[0], hour[1], hour[2]);
            dateEnd.setMilliseconds(0);

            var dateNow = new Date();
            dateNow.setMilliseconds(0);

            if (dateEnd.getTime() >= dateNow.getTime()) {
                dateEnd.setDate(dateEnd.getDate() - dateNow.getDate());
                dateEnd.setHours(dateEnd.getHours() - dateNow.getHours());
                dateEnd.setMinutes(dateEnd.getMinutes() - dateNow.getMinutes());
                dateEnd.setSeconds(dateEnd.getSeconds() - dateNow.getSeconds());

                var zero = '0';
                var setDays = (dateEnd.getDate() <= 3 ? zero.concat(dateEnd.getDate()) : '00');
                var setHours = (dateEnd.getHours() < 10 ? zero.concat(dateEnd.getHours()) : dateEnd.getHours());
                var setMinutes = (dateEnd.getMinutes() < 10 ? zero.concat(dateEnd.getMinutes()) : dateEnd.getMinutes());
                var setSeconds = (dateEnd.getSeconds() < 10 ? zero.concat(dateEnd.getSeconds()) : dateEnd.getSeconds());

                $(this).find('.days').html(setDays);
                $(this).find('.hours').html(setHours);
                $(this).find('.minutes').html(setMinutes);
                $(this).find('.seconds').html(setSeconds);
            } else {
                location.reload();
            }
        });
    }, '1000');

    /* action wishlist */
    $('html').on('click', '.j_toggle_wishlist', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if ($(this).hasClass('active')) {
            $(this).attr('title', 'Adicionar aos Favoritos');
        } else {
            $(this).attr('title', 'Remover dos Favoritos');
        }

        $(this).toggleClass('active');
        $(this).find('i').toggleClass('faa-pulse animated');

        $.post(url, {
            action: 'actions_wishlist',
            pdt_id: $(this).attr('data-pdt-id'),
            user_id: $(this).attr('data-user-id')
        }, function (data) {
            if (parseInt(data.total) >= 1) {
                $('.j_menu_wishlist').html(data.total);
                if (!$('.j_menu_wishlist').hasClass('active')) {
                    $('.j_menu_wishlist').addClass('active');
                }
            } else {
                $('.j_menu_wishlist').removeClass('active');
                $('.j_menu_wishlist').empty();
            }
        }, 'json');
    });

    $('html').on('click', '.j_remove_wishlist', function () {
        var product = $(this).parents('.products_item');
        var pdtId = $(this).data('pdt-id');
        var userId = $(this).data('user-id');

        $.post(url, {
            action: 'actions_wishlist',
            pdt_id: pdtId,
            user_id: userId
        }, function (data) {
            if (parseInt(data.total) >= 1) {
                $('.j_menu_wishlist').html(data.total);
                if (!$('.j_menu_wishlist').hasClass('active')) {
                    $('.j_menu_wishlist').addClass('active');
                }
            } else {
                $('.j_menu_wishlist').removeClass('active');
                $('.j_menu_wishlist').empty();
            }

            product.fadeOut(function () {
                $(this).remove();

                if (!$('.wishlist .products_item').length) {
                    location.reload();
                }
            });
        }, 'json');
    });

    /* force login */
    $('html').on('click', '.j_force_login', function (e) {
        e.preventDefault();
        e.stopPropagation();

        $('.force_login').fadeIn('fast', function () {
            $('.force_login_content').fadeIn('fast', function () {
                $('body').css('overflow', 'hidden');
            }).css('margin-top', 'initial');
        }).css({
            display: 'flex',
            'justify-content': 'center',
            'align-items': 'center'
        });
    });

    $('html').on('click', '.j_force_login_close', function () {
        $('.force_login_content').fadeOut('fast', function () {
            $('.force_login').fadeOut('fast', function () {
                $('body').css('overflow', 'initial');
            });
        }).css('margin-top', '-500px');
    });

    /* browsing history */
    $('html').on('click', '.j_browsing_history', function () {
        $.post(url, {action: 'browsing_history', pdt_id: $(this).attr('data-pdt-id')});

        if ($(this).hasClass('j_all')) {
            $('.browsing_history').fadeOut(function () {
                $(this).remove();
            });
        } else {
            $(this).parents('.owl-item').fadeOut(function () {
                $(this).remove();

                if (!$('.browsing_history .owl-item').length) {
                    $('.browsing_history').fadeOut(function () {
                        $(this).remove();
                    });
                }
            });
        }
    });

    $('html').on('click', '.j_clear_browsing_history', function () {
        $.post(url, {action: 'actions_browsing_history', pdt_id: $(this).attr('data-pdt-id')});
    });

    /* open modal product */
    $('html').on('click', '.j_view_product', function () {
        $.post(url, {action: 'get_pdt', pdt_id: $(this).attr('data-pdt-id')}, function (data) {
            if (data.pdt) {
                /* insert content */
                $('body').prepend(data.pdt).css('overflow', 'hidden');

                /* image zoom */
                zoom();

                $('.products_modal').fadeIn('slow', function () {
                    $(this).find('.products_modal_content').fadeIn('slow', function () {
                        /*loading colors*/
                        if ($('.wc_cart_add.wc_online_content .color_content.no_relatives').length) {
                            $('.wc_cart_add.wc_online_content .color_content.no_relatives .image_loading').fadeOut('slow', function () {
                                $('.wc_cart_add.wc_online_content .color_content.no_relatives .boxing_loading').fadeIn('slow');
                            });
                        }

                        /*loading prints*/
                        if ($('.wc_cart_add.wc_online_content .print_content.no_relatives').length) {
                            $('.wc_cart_add.wc_online_content .print_content.no_relatives .image_loading').fadeOut('slow', function () {
                                $('.wc_cart_add.wc_online_content .print_content.no_relatives .boxing_loading').fadeIn('slow');
                            });
                        }

                        /*loading sizes*/
                        if ($('.wc_cart_add.wc_online_content .size_content').length && !$('.wc_cart_add .size_content .image_loading').hasClass('invisible')) {
                            $('.wc_cart_add.wc_online_content .size_content .image_loading').fadeOut('slow', function () {
                                $('.wc_cart_add.wc_online_content .size_content .boxing_loading').fadeIn('slow');
                            });
                        }

                        /*loading result*/
                        if (!$('.wc_cart_add.wc_online_content .result_content .image_loading').hasClass('invisible')) {
                            $('.wc_cart_add.wc_online_content .result_content .image_loading').fadeOut('slow', function () {
                                $('.wc_cart_add.wc_online_content .result_content .boxing_loading').fadeIn('slow');
                            });
                        }
                    });
                });
            }
        }, 'json');

        return false;
    });

    /* close modal product */
    $('html').on('click', '.j_close_modal_pdp', function () {
        $('.products_modal_content').fadeOut('fast', function () {
            $('.products_modal').fadeOut('fast', function () {
                $(this).remove();
                $('body').css('overflow', 'visible');
            });
        });
    });

    /* open search mobile */
    $('html').on('click', '.j_open_search_mobile', function () {
        $(this).toggleClass('active');
        $('.header_mobile_search').slideToggle('fast');

        return false;
    });

    /* open categories mobile */
    $('html').on('click', '.j_open_categories_mobile', function () {
        $(this).toggleClass('active');
        $('.header_mobile_categories').slideToggle('fast');

        return false;
    });

    /* open sub categories mobile */
    $('html').on('click', '.j_cat_open_end_close', function () {
        if ($(this).parent().parent().find('ul').length) {
            $(this).find('i').toggleClass('active');
            $(this).parent().parent().find('> ul').slideToggle('fast');
        }
    });

    /* newsletter */
    $('html').on('submit', 'form[name="newsletter"]', function () {
        var form = $(this);

        var email = form.find('input[name="email"]').val();
        form.find('i').attr('class', 'fa fa-spinner fa-spin');

        $.post(url, {action: 'add_newsletter', email: email}, function (data) {
            setTimeout(function () {
                form.find('i').attr('class', 'fa fa-envelope');
                form.trigger('reset');
            }, '500');

            if (!data.success) {
                $('.alert_newsletter_content').addClass('error').html("<i class='fa fa-close'></i> Informe um e-mail válido!");
            }

            setTimeout(function () {
                $('.alert_newsletter_content').animate({'margin-right': '0'}, 500);
            }, '500');

            setTimeout(function () {
                $('.alert_newsletter_content').animate({'margin-right': '-310px'}, 500);
            }, '5000');
        }, 'json');

        return false;
    });

    /* select gallery */
    $('html').on('click', '.product .j_select_gallery', function () {
        var parent = $(this).parents('.product_image');
        parent.find('.j_select_gallery').removeClass('active');
        $(this).addClass('active');

        var image = $(this).attr('src');
        parent.find('.j_focus_image').attr('src', image).attr('data-zoom', image);

        /* image zoom */
        $('.zoomImg').remove();
        zoom();
    });

    /* scroll to warranty */
    $(function () {
        $('html').on('click', '.exchange_content a', function () {
            $('.product_footer .wc_tab[href="#warranty"]').addClass('wc_active');

            var scroll = $('.product_footer');
            $('html, body').animate({scrollTop: $(scroll).offset().top}, 500);

            return false;
        });
    });

    /*
     COMBO
     */

    $(document).ready(function () {
        $('.combo_cart_add').each(function (index, value) {
            var comboItem = $(this);

            /*loading colors*/
            if (comboItem.find('.color_content.no_relatives').length) {
                comboItem.find('.color_content.no_relatives .image_loading').fadeOut('slow', function () {
                    comboItem.find('.color_content.no_relatives .boxing_loading').fadeIn('slow');
                });
            }

            /*loading prints*/
            if (comboItem.find('.print_content.no_relatives').length) {
                comboItem.find('.print_content.no_relatives .image_loading').fadeOut('slow', function () {
                    comboItem.find('.print_content.no_relatives .boxing_loading').fadeIn('slow');
                });
            }

            /*loading sizes*/
            if (comboItem.find('.size_content').length && !comboItem.find('.size_content .image_loading').hasClass('invisible')) {
                comboItem.find('.size_content .image_loading').fadeOut('slow', function () {
                    comboItem.find('.size_content .boxing_loading').fadeIn('slow');
                });
            }
        });
    });

    /* select color */
    $('html').on('click', '.combo_cart_add .combo_select_color:not(.active)', function () {
        var comboItem = $(this).parents('.combo_cart_add');

        if (!comboItem.find('.size_content').length) {
            comboItem.find('input[name="stock_id"]').prop('checked', false);
            $(this).find('input[name="stock_id"]').prop('checked', true);
        }

        comboItem.find('.combo_select_color').removeClass('active');
        $(this).addClass('active');

        $.post(url, {
            action: 'get_sizes',
            id: $(this).attr('data-pdt-id'),
            color: $(this).attr('data-stock-color')
        }, function (data) {
            if (data.sizes) {
                comboItem.find('.combo_target_sizes').html(data.sizes);

                if (comboItem.find('.size_content .boxing_loading').css('display') === 'none') {
                    comboItem.find('.size_content .image_loading').fadeIn('slow', function () {
                        $(this).fadeOut('slow', function () {
                            comboItem.find('.size_content .boxing_loading').fadeIn('slow');
                        });
                    });
                }
            }
        }, 'json');

        return false;
    });

    /* select print */
    $('html').on('click', '.combo_cart_add .combo_select_print:not(.active)', function () {
        var comboItem = $(this).parents('.combo_cart_add');

        if (!comboItem.find('.size_content').length) {
            comboItem.find('input[name="stock_id"]').prop('checked', false);
            $(this).find('input[name="stock_id"]').prop('checked', true);
        }

        comboItem.find('.combo_select_print').removeClass('active');
        $(this).addClass('active');

        $.post(url, {
            action: 'get_sizes',
            id: $(this).attr('data-pdt-id'),
            print: $(this).attr('data-stock-print')
        }, function (data) {
            if (data.sizes) {
                comboItem.find('.combo_target_sizes').html(data.sizes);

                if (comboItem.find('.size_content .boxing_loading').css('display') === 'none') {
                    comboItem.find('.size_content .image_loading').fadeIn('slow', function () {
                        $(this).fadeOut('slow', function () {
                            comboItem.find('.size_content .boxing_loading').fadeIn('slow');
                        });
                    });
                }
            }
        }, 'json');

        return false;
    });

    /* select size */
    $('html').on('click', '.combo_cart_add .combo_select_size:not(.active)', function () {
        var comboItem = $(this).parents('.combo_cart_add');

        comboItem.find('input[name="stock_id"]').prop('checked', false);
        $(this).find('input[name="stock_id"]').prop('checked', true);

        comboItem.find('.combo_select_size').removeClass('active');
        $(this).addClass('active');

        return false;
    });

    /* add combo to cart */
    $('html').on('click', '.combo .combo_purchase_button', function () {
        var pdtId = $('.combo').attr('data-pdt-id');
        var countError = 0;

        $('.combo_cart_add').each(function (index, value) {
            if (!$(this).find('input[name="stock_id"]').is(':checked')) {
                countError++;
            }
        });

        if (countError >= 1) {
            wcCartTrigger("<div class='trigger trigger_ajax trigger_info'><b>(" + countError + ")</b> Produto" + (countError > 1 ? 's' : '') + " necessita" + (countError > 1 ? 'm' : '') + " definir a cor ou o tamanho ou a estampa!<span class='ajax_close'></span></div>");
        } else {
            var combo = null;

            $('.combo_cart_add').each(function (index, value) {
                if ($(this).find('input[name="stock_id"]').is(':checked')) {
                    if (combo) {
                        combo += ',' + $(this).find('input[name="stock_id"]:checked').val();
                    } else {
                        combo = $(this).find('input[name="stock_id"]:checked').val();
                    }
                }
            });

            if (combo) {
                $.post(url, {
                    action: 'combo_cart_add',
                    pdt_id: pdtId,
                    stock_id: combo,
                    item_amount: 1
                }, function (data) {
                    if (data.cart_amount) {
                        $('.cart_count').html(data.cart_amount);

                        if (!$('.header_mobile_nav .cart_count').hasClass('active')) {
                            $('.header_mobile_nav .cart_count').addClass('active');
                        }

                        $('.wc_cart_manager_info').html("Você adicionou <b>(" + data.cart_amount + ")</b> produtos a sua lista de compras. O que deseja fazer agora?");
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
            }
        }

        return false;
    });

    /* combo hide */
    $('html').on('click', '.j_combo_hide', function () {
        var pdtId = $('.combo').attr('data-pdt-id');
        var item = $(this);

        if ($('.combo .products_item').length > 2) {
            $.post(url, {action: 'combo_hide', pdt_id: pdtId, combo_item: item.attr('data-pdt-id')}, function (data) {
                item.parents('.products_item').fadeOut(function () {
                    $(this).remove();

                    $('.combo').find('.combo_purchase_title').html(data.combo_count);
                    $('.combo').find('.combo_purchase_price').html(data.combo_price);
                    $('.combo').find('.combo_restore').fadeIn('slow');
                });
            }, 'json');
        } else {
            wcCartTrigger("<div class='trigger trigger_ajax trigger_alert'>Desculpe! Seu combo deve ter no mínimo <b>(2)</b> produtos.<span class='ajax_close'></span></div>");
        }
    });

    /* combo restore */
    $('html').on('click', '.j_combo_restore', function () {
        var pdtId = $('.combo').attr('data-pdt-id');

        $.post(url, {action: 'combo_restore', pdt_id: pdtId}, function (data) {
            $('.combo').find('.combo_purchase_title').html(data.combo_count);
            $('.combo').find('.combo_purchase_price').html(data.combo_price);

            $('.combo .products_wrap').fadeTo(500, '0.5', function () {
                $('.combo .products_wrap').html(data.content).fadeTo(500, '1', function () {
                    $('.combo').find('.combo_restore').fadeOut('fast', function () {
                        $('.combo_cart_add').each(function (index, value) {
                            var comboItem = $(this);

                            /*loading colors*/
                            if (comboItem.find('.color_content.no_relatives').length) {
                                comboItem.find('.color_content.no_relatives .image_loading').fadeOut('slow', function () {
                                    comboItem.find('.color_content.no_relatives .boxing_loading').fadeIn('slow');
                                });
                            }

                            /*loading prints*/
                            if (comboItem.find('.print_content.no_relatives').length) {
                                comboItem.find('.print_content.no_relatives .image_loading').fadeOut('slow', function () {
                                    comboItem.find('.print_content.no_relatives .boxing_loading').fadeIn('slow');
                                });
                            }

                            /*loading sizes*/
                            if (comboItem.find('.size_content').length && !comboItem.find('.size_content .image_loading').hasClass('invisible')) {
                                comboItem.find('.size_content .image_loading').fadeOut('slow', function () {
                                    comboItem.find('.size_content .boxing_loading').fadeIn('slow');
                                });
                            }
                        });

                        setTimeout(function () {
                            HeaderRender('auto_height');
                            HeaderRender('combo_cart_add');
                        }, '1000');
                    });
                });
            });
        }, 'json');
    });

    /* triggers alert */
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

    /* image zoom */
    function zoom() {
        $(".image-zoom").wrap('<span style="display: inline-block"></span>').css("display", "block").parent().zoom({
            url: $(this).find("img").attr("data-zoom")
        });
    }
});