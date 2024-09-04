<link rel="stylesheet" href="<?= BASE; ?>/_cdn/widgets/virtual-taster/virtual-taster.css"/>
<script src="<?= BASE; ?>/_cdn/widgets/virtual-taster/virtual-taster.js"></script>

<div class="virtual-taster">
    <div class="virtual-taster__content">
        <div class="virtual-taster__content__header">
            <img src="<?= INCLUDE_PATH; ?>/images/logo_mobile.png" alt="<?= SITE_NAME; ?>" title="<?= SITE_NAME; ?>"/>
        </div>

        <div class="virtual-taster__content__body">
            <div class="virtual-taster__content__body__measures">
                <div class="virtual-taster__content__body__measures__image">
                    <img src="<?= BASE; ?>/_cdn/widgets/virtual-taster/images/product.jpg" alt="Body Fitness Frente Única Brilho BY029" title="Body Fitness Frente Única Brilho BY029"/>
                </div>

                <div class="virtual-taster__content__body__measures__fields">
                    <h1>Descubra seu tamanho</h1>
                    <p>Informe os dados abaixo para descobrir seu tamanho e o caimento desta peça em você.</p>

                    <form class="virtual-taster__content__body__measures__fields__wrapper" name="measures" method="post" action="" enctype="multipart/form-data">
                        <label for="height">
                            <span>Altura</span>
                            <input class="js_height" type="text" name="height" autocomplete="off" id="height"/>
                            <span>CM</span>
                        </label>

                        <label for="weight">
                            <span>Peso</span>
                            <input class="js_weight" type="text" name="weight" autocomplete="off" id="weight"/>
                            <span>KG</span>
                        </label>

                        <label for="age">
                            <span>Idade</span>
                            <input class="js_age" type="text" name="age" autocomplete="off" id="age"/>
                            <span>ANOS</span>
                        </label>
                    </form>
                </div>
            </div>

            <div class="virtual-taster__content__body__settings"></div>

            <div class="virtual-taster__content__body__result"></div>
        </div>

        <div class="virtual-taster__content__footer">
            <button class="virtual-taster__content__footer__previous js_previous_step" type="button">Voltar</button>
            <button class="virtual-taster__content__footer__next js_next_step" type="button" disabled="disabled">Próximo</button>
        </div>

        <div class="virtual-taster__content__close js_close"><i class="fa fa-close"></i></div>
    </div>
</div>