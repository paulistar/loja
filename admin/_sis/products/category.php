<?php
$AdminLevel = LEVEL_WC_PRODUCTS;
if (!APP_PRODUCTS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

// AUTO INSTANCE OBJECT CREATE
if (empty($Create)):
    $Create = new Create;
endif;

$CatId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($CatId):
    $Read->ExeRead(DB_PDT_CATS, "WHERE cat_id = :id", "id={$CatId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar uma categoria que não existe ou que foi removida recentemente!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=products/categories');
        exit;
    endif;
else:
    $Date = date('Y-m-d H:i:s');
    $Title = "Nova Categoria - {$Date}";
    $Name = Check::Name($Title);
    $CarCreate = ['cat_name' => $Name, 'cat_created' => $Date];
    $Create->ExeCreate(DB_PDT_CATS, $CarCreate);
    header('Location: dashboard.php?wc=products/category&id=' . $Create->getResult());
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-price-tags"><?= $cat_title ? $cat_title : 'Nova Categoria'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=products/home">Produtos</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=products/categories">Categorias</a>
            <span class="crumb">/</span>
            Gerenciar Categoria
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Ver Categorias!" href="dashboard.php?wc=products/categories" class="btn btn_blue icon-eye">Ver Categorias!</a>
        <a title="Nova Categoria" href="dashboard.php?wc=products/category" class="btn btn_green icon-plus">Adicionar Categoria!</a>
    </div>

</header>

<div class="dashboard_content">
    <div class="box box100">

        <div class="panel_header default">
            <h2 class="icon-price-tags">Dados da Categoria</h2>
        </div>

        <div class="panel">
            <form class="auto_save" name="category_add" action="" method="post" enctype="multipart/form-data">
                <div class="callback_return"></div>
                <input type="hidden" name="callback" value="Products"/>
                <input type="hidden" name="callback_action" value="cat_manager"/>
                <input type="hidden" name="cat_id" value="<?= $CatId; ?>"/>
                <label class="label">
                    <span class="legend">Nome:</span>
                    <input style="font-size: 1.5em;" type="text" name="cat_title" value="<?= $cat_title; ?>" placeholder="Título da Categoria:" required/>
                </label>

                <!----------------------------------
                ######## CUSTOM BY ALISSON #########
                ----------------------------------->
                <div class="category_selection">
                    <span class="category_selection_legend">Setor:</span>
                    <span class="category_selection_title j_open_category_selection">
                        <?php
                        if (empty($cat_parent)):
                            echo 'Esse é um setor!';
                        else:
                            $Read->LinkResult(DB_PDT_CATS, 'cat_id', $cat_parent, 'cat_title');
                            echo $Read->getResult()[0]['cat_title'];
                        endif;
                        ?>
                    </span>

                    <div class="category_selection_content j_category_selection_content">
                        <?php
                        $Read->FullRead("SELECT cat_id, cat_parent, cat_tree, cat_title FROM " . DB_PDT_CATS . " WHERE cat_parent IS NULL ORDER BY cat_title ASC");

                        function loopCat() {
                            global $Read, $cat_id, $cat_parent;
                            if ($Read->getResult()):
                                foreach ($Read->getResult() as $CAT):
                                    $Read->FullRead("SELECT cat_id, cat_parent, cat_tree, cat_title FROM " . DB_PDT_CATS . " WHERE cat_parent = :parent ORDER BY cat_title ASC", "parent={$CAT['cat_id']}");

                                    echo "<li>";
                                    echo "<span>";
                                    echo "<i class='" . ($Read->getResult() ? "icon-plus icon-notext" : "icon-minus icon-notext") . "'></i>";
                                    echo "<input id='radio-category-{$CAT['cat_id']}' class='j_category_selection' type='radio' name='cat_parent' value='{$CAT['cat_id']}' data-title='{$CAT['cat_title']}'" . ($CAT['cat_id'] == $cat_id || $CAT['cat_parent'] == $cat_id || in_array($cat_id, explode(',', $CAT['cat_tree'])) ? ' disabled="disabled"' : '') . ($CAT['cat_id'] == $cat_parent ? ' checked="checked"' : '') . "/>";
                                    echo "<label" . ($CAT['cat_id'] == $cat_id || $CAT['cat_parent'] == $cat_id || in_array($cat_id, explode(',', $CAT['cat_tree'])) ? ' class="disabled"' : '') . " for='radio-category-{$CAT['cat_id']}'>{$CAT['cat_title']}</label>";
                                    echo "</span>";

                                    if ($Read->getResult()):
                                        echo "<ul>";
                                        loopCat();
                                        echo "</ul>";
                                    endif;
                                    echo "</li>";
                                endforeach;
                            endif;
                        }

                        echo "<ul>";
                        echo "<li>";
                        echo "<span>";
                        echo "<i class='icon-plus icon-notext'></i>";
                        echo "<input id='radio-category-0' class='j_category_selection' type='radio' name='cat_parent' value='' data-title='Esse é um setor!'" . (empty($cat_parent) ? ' checked="checked"' : '') . "/>";
                        echo "<label for='radio-category-0'>Esse é um setor!</label>";
                        echo "</span>";
                        echo "</li>";
                        loopCat();
                        echo "</ul>";
                        ?>
                    </div>
                </div>

                <div class="m_top">&nbsp;</div>
                <img class="form_load fl_right none" style="margin-left: 10px; margin-top: 2px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                <button class="btn btn_green icon-price-tags fl_right">Atualizar Categoria!</button>
                <div class="clear"></div>
            </form>
        </div>
    </div>
</div>