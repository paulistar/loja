<?php require 'Checkout.workcontrol.php'; ?>

<div class="workcontrol_load"><p class="load_message">Aguarde enquanto processamos o pagamento!</p><div class="workcontrol_load_content"><div class="workcontrol_load_ajax"></div><span class="workcontrol_load_close">X Fechar</span></div></div>

<?php if (ECOMMERCE_PAY_SPLIT): ?>
    <ul class="workcontrol_pay_tabs">
        <li class="active" id="card">Cartão de Credito:</li><li id="billet">Boleto Bancário:</li>
    </ul>

    <form id="card" autocomplete="off" name="workcontrol_pagseguro" class="workcontrol_pagseguro" action="" method="post">
        <div class="labelline">
            <label class="label70">
                <span>Número do Cartão:</span>
                <input id="cartao" class="workcontrol_cardnumber" type="text" name="cardNumber" maxlength="22" required onkeypress="return wcIsNumericHit(event)"/>
            </label><div class="label30 labelDate">
                <span class="span">Data de Validade:</span>
                <div class="labelline">
                    <div class="month"><input id="validadeMes" type="text" name="cardExpirationMonth" maxlength="2" placeholder="MM" required onkeypress="return wcIsNumericHit(event)"/></div><div class="year"><input id="validadeAno" type="text" name="cardExpirationYear" maxlength="2" placeholder="YY" required onkeypress="return wcIsNumericHit(event)"/></div>
                </div>
            </div>
        </div>

        <div class="labelline">
            <label class="label70">
                <span>Nome Impresso no Cartão:</span>
                <input id="nome" name="cardName" type="text" required/>
            </label><div class="label30">
                <label>
                    <span>Código de Segurança:</span>
                    <input id="cvv" type="text" name="cardCVV" maxlength="4" required onkeypress="return wcIsNumericHit(event)"/>
                </label>
            </div>
        </div>

        <div class="workcontrol_carddata">
            <h3>Dados do Titular do Cartão:</h3>

            <div class="label70">
                <label class="label50 first">
                    <span>CPF:</span>
                    <input class="formCpf" type="text" name="holderDocument" required onkeypress="return wcIsNumericHit(event)"/>
                </label><div class="label50 last">
                    <label>
                        <span>Data de Nascimento:</span>
                        <input class="formDate" type="text" name="holderBirthDate" required onkeypress="return wcIsNumericHit(event)"/>
                    </label>
                </div>
            </div><div class="label30">
                <label>
                    <span>Telefone:</span>
                    <input class="formPhone" type="text" name="holderPhone" required onkeypress="return wcIsNumericHit(event)"/>
                </label>
            </div>
        </div>

        <div class="labelline labelactions">
            <label class="label50">
                <select required name="cardInstallmentQuantity" id="cardInstallmentQuantity">
                    <option value="" disabled selected>PARCELAMENTO:</option>
                    <?php
                    $total = $_SESSION['wc_payorder']['order_price'];

                    if (ECOMMERCE_PAY_SPLIT):
                        $fees = [
                            1 => 0,
                            2 => 4.50,
                            3 => 5.00,
                            4 => 5.50,
                            5 => 6.50,
                            6 => 7.50,
                            7 => 8.50,
                            8 => 9.50,
                            9 => 10.50,
                            10 => 11.50,
                            11 => 12.00,
                            12 => 12.50
                        ];

                        foreach ($fees as $installment => $fee):
                            if ($installment <= ECOMMERCE_PAY_SPLIT_NUM):
                                if ($installment <= ECOMMERCE_PAY_SPLIT_ACN):
                                    $price = number_format($total / $installment, 2, ',', '.');
                                    echo "<option value='{$installment}'>{$installment}x de R$ {$price} - sem juros</option>";
                                else:
                                    $price = number_format(($total * ((100 + $fee) / 100)) / $installment, 2, ',', '.');
                                    echo "<option value='{$installment}'>{$installment}x de R$ {$price} - R$ " . number_format($total * ((100 + $fee) / 100), 2, ',', '.') . "</option>";
                                endif;
                            endif;
                        endforeach;
                    else:
                        $price = number_format($total, 2, ',', '.');
                        echo "<option value='1'>1x de R$ {$price}</option>";
                    endif;
                    ?>
                </select>
            </label>

            <button class="btn btn_green wc_button_cart fl_right">Comprar Agora!</button>
        </div>

        <div class="clear"></div>
    </form>
<?php endif; ?>

<form id="billet" <?= (!ECOMMERCE_PAY_SPLIT ? 'style="display: block;"' : ''); ?> autocomplete="off" name="workcontrol_pagseguro" class="workcontrol_pagseguro workcontrol_pagseguro_billet" action="" method="post">
    <div>
        <h3>Detalhes de pagamento:</h3>
        <p>Fique atento(a) ao vencimento do boleto. Você pode pagar o boleto em qualquer banco ou casa lotérica até o dia do vencimento!</p>
        <p>As compras efetuadas com boleto levam até 3 dias úteis para serem compensadas. Este prazo deve ser estimado por você ao prazo de envio do produto!</p>
        <h4>Valor a pagar: <b>R$ <?= number_format($order_price, '2', ',', '.'); ?></b></h4>
    </div>

    <div class="labelline" style="margin-top: 20px;">
        <button class="btn btn_green wc_button_cart fl_right">Gerar Boleto!</button>
    </div>

    <div class="clear"></div>
</form>