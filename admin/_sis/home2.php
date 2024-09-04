<?php
$AdminLevel = 6;
if (empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

//GET DATES
$StartDate = (!empty($_SESSION['wc_report_date'][0]) ? $_SESSION['wc_report_date'][0] : date("Y-m-01"));
$EndDate = (!empty($_SESSION['wc_report_date'][1]) ? $_SESSION['wc_report_date'][1] : date("Y-m-d"));

//DEFAULT REPORT
$DateStart = new DateTime($StartDate);
$DateEnd = new DateTime(date("Y-m-d", strtotime($EndDate . "+1day")));
$DateInt = new DateInterval("P1D");
$DateInterval = new DatePeriod($DateStart, $DateInt, $DateEnd);
?>
<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-home">Dashboard</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
        </p>
    </div>
</header>

<div class="dashboard_content">

    <article class="box box50">
        <div class="panel">
            <div class="wc_ead_chart_control">
                <div class="wc_ead_chart_range">
                    <form name="class_add" action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="callback" value="Reports"/>
                        <input type="hidden" name="callback_action" value="get_report"/>
                        <input type="hidden" name="report_back" value="home"/>

                        <label class="wc_ead_chart_range_picker">
                            <span>DE:</span><input readonly="readonly" value="<?= date("d/m/Y", strtotime($StartDate)); ?>" name="start_date" type="text" data-language="pt-BR" class="jwc_datepicker_start"/>
                        </label><label class="wc_ead_chart_range_picker">
                            <span>ATÉ:</span><input readonly="readonly" value="<?= date("d/m/Y", strtotime($EndDate)); ?>" name="end_date" type="text" data-language="pt-BR" class="jwc_datepicker_end"/>
                        </label><button class="btn icon-spinner11 icon-notext"></button><img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                    </form>
                </div><div class="wc_ead_chart_change">
                    <span class="icon icon-stats-bars icon-notext jwc_chart_change jwc_area_chart btn btn_blue btn_green"></span>
                    <span class="icon icon-stats-bars2 icon-notext jwc_chart_change jwc_column_chart btn btn_blue"></span>
                    <span class="icon icon-stats-dots icon-notext jwc_chart_change jwc_line_chart btn btn_blue"></span>
                </div>
            </div>
            <div id="jwc_chart_container"></div>

            <?php
            //GET TOTALS
            $Read->FullRead("SELECT count(order_id) as TotalOrders, SUM(order_status = 1 OR order_status = 6) as TotalSales, SUM(CASE WHEN (order_status = 1 OR order_status = 6) THEN order_price ELSE 0 END) AS TotalProfit FROM " . DB_ORDERS . " WHERE date(order_date) >= :start AND date(order_date) <= :end", "start={$StartDate}&end={$EndDate}");
            $TotalOrders = str_pad($Read->getResult()[0]['TotalOrders'], 3, 0, 0);
            $TotalSales = str_pad($Read->getResult()[0]['TotalSales'], 3, 0, 0);
            $TotalProfit = number_format($Read->getResult()[0]['TotalProfit'], '2', ',', '.');
            $TotalConversion = ($TotalOrders >= 1 ? round(($TotalSales * 100) / $TotalOrders) : "0");
            ?>
            <div class="wc_ead_reports_boxes">
                <div class="box box25 wc_ead_reports_total">
                    <div class="box_content">
                        <p class="icon-cart"><?= $TotalOrders; ?></p>
                        <span>Total Pedidos</span>
                    </div>
                </div><div class="box box25 wc_ead_reports_total">
                    <div class="box_content">
                        <p class="icon-checkmark"><?= $TotalSales; ?></p>
                        <span>V. Confirmadas</span>
                    </div>
                </div><div class="box box25 wc_ead_reports_total">
                    <div class="box_content">
                        <p class="icon-filter"><?= $TotalConversion; ?>%</p>
                        <span>T. Conversão</span>
                    </div>
                </div><div class="box box25 wc_ead_reports_total">
                    <div class="box_content">
                        <p>R$ <?= $TotalProfit; ?></p>
                        <span>T. em vendas</span>
                    </div>
                </div>
            </div>
        </div>
    </article>
    <article class="box box50" style="padding: 0;">
        <div class="box box50">
            <div class="panel_header success">
                <span>
                    <a href="javascript:void(0)" class="btn btn_green icon-loop icon-notext" id="loopDashboard"></a>
                </span>
                <h2 class="icon-earth">ONLINE AGORA:</h2>
            </div>
            <div class="panel dashboard_onlinenow">
                <?php
                $Read->FullRead("SELECT count(online_id) AS total from " . DB_VIEWS_ONLINE . " WHERE online_endview >= NOW()");
                echo "<p class='icon-users wc_useronline'>" . str_pad($Read->getResult()[0]['total'], 4, 0, STR_PAD_LEFT) . "</p>";
                ?>
                <a class="icon-target" href="dashboard.php?wc=onlinenow" title="Ver Usuários Online">ACOMPANHAR USUÁRIOS</a>
                <div class="clear"></div>
            </div>

            <div class="panel_header info m_top">
                <h2 class="icon-stats-dots">VISITAS HOJE:</h2>
            </div>
            <div class="panel dashboard_stats">
                <?php
                $Read->ExeRead(DB_VIEWS_VIEWS, "WHERE views_date = date(NOW())");
                if (!$Read->getResult()):
                    echo "<p class='wc_viewsusers'><b>0000</b><span>Usuários</span></p>";
                    echo "<p class='wc_viewsviews'><b>0000</b><span>Visitas</span></p>";
                    echo "<p class='wc_viewspages'><b>0000</b><span>Páginas</span></p>";
                    echo "<h3 class='wc_viewsstats icon-shuffle'><b>0.00</b> Páginas por Visita</h3>";
                else:
                    $Views = $Read->getResult()[0];
                    $Stats = number_format($Views['views_pages'] / $Views['views_views'], 2, '.', '');
                    echo "<p class='wc_viewsusers'><b>" . str_pad($Views['views_users'], 4, 0, STR_PAD_LEFT) . "</b><span>Usuários</span></p>";
                    echo "<p class='wc_viewsviews'><b>" . str_pad($Views['views_views'], 4, 0, STR_PAD_LEFT) . "</b><span>Visitas</span></p>";
                    echo "<p class='wc_viewspages'><b>" . str_pad($Views['views_pages'], 4, 0, STR_PAD_LEFT) . "</b><span>Páginas</span></p>";
                    echo "<h3 class='wc_viewsstats icon-shuffle'><b>{$Stats}</b> Páginas por Visita</h3>";
                endif;
                ?>
                <div class="clear"></div>
            </div>
        </div>
        <div class="box box50">
            <div class="panel_header alert">
                <h2 class="icon-stats-dots">VISITAS NO MÊS:</h2>
            </div>
            <div class="panel dashboard_stats">
                <?php
                $Read->FullRead("SELECT sum(views_users) AS users, sum(views_views) AS views, sum(views_pages) AS pages FROM " . DB_VIEWS_VIEWS . " WHERE year(views_date) = year(NOW()) AND month(views_date) = month(NOW())");
                if (!$Read->getResult()):
                    echo "<p>0000<span>Usuários</span></p>";
                    echo "<p>0000<span>Visitas</span></p>";
                    echo "<p>0000<span>Páginas</span></p>";
                    echo "<h3 class='icon-shuffle'>0.00 Páginas por Visita</h3>";
                else:
                    $mViews = $Read->getResult()[0];
                    $Stats = (!empty($mViews['pages']) ? number_format($mViews['pages'] / $mViews['views'], 2, '.', '') : '0.00');
                    echo "<p>" . str_pad($mViews['users'], 4, 0, STR_PAD_LEFT) . "<span>Usuários</span></p>";
                    echo "<p>" . str_pad($mViews['views'], 4, 0, STR_PAD_LEFT) . "<span>Visitas</span></p>";
                    echo "<p>" . str_pad($mViews['pages'], 4, 0, STR_PAD_LEFT) . "<span>Páginas</span></p>";
                    echo "<h3 class='icon-shuffle'>{$Stats} Páginas por Visita</h3>";
                endif;
                ?>
                <div class="clear"></div>
            </div>

            <div class="panel_header warning m_top">
                <h2 class="icon-stats-dots">VENDAS NO MÊS:</h2>
            </div>
            <div class="panel dashboard_stats">
                <?php
                $Read->FullRead("SELECT count(order_id) AS total FROM " . DB_ORDERS . " WHERE year(order_date) = year(NOW()) AND month(order_date) = month(NOW())");
                $OrderAll = $Read->getResult()[0]['total'];

                $Read->FullRead("SELECT count(order_id) AS total FROM " . DB_ORDERS . " WHERE order_status = 1 OR order_status = 6 AND year(order_date) = year(NOW()) AND month(order_date) = month(NOW())");
                $OrderApp = $Read->getResult()[0]['total'];

                $Read->FullRead("SELECT count(order_id) AS total FROM " . DB_ORDERS . " WHERE order_status = :st AND year(order_date) = year(NOW()) AND month(order_date) = month(NOW())", "st=2");
                $OrderRep = $Read->getResult()[0]['total'];

                $Read->FullRead("SELECT SUM(order_price) AS total FROM " . DB_ORDERS . " WHERE order_status = 1 OR order_status = 6 AND year(order_date) = year(NOW()) AND month(order_date) = month(NOW())");
                $OrderTot = $Read->getResult()[0]['total'];

                echo "<p>" . str_pad($OrderAll, 4, 0, STR_PAD_LEFT) . "<span>Todos</span></p>";
                echo "<p>" . str_pad($OrderApp, 4, 0, STR_PAD_LEFT) . "<span>Aprovados</span></p>";
                echo "<p>" . str_pad($OrderRep, 4, 0, STR_PAD_LEFT) . "<span>Cancelados</span></p>";
                echo "<h3 class='icon-coin-dollar'>R$ " . number_format($OrderTot, '2', ',', '.') . " em vendas</h3>";
                ?>
                <div class="clear"></div>
            </div>
        </div>
        <div class="box box100">
            <div class="panel_header success">
                <h2 class="icon-bubbles4">ÚLTIMOS PEDIDOS:</h2>
            </div>
            <div class="panel dashboard_orders">
                <?php
                $Read->ExeRead(DB_ORDERS, "ORDER BY date(order_date) DESC, order_status DESC LIMIT 3");
                if (!$Read->getResult()):
                    echo Erro("<span class='icon-info al_center'>Ainda Não Existem Pedidos em Seu Site!</span>", E_USER_NOTICE);
                else:
                    foreach ($Read->getResult() as $Order):
                        extract($Order);
                        echo "<p class='order'><span><a title='Detalhes do pedido' href='dashboard.php?wc=orders/order&id={$order_id}'>#" . str_pad($order_id, 7, 0, 0) . "</a></span><span>" . date("d/m/Y", strtotime($order_date)) . "</span><span>R$ " . number_format($order_price, '2', ',', '.') . "</span><span>" . getOrderStatus($order_status) . "</span></p>";
                    endforeach;
                endif;
                ?>
                <div class="clear"></div>
            </div>
        </div>
    </article>

    <?php
    if (APP_SEARCH):
        ?>
        <div class="box box100">
            <div class="panel_header alert">
                <h2 class="icon-search">ÚLTIMAS PESQUISAS (30 DIAS):</h2>
            </div>
            <div class="panel dashboard_search">
                <?php
                $Read->ExeRead(DB_SEARCH, "WHERE search_commit >= date(NOW() - INTERVAL 30 DAY) AND search_publish IS NULL ORDER BY search_commit DESC, search_count DESC LIMIT 5");
                if (!$Read->getResult()):
                    echo Erro("<span class='icon-info al_center'>Seus usuários ainda não pesquisaram em seu site. Assim que isso acontecer você poderá receber dicas de conteúdo pelas pesquisas realizadas!</span>", E_USER_NOTICE);
                    echo "<div class='clear'></div>";
                else:
                    foreach ($Read->getResult() as $Search):
                        extract($Search);
                        $Read->FullRead("SELECT post_id FROM " . DB_POSTS . " WHERE post_status = 1 AND post_date <= NOW() AND (post_title LIKE '%' :s '%' OR post_subtitle LIKE '%' :s '%')", "s={$search_key}");
                        $ResultPosts = $Read->getRowCount();

                        $Read->FullRead("SELECT pdt_id FROM " . DB_PDT . " WHERE pdt_status = 1 AND (pdt_title LIKE '%' :s '%' OR pdt_subtitle LIKE '%' :s '%')", "s={$search_key}");
                        $ResultPdts = $Read->getRowCount();
                        echo "
                            <article>
                               <h1 class='icon-search'><a href='dashboard.php?wc=posts/home&s=" . urlencode($search_key) . "' title='Ver resultados'>{$search_key}</a></h1>
                               <p>DIA " . date('d/m/Y H\hi', strtotime($search_date)) . "</p>
                               <p>" . str_pad($search_count, 4, 0, STR_PAD_LEFT) . " VEZES</p>
                               <p>" . str_pad($ResultPosts + $ResultPdts, 4, 0, STR_PAD_LEFT) . " RESULTADOS</p>
                               <p>
                                    <button class='btn btn_green icon-notext icon-checkmark wc_tooltip j_wc_action' data-callback='Search' data-callback-action='publish' data-value='$search_id'><span class='wc_tooltip_balloon'>Publicar</span></button>
                                    <button class='btn btn_red icon-notext icon-cross wc_tooltip j_wc_action' data-callback='Search' data-callback-action='delete' data-value='$search_id'><span class='wc_tooltip_balloon'>Deletar</span></button>
                               </p>
                            </article>
                        ";
                    endforeach;
                endif;
                ?>
                <a class="dashboard_searchnowlink" href="dashboard.php?wc=searchnow" title="Ver Mais">MAIS PESQUISAS!</a>
                <div class="clear"></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    //ICON REFRESH IN DASHBOARD
    $('#loopDashboard').click(function () {
        Dashboard();
    });

    //DASHBOARD REALTIME
    setInterval(function () {
        Dashboard();
    }, 10000);
</script>
<?php
$getDayChart = array();
$getUserChart = array();
$getEnrollChart = array();
foreach ($DateInterval as $setDayChart):
    //GET DAYS
    $getDayChart[] = "'" . $setDayChart->format('d/m/Y') . "'";

    //GET DAY FOR READ
    $ReadDay = $setDayChart->format('Y-m-d');

    //GET ORDERS
    $Read->FullRead("SELECT count(order_id) AS TotalOrders, SUM(order_status = 1) AS TotalSales, SUM(order_price) AS TotalProfit FROM " . DB_ORDERS . " WHERE date(order_date) = :date", "date={$ReadDay}");
    $getOrderChart[] = ($Read->getResult()[0]['TotalOrders'] ? $Read->getResult()[0]['TotalOrders'] : 0);
    $getSalesChart[] = ($Read->getResult()[0]['TotalSales'] ? $Read->getResult()[0]['TotalSales'] : 0);
endforeach;

$DaysChart = implode(", ", $getDayChart);
$OrderChart = implode(", ", $getOrderChart);
$SalesChart = implode(", ", $getSalesChart);

unset($_SESSION['wc_report_date']);
?>

<script>
    $(function () {
        //DATEPICKER CONFIG
        var wc_datepicker_start = $('.jwc_datepicker_start').datepicker({autoClose: true, maxDate: new Date()}).data('datepicker');
        var wc_datepicker_end = $('.jwc_datepicker_end').datepicker({autoClose: true, maxDate: new Date()}).data('datepicker');

        $('.jwc_datepicker_end').click(function () {
            var DateString = $('.jwc_datepicker_start').val().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            wc_datepicker_end.update('minDate', new Date(DateString[3], DateString[2] - 1, DateString[1]));
            if (!$(this).val()) {
                $(this).val("<?= date("d/m/Y", strtotime($EndDate)); ?>");
            }
        });

        $('.jwc_datepicker_start').click(function () {
            var DateString = $('.jwc_datepicker_end').val().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            wc_datepicker_start.update('maxDate', new Date(DateString[3], DateString[2] - 1, DateString[1]));
            if (!$(this).val()) {
                $(this).val("<?= date("d/m/Y", strtotime($StartDate)); ?>");
            }
        });

        //CHART CONFIG
        var wc_chart = Highcharts.chart('jwc_chart_container', {
            chart: {
                type: 'area',
                spacingBottom: 0,
                spacingTop: 5,
                spacingLeft: 0,
                spacingRight: 20
            },
            title: {
                text: null
            },
            subtitle: {
                text: null
            },
            yAxis: {
                allowDecimals: false,
                title: {
                    text: 'Registros'
                }
            },
            tooltip: {
                useHTML: true,
                shadow: false,
                headerFormat: '<p class="al_center">{point.key}</p><p class="al_center" style="font-size: 2em">{point.y}</p>',
                pointFormat: '<p class="al_center">{series.name}</p><p class="al_center"></p>',
                backgroundColor: '#000',
                borderWidth: 0,
                padding: 20,
                style: {
                    padding: 20,
                    color: '#fff'
                }
            },
            xAxis: {
                categories: [<?= $DaysChart; ?>],
                minTickInterval: 7
            },
            series: [
                {
                    name: 'Pedidos',
                    data: [<?= $OrderChart; ?>],
                    color: '#0E96E5 ',
                    lineColor: '#006699'
                },
                {
                    name: 'Vendas',
                    data: [<?= $SalesChart; ?>],
                    color: '#00B494',
                    lineColor: '#008068'
                }
            ]
        });

        //CHART CHANGE
        $('.jwc_chart_change').click(function () {
            $('.jwc_chart_change').removeClass('btn_green');
            $(this).addClass('btn_green');
        });

        $('.jwc_area_chart').click(function () {
            wc_chart.update({
                chart: {
                    type: 'area'
                }
            });
        });

        $('.jwc_column_chart').click(function () {
            wc_chart.update({
                chart: {
                    type: 'column'
                }
            });
        });

        $('.jwc_line_chart').click(function () {
            wc_chart.update({
                chart: {
                    type: 'line'
                }
            });
        });
    });
</script>