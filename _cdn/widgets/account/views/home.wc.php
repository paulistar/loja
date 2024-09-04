<ul class="account_tabs">
    <li>
        <a class="j_account_tab active" href="#profile" title="Meu Perfil">
            <i class='fa fa-user'></i> Meu Perfil
        </a>
    </li>

    <?php
    $Read->LinkResult(DB_USERS_ADDR, 'user_id', $_SESSION['userLogin']['user_id'], '*');
    if ($Read->getResult()):
        foreach ($Read->getResult() as $ADDR):
            ?>
            <li>
                <a class="j_account_tab" href="#address_<?= $ADDR['addr_id']; ?>" title="Meu Endereço">
                    <i class='fa fa-map-marker'></i> Meu Endereço
                </a>
            </li>
            <?php
        endforeach;
    endif;
    ?>
</ul>

<form id="profile" class="account_form j_account_form active" name="account_form" autocomplete="off" action=""
      method="post"
      enctype="multipart/form-data">
    <input type="hidden" name="action" value="wc_user"/>

    <div class="account_form_wrapper">
        <label class="account_form_item">
            <span>
                Foto <i class='fa fa-pencil'></i>
            </span>

            <input class="wc_loadimage" id="account_user_avatar" type="file" name="user_thumb"/>
        </label>

        <label class="account_form_item">
            <span>
                Nome <i class='fa fa-pencil'></i>
            </span>

            <input type="text" name="user_name" value="<?= $_SESSION['userLogin']['user_name']; ?>" required/>
        </label>

        <label class="account_form_item">
            <span>
                Sobrenome <i class='fa fa-pencil'></i>
            </span>

            <input type="text" name="user_lastname" value="<?= $_SESSION['userLogin']['user_lastname']; ?>" required/>
        </label>

        <label class="account_form_item">
            <span>
                E-mail <i class='fa fa-envelope-o'></i>
            </span>

            <input type="text" name="user_email" value="<?= $_SESSION['userLogin']['user_email']; ?>"/>
        </label>

        <label class="account_form_item">
            <span>
                CPF <i class='fa fa-id-card-o'></i>
            </span>

            <input class="formCpf" type="text" name="user_document"
                   value="<?= $_SESSION['userLogin']['user_document']; ?>"/>
        </label>

        <label class="account_form_item">
            <span>
                Gênero <i class='fa fa-transgender'></i>
            </span>

            <select name="user_genre" required>
                <option value="1" <?= ($_SESSION['userLogin']['user_genre'] == 1 ? 'selected' : ''); ?>>Masculino
                </option>
                <option value="2" <?= ($_SESSION['userLogin']['user_genre'] == 2 ? 'selected' : ''); ?>>Feminino
                </option>
            </select>
        </label>

        <label class="account_form_item">
            <span>
                Telefone <i class='fa fa-phone'></i>
            </span>

            <input class="formPhone" type="text" name="user_telephone"
                   value="<?= $_SESSION['userLogin']['user_telephone']; ?>"/>
        </label>

        <label class="account_form_item">
            <span>
                Celular <i class='fa fa-mobile-phone'></i>
            </span>

            <input class="formPhone" type="text" name="user_cell" value="<?= $_SESSION['userLogin']['user_cell']; ?>"/>
        </label>

        <label class="account_form_item">
            <span>
                Senha <i class="fa fa-lock"></i>
            </span>

            <input type="password" name="user_password" placeholder="Nova senha"/>
        </label>

        <div class="account_form_button">
            <button>
                <i class="fa fa-check-circle"></i> Atualizar
            </button>

            <img alt="Recuperando Senha!" title="Recuperando Senha!" src="<?= BASE; ?>/_cdn/widgets/account/load.gif"/>
        </div>
    </div>
</form>

<?php
$Read->LinkResult(DB_USERS_ADDR, 'user_id', $_SESSION['userLogin']['user_id'], '*');
if ($Read->getResult()):
    foreach ($Read->getResult() as $ADDR):
        ?>
        <form id="address_<?= $ADDR['addr_id']; ?>" class="account_form j_account_form" name="account_form" autocomplete="off" action=""
              method="post"
              enctype="multipart/form-data">
            <input type="hidden" name="action" value="address"/>
            <input type="hidden" name="address" value="<?= $ADDR['addr_id']; ?>"/>

            <div class="account_form_wrapper">
                <label class="account_form_item">
                    <span>
                        Nome <i class='fa fa-pencil'></i>
                    </span>

                    <input type="text" name="addr_name" value="<?= $ADDR['addr_name']; ?>" required/>
                </label>

                <label class="account_form_item">
                    <span>
                        CEP <i class='fa fa-pencil'></i>
                    </span>

                    <input class="formCep wc_getCep" type="text" name="addr_zipcode"
                           value="<?= $ADDR['addr_zipcode']; ?>" required/>
                </label>

                <label class="account_form_item">
                    <span>
                        Rua <i class='fa fa-envelope-o'></i>
                    </span>

                    <input class="wc_logradouro" type="text" name="addr_street" value="<?= $ADDR['addr_street']; ?>"/>
                </label>

                <label class="account_form_item">
                    <span>
                        Número <i class='fa fa-id-card-o'></i>
                    </span>

                    <input type="text" name="addr_number" value="<?= $ADDR['addr_number']; ?>"/>
                </label>

                <label class="account_form_item">
                    <span>
                        Complemento <i class='fa fa-transgender'></i>
                    </span>

                    <input class="wc_complemento" type="text" name="addr_complement"
                           value="<?= $ADDR['addr_complement']; ?>"/>
                </label>

                <label class="account_form_item">
                    <span>
                        Bairro <i class='fa fa-phone'></i>
                    </span>

                    <input class="wc_bairro" type="text" name="addr_district" value="<?= $ADDR['addr_district']; ?>"/>
                </label>

                <label class="account_form_item">
                    <span>
                        Cidade <i class='fa fa-mobile-phone'></i>
                    </span>

                    <input class="wc_localidade" type="text" name="addr_city" value="<?= $ADDR['addr_city']; ?>"/>
                </label>

                <label class="account_form_item">
                    <span>
                        UF <i class="fa fa-lock"></i>
                    </span>

                    <input class="wc_uf" type="text" name="addr_state" value="<?= $ADDR['addr_state']; ?>"
                           maxlength="2"/>
                </label>

                <label class="account_form_item">
                    <span>
                        País <i class="fa fa-lock"></i>
                    </span>

                    <input type="text" name="addr_country"
                           value="<?= ($ADDR['addr_country'] ? $ADDR['addr_country'] : 'Brasil'); ?>"/>
                </label>

                <div class="account_form_button">
                    <button>
                        <i class="fa fa-check-circle"></i> Atualizar
                    </button>

                    <img alt="Recuperando Senha!" title="Recuperando Senha!"
                         src="<?= BASE; ?>/_cdn/widgets/account/load.gif"/>
                </div>
            </div>
        </form>
    <?php endforeach; ?>
<?php endif; ?>

<?php
if (empty($_SESSION['userLogin'])):
    die('<h1 style="padding: 50px 0; text-align: center; font-size: 3em; font-weight: 300; color: #C63D3A">Acesso Negado!</h1>');
endif;
