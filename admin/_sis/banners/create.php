<?php
$AdminLevel = LEVEL_WC_BANNERS;
if (!APP_BANNERS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
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

$BannerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($BannerId):
    $Read->ExeRead(DB_BANNERS, "WHERE banner_id = :id", "id={$BannerId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um banner que não existe ou que foi removido recentemente!";
        header('Location: dashboard.php?wc=banners/home');
    endif;
else:
    $BannerCreate = ['banner_date' => date('Y-m-d H:i:s')];
    $Create->ExeCreate(DB_BANNERS, $BannerCreate);
    header('Location: dashboard.php?wc=banners/create&id=' . $Create->getResult());
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-camera"><?= $banner_title ? $banner_title : 'Novo Banner'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=banners/home">Banners</a>
            <span class="crumb">/</span>
            Gerenciar Banner
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Ver Banners!" href="dashboard.php?wc=banners/home" class="btn btn_blue icon-eye">Ver Banners!</a>
        <a title="Novo Banner!" href="dashboard.php?wc=banners/create" class="btn btn_green icon-plus">Adicionar Banner!</a>
    </div>
</header>

<div class="dashboard_content">
    <form name="post_create" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="callback" value="Banners"/>
        <input type="hidden" name="callback_action" value="manager"/>
        <input type="hidden" name="banner_id" value="<?= $banner_id; ?>"/>

        <article class="box box100">
            <div class="panel">
                <div class="slide_create_cover al_center">
                    <div class="upload_progress none">0%</div>
                    <?php
                    $BannerImage = (!empty($banner_image) && file_exists("../uploads/{$banner_image}") && !is_dir("../uploads/{$banner_image}") ? "uploads/{$banner_image}" : 'admin/_img/no_image.jpg');
                    ?>
                    <img class="banner_image post_cover" alt="Capa" title="Capa" src="../tim.php?src=<?= $BannerImage; ?>&w=430&h=310" default="../tim.php?src=<?= $BannerImage; ?>&w=430&h=310"/>
                </div>

                <label class="label m_top">
                    <span class="legend">Capa:</span>
                    <input type="file" class="wc_loadimage" name="banner_image"/>
                </label>

                <label class="label">
                    <span class="legend">Título:</span>
                    <input style="font-size: 1.5em;" type="text" name="banner_title" value="<?= $banner_title; ?>" required/>
                </label>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Página:</span>
                        <select class="j_banner_select_page" name="banner_page" id="<?= $banner_id; ?>">
                            <option value="">Selecione a Página</option>
                            <?php
                            foreach (new DirectoryIterator('../themes/' . THEME) as $file):
                                if ($file->getExtension() === 'php'):
                                    $filename = str_replace('.php', '', $file->getFilename());
                                    $path = $file->getPathname();
                                    $contentFile = file_get_contents($path);
                                    $countLine = substr_count($contentFile, 'count_line_banner');
                                    if ($countLine >= 1):
                                        ?>
                                        <option value="<?= $filename; ?>"<?= ($banner_page == $filename ? ' selected="selected"' : ''); ?>><?= ucfirst($filename); ?></option>
                                        <?php
                                    endif;
                                endif;
                            endforeach;
                            ?>
                        </select>
                    </label>

                    <label class="label">
                        <span class="legend">Linha:</span>
                        <select class="j_banner_select_line" name="banner_line" data-page="<?= $banner_page; ?>">
                            <?php
                            if (!$banner_page):
                                ?>
                                <option value="">Selecione a Página</option>
                                <?php
                            else:
                                foreach (new DirectoryIterator('../themes/' . THEME) as $file):
                                    if ($file->getExtension() === 'php'):
                                        $filename = str_replace('.php', '', $file->getFilename());
                                        $path = $file->getPathname();
                                        $contentFile = file_get_contents($path);
                                        $countLine = substr_count($contentFile, 'count_line_banner');

                                        if ($banner_page == $filename):
                                            for ($i = 1; $i <= $countLine; $i ++):
                                                ?>
                                                <option value="<?= $i; ?>"<?= ($banner_line == $i ? ' selected="selected"' : ''); ?>><?= $i; ?></option>
                                                <?php
                                            endfor;
                                        endif;
                                    endif;
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </label>
                    <div class="clear"></div>
                </div>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Dimensão:</span>
                        <select class="j_banner_select_dimension" name="banner_size">
                            <?php
                            $Read->FullRead("SELECT banner_size FROM " . DB_BANNERS . " WHERE banner_id = :id", "id={$banner_id}");
                            if ($Read->getResult() && !empty($Read->getResult()[0]['banner_size'])):
                                echo "<option value=''>Selecione a Dimensão</option>";
                                echo "<option selected='selected' value='{$Read->getResult()[0]['banner_size']}'>1 ÷ {$Read->getResult()[0]['banner_size']}</option>";
                            else:
                                echo "<option value=''>Selecione a Linha</option>";
                            endif;
                            ?>
                        </select>
                    </label>

                    <label class="label">
                        <span class="legend">Link: (<?= BASE; ?>/<b>destino</b>)</span>
                        <input type="text" name="banner_link" value="<?= $banner_link; ?>" required/>
                    </label>
                    <div class="clear"></div>
                </div>

                <div class="wc_actions" style="text-align: right">
                    <label class="label_check label_publish <?= ($banner_status == 1 ? 'active' : ''); ?>"><input style="margin-top: -1px;" type="checkbox" value="1" name="banner_status" <?= ($banner_status == 1 ? 'checked' : ''); ?>> Publicar Agora!</label>
                    <button name="public" value="1" class="btn btn_green icon-share" style="margin-left: 5px;">Atualizar Banner!</button>
                    <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                </div>
                <div class="clear"></div>
            </div>
        </article>
    </form>
</div>