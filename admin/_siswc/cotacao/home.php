<?php
$AdminLevel = LEVEL_WC_SHIPPING_QUOTE;
if (!APP_SHIPPING_QUOTE || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;
echo "<link rel='stylesheet' href='./_siswc/cotacao/cotacao.wc.css'/>";
echo "<script src='./_siswc/cotacao/cotacao.wc.js'></script>";
echo "<script src='../_cdn/masks.js'></script>";
echo "<script src='../_cdn/jquery.mask.min.js'></script>";
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-truck">Cotação de Frete</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=cotacao/home">Cotação de frete</a>
        </p>
    </div>
</header>

<div class="dashboard_content">
    <form class="jwc_quotation_form" name="wc_send_quotation" action="" method="post">
        <input type="hidden" name="callback" value="Cotacao"/>
        <input type="hidden" name="callback_action" value="quotation"/>

        <div class="box box100">
            <div class="panel_header default">
                <h2 class="icon-truck">Insira as informações para calcular o frete</h2>
            </div>
            <div class="panel">
                <div class="label_33">
                    <label class="label">
                        <span class="legend">CEP Destino:</span>
                        <input type="text" name="cepDestino" class="cep" value="" placeholder="CEP do destino" pattern="\d{5}-\d{3}" required/>
                    </label>
                    <label class="label">
                        <span class="legend">Peso em Kg:</span>
                        <input type="text" name="pesoFinal" class="weight" value="" placeholder="Peso total do pedido em Kg" required/>
                    </label>
                    <label class="label">
                        <span class="legend">Valor da NF:</span>
                        <input type="text" name="vlFinal" class="money" value="" placeholder="Valor total do pedido" required/>
                    </label>
                </div>
                <div id="volume">
                    <span class="legend icon-box-add">Volumes:</span>
                    <div class="label_25">
                        <label class="label">
                            <span class="legend">Largura em metros:</span>
                            <input type="text" name="width[]" class="number" value="" placeholder="Largura do volume" />
                        </label>
                        <label class="label">
                            <span class="legend">Comprimento em metros:</span>
                            <input type="text" name="length[]" class="number" value="" placeholder="Comprimento do volume" />
                        </label>
                        <label class="label">
                            <span class="legend">Altura em metros:</span>
                            <input type="text" name="height[]" class="number" value="" placeholder="Altura do volume" />
                        </label>
                        <label class="label">
                            <span class="legend">Quantidade:</span>
                            <input type="number" name="qtd[]" value="" placeholder="Quantidade de volumes" >
                        </label>
                        <button name="" value="" id="add_field" class="btn btn_green icon-plus">ADICIONAR CAMPO</button>
                    </div>
                </div>
                <div class="label_25">
                    <?php if (ECOMMERCE_SHIPMENT_TNT_QUOTE) { ?>
                        <label class="label_check">
                            <span class="legend">Cotar Transportadora TNT Mercúrio</span>
                            <input type="checkbox" name="tntQuote" value="tntQuote" checked/>
                        </label>
                    <?php } ?>
                    <?php if (ECOMMERCE_SHIPMENT_JAMEF_QUOTE) { ?>
                        <label class="label_check">
                            <span class="legend">Cotar Transportadora Jamef</span>
                            <input type="checkbox" name="jamefQuote" value="jamefQuote" checked/>
                        </label>
                    <?php } ?>
                    <?php if (ECOMMERCE_SHIPMENT_CORREIOS_QUOTE) { ?>
                        <label class="label_check">
                            <span class="legend">Cotar Transportadora Correios</span>
                            <input type="checkbox" name="correiosQuote" value="correiosQuote" checked/>
                        </label>
                    <?php } ?>
                    <?php if (ECOMMERCE_SHIPMENT_JADLOG_QUOTE) { ?>
                        <label class="label_check">
                            <span class="legend">Cotar Transportadora Jadlog</span>
                            <input type="checkbox" name="jadlogQuote" value="jadlogQuote" />
                        </label>
                    <?php } ?>
                    <?php if (ECOMMERCE_SHIPMENT_TAM_QUOTE) { ?>
                        <label class="label_check">
                            <span class="legend">Cotar Transportadora TAM</span>
                            <input type="checkbox" name="tamQuote" value="tamQuote" />
                        </label>
                    <?php } ?>
                </div>

                <div class="m_top">&nbsp;</div>

                <div class="wc_actions" style="text-align: center; margin-bottom: 10px;">
                    <button name="public" value="1" class="btn btn_green  icon-calculator">COTAR FRETE</button>
                    <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                </div>
                <div class="clear"></div>
            </div>
    </form>

    <div class="box box100 cotacao" style="display: none;">
        <div class="panel_header success">
            <h2 class=" icon-calculator">Cotação</h2>
        </div>
        <div class="panel">
            <div class="frete"></div>
        </div>
    </div>


</div>

</div>