<!DOCTYPE html>
<html lang="pt-br" dir="ltr">
    <head>
        <?php 
            include('inc/config.php'); 
            include('inc/meta-header.php');
        ?>
            <title>Entre em contato</title>
    </head>
    <body>
        <!-- < ?php include_once("inc/analyticstracking.php") ?> -->
        <?php include('inc/navbar.php'); ?>
        <div class="uk-container uk-margin-large-top" style="position: relative; padding-bottom: 15em;">
            <p>Não foi possível encontrar a página solicitada.</p>
            <div class="uk-grid uk-margin-large-bottom" uk-grid>
                <div class="uk-width-1-1@m">
                    <form class="uk-form" action="result.php" method="get">
                        <fieldset>
                            <legend>Realize uma nova pesquisa</legend>
                            <input type="text" placeholder="Pesquise por termo ou autor" class="uk-form-width-medium" name="search_index">                                        
                            <select name="base[]">
                                <option value="all">Todas as bases</option>
                                <option value="Produção científica">Produção científica</option>
                                <option value="Teses e dissertações">Teses e dissertações</option>
                            </select>
                            <button class="uk-button-primary">Buscar</button>                                    
                        </fieldset>
                    </form>
                </div>
            </div>
            <hr>
            
        </div>
        <div style="position: relative; max-width: initial;">
            <?php require 'inc/footer.php'; ?>
        </div>
    </body>
</html>