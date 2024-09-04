<?php
$AdminLevel = LEVEL_WC_SLIDES;
if (!APP_SLIDE || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

//AUTO DELETE POST TRASH
if (DB_AUTO_TRASH):
    $Delete = new Delete;
    $Delete->ExeDelete(DB_SLIDES, "WHERE slide_image IS NULL AND slide_title IS NULL AND slide_id >= :st", "st=1");
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-camera">Banners Inativos</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=banners/home">Banners</a>
            <span class="crumb">/</span>
            Inativos
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Novo Banner" href="dashboard.php?wc=banners/create" class="btn btn_green icon-plus">Adicionar Banner!</a>
    </div>
</header>

<div class="dashboard_content">
    <?php
    $getPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    $Page = ($getPage ? $getPage : 0);
    $Pager = new Pager('dashboard.php?wc=banners/home&page=', "<<", ">>", 3);
    $Pager->ExePager($Page, 9);
    $Read->ExeRead(DB_BANNERS, "WHERE banner_status = :status ORDER BY banner_date DESC LIMIT :limit OFFSET :offset", "status=0&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
    if (!$Read->getResult()):
        $Pager->ReturnPage();
        Erro("<span class='al_center icon-notification'>Ainda não existem banners cadastrados em seu site. Comece cadastrando o primeiro!</span>", E_USER_NOTICE);
    else:
        foreach ($Read->getResult() as $Banner):
            extract($Banner);
            echo "<article class='box box33 banner_single" . (!$banner_status ? ' inactive' : '') . "' id='{$banner_id}'>
                    <header>
                        <h1><a target='_blank' href='" . BASE . "/{$banner_link}' title='{$banner_title}'>{$banner_title}</a></h1>
                    </header>
                    <div class='box_content'>
                    <img style='font-size: 1.2em; margin: 10px 0 20px 0;' src='" . BASE . "/tim.php?src=uploads/{$banner_image}&w=440&h=310' title='{$banner_title}' alt='{$banner_title}'>
                    <a title='Editar Banner' href='dashboard.php?wc=banners/create&id={$banner_id}' class='icon-notext icon-pencil btn btn_blue'></a>
                    <span rel='banner_single' class='j_delete_action icon-notext icon-cancel-circle btn btn_red' id='{$banner_id}'></span>
                    <span rel='banner_single' callback='Banners' callback_action='delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$banner_id}'>Deletar Banner?</span>
                    </div>
                </article>";
        endforeach;

        $Pager->ExePaginator(DB_BANNERS, "WHERE banner_status = :status", "status=0");
        echo $Pager->getPaginator();
    endif;
    ?>
</div>