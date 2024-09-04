<?php
/**
 * Created by NetBeans.
 * User: ebrahimpleite
 * Date: 20/07/2018
 * Time: 15:23
 */
require_once __DIR__ . '/Pagarme.php';
//
$pagarme = new Payment\Pagarme;

require 'Checkout.workcontrol.php';
?>
<script src="<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/card.js"></script>

<div class="workcontrol_load"><p class="load_message">Aguarde enquanto processamos o pagamento!</p><div class="workcontrol_load_content"><div class="workcontrol_load_ajax"></div><span class="workcontrol_load_close">X Fechar</span></div></div>

<?php if (ECOMMERCE_PAY_SPLIT): ?>
    <ul class="workcontrol_pay_tabs">
        <li class="active" id="card">Cartão de Credito:</li><li id="billet">Boleto Bancário:</li>
    </ul>
    <form id="card" autocomplete="off" name="workcontrol_pagarme" class="workcontrol_pagseguro" action="" method="post">
        <?php
        $Read->ExeRead(DB_USERS_CARDS, "WHERE user_id = :uid", "uid={$_SESSION['userLogin']['user_id']}");
        if ($Read->getResult()):
            echo "<div class='pagarme_card'>";
            echo "<p class='title_card'><strong>Cartões sugeridos</strong></p>";
            foreach ($Read->getResult() AS $Cards):
                $Card = $pagarme->getCreditCard($Cards['card_id']);
                echo "<input id='{$Card->id}' type='radio' name='select_creditcard' value='{$Card->id}' /><label class='drinkcard-cc {$Card->brand}' for='{$Card->id}'>Terminado em {$Card->last_digits} <br><small>Pague em até " . ECOMMERCE_PAY_SPLIT_NUM . "x</small></label>";
            endforeach;
            echo "</div>";
            echo "<p class='title_card pagarme_options'><strong>Pague com um novo cartão</strong></p>";
        endif;
        ?>
        <div class="labelline pagarme_options">
            <label class="label70">
                <span>Número do Cartão:</span>
                <input type="text" name="cardNumber" class="workcontrol_cardnumber" placeholder="•••• •••• •••• ••••" required>
            </label><div class="label30 labelDate">
                <span class="span">Data de Validade:</span>
                <div class="labelline">
                    <div class="month"><input required onkeypress="return wcIsNumericHit(event)" maxlength="2" type="text" name="expirationMonth" id="validadeMes" placeholder="MM"/></div><div class="year"><input required onkeypress="return wcIsNumericHit(event)" maxlength="2" type="text" name="expirationYear" id="validadeAno" placeholder="YY"/></div>
                </div>
            </div>
        </div>
        <div class="labelline pagarme_options">
            <label class="label70">
                <span>Nome Impresso no Cartão:</span>
                <input required type="text" name="cardName" id="nome" placeholder="Nome impresso no cartão:"/>
            </label><div class="label30">
                <label>
                    <span>Código de Segurança:</span>
                    <input required onkeypress="return wcIsNumericHit(event)" id="cvv" maxlength="4" type="text" name="cardCVV" placeholder="CVV:"/>
                </label>
            </div>
        </div>

        <div class="labelline labelactions">
            <label class="label50">
                <select required name="cardInstallmentQuantity" id="cardInstallmentQuantity">
                    <option value="" disabled selected>SELECIONE AS PARCELAS:</option>
                    <?php
                    $installs = $pagarme->getInstallments(str_replace(".", "", $_SESSION['wc_payorder']['order_price']));
                    for ($i = 1; $i < ECOMMERCE_PAY_SPLIT_NUM + 1; ++$i):
                        echo "<option value='" . $installs->installments->{$i}->installment . " " . $installs->installments->{$i}->installment_amount . " " . $installs->installments->{$i}->amount . "'>" . $installs->installments->{$i}->installment . "x R$ " . number_format($installs->installments->{$i}->installment_amount / 100, '2', ',', '.') . ($i <= ECOMMERCE_PAY_SPLIT_ACN ? " - sem juros" : null) . "</option>";
                    endfor;
                    ?>
                </select>
            </label>
            <button class="btn btn_green wc_button_cart fl_right">Comprar Agora!</button>
        </div>
        <div class="ds_none">
            <div class='card-wrapper'></div>
        </div>
    </form>
<?php endif; ?>

<form id="billet" <?= (!ECOMMERCE_PAY_SPLIT ? 'style="display: block;"' : ''); ?> autocomplete="off" name="workcontrol_pagarme" class="workcontrol_pagseguro workcontrol_pagseguro_billet" action="" method="post">
    <div>
        <h3>Detalhes de pagamento:</h3>
        <p>Fique atento(a) ao vencimento do boleto. Você pode pagar o boleto em qualquer banco ou casa lotérica até o dia do vencimento!</p>
        <p>As compras efetuadas com boleto levam até 3 dias úteis para serem compensadas. Este prazo deve ser estimado por você ao prazo de envio do produto!</p>
        <h4>Valor a pagar: <b>R$ <?= number_format($order_price, '2', ',', '.'); ?></b></h4>
    </div>
    <div class="labelline" style="margin-top: 20px;">
        <button class="btn btn_green wc_button_cart">Gerar Boleto!</button>
    </div>
    <div class="clear"></div>
</form>

<style>
    .pagarme_card{
        margin-bottom: 40px;
    }
    .title_card{
        margin-bottom: 10px !important;
    }
    .pagarme_card input{
        position:absolute !important;
        z-index:999 !important;
        -webkit-appearance:none;
        -moz-appearance:none;
        appearance:none;
    }
    .visa{background-image:url(<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/bandeiras/visa_select.png);}
    .mastercard{background-image:url(<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/bandeiras/mastercard_select.png);}
    .elo{background-image:url(<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/bandeiras/elo_select.png);}
    .amex{background-image:url(<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/bandeiras/amex_select.png);}
    .hipercard{background-image:url(<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/bandeiras/hipercard_select.png);}
    .discover{background-image:url(<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/bandeiras/discover_select.png);}
    .dinersclub{background-image:url(<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/bandeiras/diners_select.png);}
    .aura{background-image:url(<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/bandeiras/aura_select.png);}
    .jcb{background-image:url(<?= BASE; ?>/_cdn/widgets/ecommerce/PagarmeWc/bandeiras/jcb_select.png);}

    .pagarme_card input:active +.drinkcard-cc{opacity: .9;}
    .pagarme_card input:checked +.drinkcard-cc{
        background-color: #f2f2f2;
        position: relative;
    }
    .pagarme_card input:checked +.drinkcard-cc::after{
        content: "\f00c";
        font-family: 'FontAwesome';
        display: block;
        position: absolute;
        right: -3px;
        top: -3px;
        background: #00b493;
        color: #fff;
        width: 20px;
        height: 20px;
        text-align: center;
        line-height: 20px;
        border-radius: 50%;
        font-size: 12px;
    }
    .drinkcard-cc{
        cursor:pointer !important;
        background-size:80px !important;
        background-repeat:no-repeat !important;
        background-position: 10px;
        height:70px !important;
        -webkit-transition: all 100ms ease-in !important;
        -moz-transition: all 100ms ease-in !important;
        transition: all 100ms ease-in !important;
        padding: 10px 0 0 100px;
        border: 1px solid #eaeaea;
        border-radius: 5px;
        color: #333;
        text-transform: none;
        margin-top: 10px !important;
    }
    .drinkcard-cc small{
        color: #39b54a;
    }
    .drinkcard-cc:hover{
        background-color: #eaeaea;
    }
    .ds_none{display:none !important}
</style>