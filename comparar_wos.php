<!DOCTYPE html>
<html lang="pt-br" dir="ltr">
    <head>
        <?php include('inc/functions.php'); ?> 
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>BDPI USP - Comparar registros da Web of Science</title>
        <link rel="shortcut icon" href="inc/images/faviconUSP.ico" type="image/x-icon">
        <!-- <link rel="stylesheet" href="inc/uikit/css/uikit.min.css"> -->
        <link rel="stylesheet" href="inc/uikit/css/uikit.css">
        <link rel="stylesheet" href="inc/css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>        
        <script src="inc/uikit/js/uikit.min.js"></script>
        <script src="inc/uikit/js/components/grid.js"></script>
        <!-- Save as javascript -->
        <script src="http://cdn.jsdelivr.net/g/filesaver.js"></script>
        <script>
              function SaveAsFile(t,f,m) {
                    try {
                        var b = new Blob([t],{type:m});
                        saveAs(b, f);
                    } catch (e) {
                        window.open("data:"+m+"," + encodeURIComponent(t), '_blank','');
                    }
                }
        </script> 
    </head>
    <body>
    <?php include('inc/navbar.php'); ?>
    <div class="uk-container uk-container-center uk-margin-top">   
                
                <h1>CSV da Web of Science</h1>
                
<form action="" method="post" accept-charset="utf-8" enctype="multipart/form-data">
<input type="file" name="file">
<input type="submit" name="btn_submit" value="Upload File" />

        </form>

    
    
    
<?php
if (isset($_FILES['file'])) {    
    $fh = fopen($_FILES['file']['tmp_name'], 'r+');

    $record_scopus = [];
    $record_scopus[] = 'Ano do material pesquisado\\tTipo de material pesquisado\\tTítulo pesquisado\\tDOI pesquisado\\tAutores\\tTipo de material recuperado\\tTítulo recuperado\\tDOI recuperado\\tAutores\\tAno recuperado\\tPontuação\\tID\\tUnidade';
    
    
    while( ($row = fgetcsv($fh, 8192,"\t")) !== FALSE ) {    
         $record_scopus[] = compararRegistrosWos("Artigo",$row[44],$row[8],$row[1],$row[54]);
    }
    
    $record_blob = implode("\\n", $record_scopus);
     
    echo '<br/><br/><br/>
    <button class="ui blue label" onclick="SaveAsFile(\''.$record_blob.'\',\'comparativo_wos.csv\',\'text/plain;charset=utf-8\')">
        Exportar resultado em CSV
    </button>
    ';
    
}
?>
        <hr>
     <?php include('inc/footer.php'); ?>           
           
        </div>
        <?php include('inc/offcanvas.php'); ?>
    </body>
</html>