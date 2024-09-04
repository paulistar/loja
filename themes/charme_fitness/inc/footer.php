<?php if (ACTIVE_CAMPAIGN == 1): ?>
    <div class="alert_newsletter">
        <div class="alert_newsletter_content">
            <i class='fa fa-check'></i> E-mail cadastrado com sucesso!
        </div>
    </div>

    <div class="newsletter container">
        <div class="content">
            <article>
                <h1>Assine nossa newsletter</h1>

                <div class="newsletter_title">
                    <div class="newsletter_title_content">
                        <i class="fa fa-envelope-o"></i>
                        <p>Receba um Cupom de R$ 10,00<br/> <span>Assine nossa newsletter e receba via email</span></p>
                    </div>
                </div><div class="newsletter_area_form">
                    <form class="one_input" name="newsletter" method="post" action="">
                        <input class="one_input_field" type="email" name="email" placeholder="Digite seu e-mail" required="required"/>
                        <button class="one_input_button" type="submit">
                            <i class="fa fa-envelope"></i>
                        </button>
                    </form>
                </div>
            </article>

            <div class="clear"></div>
        </div>
    </div>
<?php endif; ?>

<footer class="footer container">
    <section class="content">
        <header>
            <h1>Saiba Mais Sobre a <?= SITE_NAME; ?></h1>
        </header>

        <div class="footer_links box box2">
            <article class="box box3">
                <h1>Institucional</h1>

                <ul>
                    <li><a href="<?= BASE . '/sobre'; ?>" title="Sobre a <?= SITE_NAME; ?>">Sobre a <?= SITE_NAME; ?></a></li>
                    <li><a href="<?= BASE . '/politica-de-privacidade'; ?>" title="Política de Privacidade">Política de Privacidade</a></li>
                    <li><a class="jwc_contact" href="#" title="Fale Conosco">Fale Conosco</a></li>
                </ul>
            </article><article class="box box3">
                <h1>Ajuda e Suporte</h1>

                <ul>
                    <li><a href="<?= BASE . '/perguntas-frequentes'; ?>" title="Perguntas Frequentes">Perguntas Frequentes</a></li>
                    <li><a href="<?= BASE . '/frete-e-envio'; ?>" title="Frete e Envio">Frete e Envio</a></li>
                    <li><a href="<?= BASE . '/trocas-e-devolucoes'; ?>" title="Trocas e Devoluçoes">Trocas e Devoluçoes</a></li>
                </ul>
            </article><article class="box box3">
                <h1>Principais Categorias</h1>

                <ul>
                    <?php
                    $Read->FullRead("SELECT cat_title, cat_name FROM " . DB_PDT_CATS . " WHERE cat_parent IS NULL ORDER BY cat_title ASC");
                    if ($Read->getResult()):
                        foreach ($Read->getResult() as $CAT):
                            ?>
                            <li><a href="<?= BASE . "/produtos/{$CAT['cat_name']}"; ?>" title="<?= $CAT['cat_title']; ?>"><?= $CAT['cat_title']; ?></a></li>
                            <?php
                        endforeach;
                    endif;
                    ?>
                </ul>
            </article>
        </div><div class="footer_contact box box2">
            <article class="footer_contact_content">
                <h1>Precisa de ajuda para comprar?</h1>
                <span>Entre em contato! Nós lhe ajudaremos</span>

                <div class="footer_contact_whatsapp">
                    <i class="icon-whatsapp"></i><?= SITE_ADDR_PHONE_B; ?>
                </div>

                <div class="footer_contact_suport">
                    <p>Horário de Atendimento: Segunda à sábado - 9h às 17h<br/> Atendimento por email: <a class="jwc_contact" href="#" title="Fale Conosco"><?= SITE_ADDR_EMAIL; ?></a></p>
                </div>
            </article>
        </div>

        <div class="footer_about box box2">
            <div class="footer_about_company box">
                <a href="<?= BASE; ?>" title="<?= SITE_NAME; ?>">
                    <img src="<?= INCLUDE_PATH; ?>/images/logo_footer.png" alt="<?= SITE_NAME; ?>" title="<?= SITE_NAME; ?>"/>
                </a>

                <p>A <?= SITE_NAME; ?> vem com uma proposta jovem e moderna, direcionada ao público fitness e também a quem gosta de vestir bem! Oferecemos uma excelente qualidade de produtos, aliado a excelente experiência de compra em nosso site, com as melhores opções com preços incríveis.</p>
                <span>Faça parte do mundo <?= SITE_NAME; ?> e destaque-se.</span>
            </div><div class="footer_about_social box">
                <a href="https://www.facebook.com/<?= SITE_SOCIAL_FB_PAGE; ?>" target="_blank" title="<?= SITE_NAME; ?> no Facebook">
                    <i class="fa fa-facebook"></i>
                </a>
                <a href="https://www.twitter.com/<?= SITE_SOCIAL_TWITTER; ?>" target="_blank" title="<?= SITE_NAME; ?> no Twitter">
                    <i class="fa fa-twitter"></i>
                </a>
                <a href="https://www.instagram.com/<?= SITE_SOCIAL_INSTAGRAM; ?>" target="_blank" title="<?= SITE_NAME; ?> no Instagram">
                    <i class="fa fa-instagram"></i>
                </a>
                <a href="https://plus.google.com/<?= SITE_SOCIAL_GOOGLE_PAGE; ?>" target="_blank" title="<?= SITE_NAME; ?> no Google+">
                    <i class="fa fa-google-plus"></i>
                </a>
            </div>
        </div><div class="footer_payments_and_security box box2">
            <div class="footer_payments_and_security_content">
                <article>
                    <h1>Formas de Pagamento</h1>
                    <img class="payments" src="<?= INCLUDE_PATH; ?>/images/cards.png" alt="Formas de Pagamento" title="Formas de Pagamento"/>
                </article>

                <article>
                    <h1>Segurança</h1>
                    <a href="https://www.comodo.com/" title="Comodo">
                        <img src="<?= INCLUDE_PATH; ?>/images/comodo.png" alt="Comodo" title="Comodo"/>
                    </a>
                </article>
            </div>
        </div>

        <div class="clear"></div>
    </section>

    <div class="footer_copy container">
        <div class="content">
            <p>Copyright ® 2017 <?= SITE_NAME; ?> CNPJ:<?= SITE_ADDR_CNPJ; ?> - Todos os Direitos Reservados - Imagens meramente ilustrativas<br>Os preços, promoções, condições de pagamento, frete e produtos são válidos exclusivamente para compras realizadas via internet.</p>

            <div class="clear"></div>
        </div>
    </div>
</footer>

<!-- WhatsHelp.io widget -->
<script type="text/javascript">
    (function () {
        var options = {
            facebook: "378234449176404", // Facebook page ID
            whatsapp: "+5527999381073", // WhatsApp number
            email: "atendimento@charmefitness.com.br", // Email
            company_logo_url: "//storage.whatshelp.io/widget/af/af41/af415192bd44b95ecc387a4125a7ddc7/18010998_452612618405253_8932281448526586086_n.png", // URL of company logo (png, jpg, gif)
            greeting_message: "Olá, como podemos ajudá-lo? Basta enviar-nos uma mensagem agora para obter assistência.", // Text of greeting message
            call_to_action: "Precisa de ajuda?", // Call to action
            button_color: "#ff36a4", // Color of button
            position: "right", // Position may be 'right' or 'left'
            order: "facebook,whatsapp,email" // Order of buttons
        };
        var proto = document.location.protocol, host = "whatshelp.io", url = proto + "//static." + host;
        var s = document.createElement('script');
        s.type = 'text/javascript';
        s.async = true;
        s.src = url + '/widget-send-button/js/init.js';
        s.onload = function () {
            WhWidgetSendButton.init(host, proto, options);
        };
        var x = document.getElementsByTagName('script')[0];
        x.parentNode.insertBefore(s, x);
    })();
</script>
<!-- /WhatsHelp.io widget -->