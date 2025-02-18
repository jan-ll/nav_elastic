<!DOCTYPE html>
<?php
/**
 * PHP version 7
 * Result page
 *
 * The page for display results of search.
 *
 * @category Search
 * @package  Nav_Elastic
 * @author   Tiago Murakami <tiago.murakami@dt.sibi.usp.br>
 * @license  https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link     https://github.com/SIBiUSP/nav_elastic
 */
require 'inc/config.php';

array_walk_recursive($_GET, function (&$item, $key){
    $item = htmlspecialchars(strip_tags($item),ENT_NOQUOTES);
});

if (isset($_GET["search"])) {
    foreach ($_GET["search"] as $getSearch) {
        $getCleaned[] = htmlspecialchars($getSearch, ENT_QUOTES);
    }
    unset($_GET["search"]);
    $_GET["search"] = $getCleaned;
}

if (isset($fields)) {
    $_GET["fields"] = $fields;
}

$result_get = get::analisa_get($_GET);
$limit = $result_get['limit'];
$page = $result_get['page'];

if (isset($_GET["sort"])) {
    $result_get['query']["sort"][$_GET["sort"]]["unmapped_type"] = "long";
    $result_get['query']["sort"][$_GET["sort"]]["missing"] = "_last";
    $result_get['query']["sort"][$_GET["sort"]]["order"] = "desc";
    $result_get['query']["sort"][$_GET["sort"]]["mode"] = "max";
} else {
    $result_get['query']['sort']['datePublished.keyword']['order'] = "desc";
}

$params = [];
$params["index"] = $index;
$params["type"] = $type;
$params["size"] = $limit;
$params["from"] = $result_get['skip'];
$params["body"] = $result_get['query'];

$cursor = $client->search($params);
$total = $cursor["hits"]["total"];

?>
<html>
<head>
    <?php require 'inc/meta-header.php'; ?>
    <title><?php echo $branch_abrev; ?> - Resultado da busca</title>

    <?php if ($year_result_graph == true) : ?>
        <!-- D3.js Libraries and CSS -->
        <script type="text/javascript" src="inc/jquery/d3.v3.min.js"></script>
        <!-- UV Charts -->
        <script type="text/javascript" src=inc/uvcharts/uvcharts.full.min.js></script>
    <?php endif; ?>

    <!-- Altmetric Script -->
    <script type='text/javascript' src='https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js'></script>

    <!-- PlumX Script -->
    <script type="text/javascript" src="//d39af2mgp1pqhg.cloudfront.net/widget-popup.js"></script>


</head>
<body style="height: 100vh; min-height: 45em; position: relative;">
    <?php require 'inc/navbar.php'; ?>
    <br/><br/><br/>

    <?php
    if (file_exists("inc/analyticstracking.php")) {
        include_once "inc/analyticstracking.php";
    }
    ?>

    <div class="uk-container" style="position: relative; padding-bottom: 15em;">
        <div class="uk-width-1-1@s uk-width-1-1@m">
            <nav class="uk-navbar-container uk-margin" uk-navbar>
                <div class="nav-overlay uk-navbar-left">
                    <a class="uk-navbar-item uk-logo" uk-toggle="target: .nav-overlay; animation: uk-animation-fade" href="#"><?php echo $t->gettext('Clique para uma nova pesquisa'); ?></a>
                </div>
                <div class="nav-overlay uk-navbar-right">
                    <a class="uk-navbar-toggle" uk-search-icon uk-toggle="target: .nav-overlay; animation: uk-animation-fade" href="#"></a>
                </div>
                <div class="nav-overlay uk-navbar-left uk-flex-1" hidden>
                <div class="uk-navbar-item uk-width-expand">
                <form class="uk-search uk-search-navbar uk-width-1-1">
                    <input type="hidden" name="fields[]" value="name">
                    <input type="hidden" name="fields[]" value="author.person.name">
                    <input type="hidden" name="fields[]" value="authorUSP.name">
                    <input type="hidden" name="fields[]" value="about">
                    <input type="hidden" name="fields[]" value="description">
                    <input class="uk-search-input" type="search" name="search[]" placeholder="<?php echo $t->gettext('Nova pesquisa...'); ?>" autofocus>
                    </form>
                </div>

                <a class="uk-navbar-toggle" uk-close uk-toggle="target: .nav-overlay; animation: uk-animation-fade" href="#"></a>

                </div>
            </nav>
        </div>
        <div class="uk-width-1-1@s uk-width-1-1@m">

            <!-- List of filters - Start -->
            <?php if (!empty($_SERVER["QUERY_STRING"])) : ?>
            <p class="uk-margin-top" uk-margin>
                <a class="uk-button uk-button-default uk-button-small" href="index.php"><?php echo $t->gettext('Começar novamente'); ?></a>
                <?php
                if (!empty($_GET["search"])) {
                    foreach ($_GET["search"] as $querySearch) {
                        $querySearchArray[] = $querySearch;
                        $name_field = explode(":", $querySearch);
                        $querySearch = str_replace($name_field[0].":", "", $querySearch);
                        $diff["search"] = array_diff($_GET["search"], $querySearchArray);
                        $url_push = $_SERVER['SERVER_NAME'].$_SERVER["SCRIPT_NAME"].'?'.http_build_query($diff);
                        echo '<a class="uk-button uk-button-default uk-button-small" href="http://'.$url_push.'">'.$querySearch.' <span uk-icon="icon: close; ratio: 1"></span></a>';
                        unset($querySearchArray);
                    }
                }

                if (!empty($_GET["filter"])) {
                    foreach ($_GET["filter"] as $filters) {
                        $filters_array[] = $filters;
                        $name_field = explode(":", $filters);
                        $filters = str_replace($name_field[0].":", "", $filters);
                        $diff["filter"] = array_diff($_GET["filter"], $filters_array);
                        $url_push = $_SERVER['SERVER_NAME'].$_SERVER["SCRIPT_NAME"].'?'.http_build_query($diff);
                        echo '<a class="uk-button uk-button-primary uk-button-small" href="http://'.$url_push.'">Filtrado por: '.$filters.' <span uk-icon="icon: close; ratio: 1"></span></a>';
                        unset($filters_array);
                    }
                }

                if (!empty($_GET["notFilter"])) {
                    foreach ($_GET["notFilter"] as $notFilters) {
                        $notFiltersArray[] = $notFilters;
                        $name_field = explode(":", $notFilters);
                        $notFilters = str_replace($name_field[0].":", "", $notFilters);
                        $diff["notFilter"] = array_diff($_GET["notFilter"], $notFiltersArray);
                        $url_push = $_SERVER['SERVER_NAME'].$_SERVER["SCRIPT_NAME"].'?'.http_build_query($diff);
                        echo '<a class="uk-button uk-button-danger uk-button-small" href="http://'.$url_push.'">Ocultando: '.$notFilters.' <span uk-icon="icon: close; ratio: 1"></span></a>';
                        unset($notFiltersArray);
                    }
                }
                ?>

            </p>
            <?php endif;?>
            <!-- List of filters - End -->
        </div>
        <div class="uk-grid-divider" uk-grid>
            <div class="uk-width-1-4@s uk-width-2-6@m">
                    <!-- Facetas - Início -->
                    <h3><?php echo $t->gettext('Refinar busca'); ?></h3>
                        <hr>
                        <ul class="uk-nav-default uk-nav-parent-icon" uk-nav="multiple: true">
                            <?php
                                $facets = new Facets();
                                $facets->query = $result_get['query'];

                                if (!isset($_GET["search"])) {
                                    $_GET["search"] = null;
                                }

                                $facets->facet("base", 10, $t->gettext('Bases'), null, "_term", $_GET["search"]);
                                $facets->facet("type", 100, $t->gettext('Tipo de material'), null, "_term", $_GET["search"]);
                                $facets->facet("unidadeUSP", 200, $t->gettext('Unidades USP'), null, "_term", $_GET["search"]);
                                $facets->facet("authorUSP.departament", 100, $t->gettext('Departamento'), null, "_term", $_GET["search"]);
                                $facets->facet("author.person.name", 150, $t->gettext('Autores'), null, "_term", $_GET["search"]);
                                $facets->facet("authorUSP.name", 150, $t->gettext('Autores USP'), null, "_term", $_GET["search"]);
                                $facets->facet("datePublished", 120, $t->gettext('Ano de publicação'), "desc", "_term", $_GET["search"]);
                                $facets->facet("about", 50, $t->gettext('Assuntos'), null, "_term", $_GET["search"]);
                                $facets->facet("language", 40, $t->gettext('Idioma'), null, "_term", $_GET["search"]);
                                $facets->facet("isPartOf.name", 50, $t->gettext('Título da fonte'), null, "_term", $_GET["search"]);
                                $facets->facet("publisher.organization.name", 50, $t->gettext('Editora'), null, "_term", $_GET["search"]);
                                $facets->facet("releasedEvent", 50, $t->gettext('Nome do evento'), null, "_term", $_GET["search"]);
                                $facets->facet("country", 200, $t->gettext('País de publicação'), null, "_term", $_GET["search"]);
                                $facets->facet("USP.grupopesquisa", 100, "Grupo de pesquisa", null, "_term", $_GET["search"]);
                                $facets->facet("funder.name", 50, $t->gettext('Agência de fomento'), null, "_term", $_GET["search"]);
                                $facets->facet("USP.indexacao", 50, $t->gettext('Indexado em'), null, "_term", $_GET["search"]);
                            ?>
                            <li class="uk-nav-header"><?php echo $t->gettext('Colaboração institucional'); ?></li>
                            <?php
                                $facets->facet("author.person.affiliation.name", 50, $t->gettext('Afiliação dos autores externos normalizada'), null, "_term", $_GET["search"]);
                                $facets->facet("author.person.affiliation.name_not_found", 50, $t->gettext('Afiliação dos autores externos não normalizada'), null, "_term", $_GET["search"]);
                                $facets->facet("author.person.affiliation.location", 50, $t->gettext('País das instituições de afiliação dos autores externos'), null, "_term", $_GET["search"]);
                            ?>
                            <li class="uk-nav-header"><?php echo $t->gettext('Métricas do periódico'); ?></li>
                            <?php
                                $facets->facet("USP.qualis.qualis.2016.area", 50, $t->gettext('Qualis 2013/2016 - Área'), null, "_term", $_GET["search"]);
                                $facets->facet("USP.qualis.qualis.2016.nota", 50, $t->gettext('Qualis 2013/2016 - Nota'), null, "_term", $_GET["search"]);
                                $facets->facet("USP.qualis.qualis.2016.area_nota", 50, $t->gettext('Qualis 2013/2016 - Área / Nota'), null, "_term", $_GET["search"]);
                            ?>
                            <li class="uk-nav-header"><?php echo $t->gettext('Teses e Dissertações'); ?></li>
                            <?php
                                $facets->facet("inSupportOf", 30, $t->gettext('Tipo de tese'), null, "_term", $_GET["search"]);
                                $facets->facet("USP.areaconcentracao", 100, "Área de concentração", null, "_term", $_GET["search"]);
                                $facets->facet("USP.programa_pos_sigla", 100, "Sigla do Departamento/Programa de Pós Graduação", null, "_term", $_GET["search"]);
                                $facets->facet("USP.programa_pos_nome", 100, "Departamento/Programa de Pós Graduação", null, "_term", $_GET["search"]);
                                $facets->facet("USP.about_BDTD", 50, $t->gettext('Palavras-chave do autor'), null, "_term", $_GET["search"]);
                            ?>
                        </ul>
                        <?php if (!empty($_SESSION['oauthuserdata'])) : ?>
                            <h3 class="uk-panel-title uk-margin-top">Informações administrativas</h3>
                            <ul class="uk-nav-default uk-nav-parent-icon" uk-nav="multiple: true">
                            <hr>
                            <?php
                                $facets->facet("author.person.affiliation.locationTematres", 50, $t->gettext('País Tematres'), null, "_term", $_GET["search"]);
                                $facets->facet("USP.internacionalizacao", 10, "Internacionalização", null, "_term", $_GET["search"]);
                                $facets->facet("USP.fatorimpacto", 100, "Fator de impacto - 590m", null, "_term", $_GET["search"]);
                                $facets->facet("authorUSP.regime_de_trabalho", 50, $t->gettext('Regime de trabalho'), null, "_term", $_GET["search"]);
                                $facets->facet("authorUSP.funcao", 50, $t->gettext('Função'), null, "_term", $_GET["search"]);
                                $facets->facet("USP.CAT.date", 100, "Data de registro e alterações", "desc", "_term", $_GET["search"]);
                                $facets->facet("USP.CAT.cataloger", 100, "Catalogador", "desc", "_count", $_GET["search"]);
                                $facets->facet("authorUSP.codpes", 100, "Número USP", null, "_term", $_GET["search"]);
                                $facets->facet("isPartOf.issn", 100, "ISSN", null, "_term", $_GET["search"]);
                                $facets->facet("doi", 100, "DOI", null, "_term", $_GET["search"]);
                                $facets->facet("USP.crossref.message.funder.name", 50, $t->gettext('Agência de fomento obtida na CrossRef'), null, "_term", $_GET["search"]);
                                $facets->facet("USP.fullTextFiles.name", 10, $t->gettext('Texto completo'), null, "_term", $_GET["search"]);
                                $facets->facet("USP.fullTextFiles.description", 10, $t->gettext('Texto completo - Descrição'), null, "_term", $_GET["search"]);                                                                  
                                $facets->rebuild_facet("author.person.affiliation.name_not_found", 50, $t->gettext('Afiliação dos autores externos não normalizada'), null, "_term", $_GET["search"]);
                            ?>
                            </ul>
                        <?php endif; ?>
                        <!-- Facetas - Fim -->

                        <hr>

                        <!-- Limitar por data - Início -->
                        <form class="uk-text-small">
                            <fieldset>
                                <legend><?php echo $t->gettext('Limitar por data'); ?></legend>
                                <script>
                                    $( function() {
                                    $( "#limitar-data" ).slider({
                                    range: true,
                                    min: 1900,
                                    max: 2030,
                                    values: [ 1900, 2030 ],
                                    slide: function( event, ui ) {
                                        $( "#date" ).val( "datePublished:[" + ui.values[ 0 ] + " TO " + ui.values[ 1 ] + "]" );
                                    }
                                    });
                                    $( "#date" ).val( "datePublished:[" + $( "#limitar-data" ).slider( "values", 0 ) +
                                    " TO " + $( "#limitar-data" ).slider( "values", 1 ) + "]");
                                    } );
                                </script>
                                <p>
                                <label for="date"><?php echo $t->gettext('Selecionar período de tempo'); ?>:</label>
                                <input class="uk-input" type="text" id="date" readonly style="border:0; color:#f6931f;" name="range[]">
                                </p>
                                <div id="limitar-data" class="uk-margin-bottom"></div>
                                <?php if (!empty($_GET["search"])) : ?>
                                    <?php foreach($_GET["search"] as $search_expression): ?>
                                        <input type="hidden" name="search[]" value="<?php echo str_replace('"', '&quot;', $search_expression); ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($_GET["filter"])) : ?>
                                    <?php foreach($_GET["filter"] as $filter_expression): ?>
                                        <input type="hidden" name="filter[]" value="<?php echo str_replace('"', '&quot;', $filter_expression); ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <button class="uk-button uk-button-primary uk-button-small"><?php echo $t->gettext('Limitar datas'); ?></button>
                            </fieldset>
                        </form>
                        <!-- Limitar por data - Fim -->

                <?php if (!empty($_SESSION['oauthuserdata'])) : ?>
                <hr>
                <!-- Exportar resultados -->
                <h3 class="uk-panel-title"><?php echo $t->gettext('Exportar'); ?></h3>
                <p>Limitado aos primeiros 10000 resultados</p>
                <ul>
                    <li><a class="" href="tools/export.php?<?php echo ''.$_SERVER["QUERY_STRING"].'&format=table'; ?>">Exportar resultados em formato tabela</a></li>
                    <li><a class="" href="tools/export.php?<?php echo ''.$_SERVER["QUERY_STRING"].'&format=ris'; ?>">Exportar resultados em formato RIS</a></li>
                    <li><a class="" href="tools/export.php?<?php echo ''.$_SERVER["QUERY_STRING"].'&format=bibtex'; ?>">Exportar resultados em formato Bibtex</a></li>
                </ul>
                <!-- Exportar resultados - Fim -->

                <?php endif; ?>

            </div>

            <div class="uk-width-3-4@s uk-width-4-6@m">

            <!-- Vocabulário controlado - Início -->
            <?php if(isset($_GET["search"])) : ?>
                <?php foreach ($_GET["search"] as $expressao_busca) : ?>
                    <?php if (preg_match("/\babout\b/i", $expressao_busca, $matches)) : ?>
                        <div class="uk-alert-primary" uk-alert>
                        <a class="uk-alert-close" uk-close></a>
                        <?php $assunto = str_replace("about:", "", $expressao_busca); USP::consultar_vcusp(str_replace("\"", "", $assunto)); ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if(isset($_GET["filter"])) : ?>
                <?php foreach ($_GET["filter"] as $expressao_busca) : ?>
                    <?php if (preg_match("/\babout\b/i", $expressao_busca, $matches)) : ?>
                        <div class="uk-alert-primary" uk-alert>
                        <a class="uk-alert-close" uk-close></a>
                        <?php $assunto = str_replace("about:", "", $expressao_busca); USP::consultar_vcusp(str_replace("\"", "", $assunto)); ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <!-- Vocabulário controlado - Fim -->

            <!-- Informações sobre autores USP - Início
            < ?php if(isset($_GET["search"])) : ?>
                < ?php foreach ($_GET["search"] as $expressao_busca_codpes) : ?>
                    < ?php if (preg_match("/\bcodpes\b/i", $expressao_busca_codpes, $matches)) : ?>
                        <div class="uk-alert-primary" uk-alert>
                        <a class="uk-alert-close" uk-close></a>
                        < ?php USP::consultar_codpes($expressao_busca_codpes); ?>
                        </div>
                    < ?php endif; ?>
                < ?php endforeach; ?>
            < ?php endif; ?>
            Informações sobre autores USP - Fim -->

            <!-- Navegador de resultados - Início -->
            <?php ui::pagination($page, $total, $limit, $t); ?>
            <!-- Navegador de resultados - Fim -->

            <hr class="uk-grid-divider">

                <!-- Resultados -->
                <div class="uk-width-1-1 uk-margin-top uk-description-list-divider">
                    <ul class="uk-list uk-list-divider">
                        <?php
                        foreach ($cursor["hits"]["hits"] as $r) {
                            $record = new Record($r, $show_metrics);
                            $record->simpleRecordMetadata($t);
                        }
                        ?>
                    </ul>

                <hr class="uk-grid-divider">

                <!-- Navegador de resultados - Início -->
                <?php ui::pagination($page, $total, $limit, $t); ?>
                <!-- Navegador de resultados - Fim -->

                <!-- Gráfico do ano - Início -->
        <?php if ($year_result_graph == true && $total > 0 ) : ?>
            <div class="uk-alert-primary" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <?php $ano_bar = Results::generateDataGraphBar($result_get['query'], 'datePublished', "_term", 'desc', 'Ano', 10); ?>
                <div id="ano_chart" class="uk-visible@l"></div>
                <script type="text/javascript">
                    var graphdef = {
                        categories : ['<?= $t->gettext('Ano') ?>'],
                        dataset : {
                            '<?= $t->gettext('Ano') ?>' : [<?= $ano_bar; ?>]
                        }
                    }
                    var chart = uv.chart ('Bar', graphdef, {
                        meta : {
                            position: '#ano_chart',
                            caption : '<?= $t->gettext('Ano de publicação') ?>',
                            hlabel : '<?= $t->gettext('Ano') ?>',
                            vlabel : '<?= $t->gettext('registros') ?>'
                        },
                        graph : {
                            orientation : "Vertical"
                        },
                        dimension : {
                            width: 650,
                            height: 110
                        }
                    })
                </script>
                </div>
        <?php endif; ?>
    <!-- Gráfico do ano - Fim -->
            </div>
        </div>
        <hr class="uk-grid-divider">


    </div>

    </div>
    <div style="position: relative; max-width: initial;">
        <?php require 'inc/footer.php'; ?>
    </div>

    <script>
    $('[data-uk-pagination]').on('select.uk.pagination', function(e, pageIndex){
        var url = window.location.href.split('&page')[0];
        window.location=url +'&page='+ (pageIndex+1);
    });
    </script>

<?php require 'inc/offcanvas.php'; ?>

</body>
</html>