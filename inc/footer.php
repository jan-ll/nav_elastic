<div class="uk-container uk-margin" style="position: absolute; bottom: 0px; left: 0px; right: 0px; max-width: initial; background: #f8f8f8; margin-bottom: 0px; padding-bottom: 1em;">
	<div class="uk-grid" style="width: 100vw; max-width: 1200px; margin: 0 auto;">
    <div class="uk-width-1-2@m uk-width-1-2@s">
	    <p id="logos-rodape"class="uk-text-small uk-text-left uk-text-left@m">
	    	<a href="http://www.usp.br" target="_blank" rel="noopener noreferrer" style="font-family: arial; font-size:2.5em; font-weight: bold; line-height: 2.5em; color: #123e72">
    			<img src="<?php echo $url_base; ?>/inc/images/usp-logo-png.png" style="width: 2.5em;">
    		</a>
    		<!--<strong>Universidade de São Paulo</strong>-->
        <a href="http://www.aguia.usp.br" target="_blank" rel="noopener noreferrer">
          <span style="width: 5em; font-family: arial; font-size:2.5em; font-weight: bold; line-height: 2.5em; color: #123e72">AGUIA</span>
    		</a>
	    </p>
	</div>
	<div class="uk-width-1-2@m uk-width-1-2@s uk-align-center@s uk-padding-small">
		<div id="sub-menu" class="uk-navbar-container" uk-navbar>
		    <div class="uk-navbar-right">
		        <ul class="uk-navbar-nav">
		        	<li>
	                    <a class="uk-link-muted" href="<?php echo $url_base; ?>/advanced_search.php"><?php echo $t->gettext('Busca técnica'); ?></a>
	                </li>

		        	<li>
	                <?php if (!empty($_SESSION['oauthuserdata'])) : ?>
	                    <!-- < ?php if (in_array($_SESSION['oauthuserdata']->{'loginUsuario'}, $staffUsers)) : ?> -->
	                        <a href="<?php echo $url_base; ?>/tutorial_staff/" target="_blank" rel="noopener noreferrer"><?php echo $t->gettext('Tutorial Staff'); ?></a>
	                    <!-- < ?php else: ?> -->
	                    <!--    <a href="< php echo $url_base; ?>/tutorial/" target="_blank" rel="noopener noreferrer">< ?php echo $t->gettext('Tutorial'); ?></a> -->
	                    <!-- < ?php endif; ?> -->
	                <?php else: ?>
	                    <a href="<?php echo $url_base; ?>/tutorial/" target="_blank" rel="noopener noreferrer"><?php echo $t->gettext('Tutorial'); ?></a>
	                <?php endif; ?>
	                </li>

	                <li>
	                    <a href="<?php echo $url_base; ?>/contact.php"><?php echo $t->gettext('Contato'); ?></a>
	                </li>
		        </ul>
		    </div>
		</div>


	</div>
	<div class="uk-text-center uk-width-1-1 uk-text-center">
	    <p class="uk-text-small ">
	    	<?php echo $t->gettext('Biblioteca Digital de Produção Intelectual da Universidade de São Paulo'); ?> &nbsp;&nbsp;&nbsp; 2012 - <?php echo date("Y"); ?>
	    </p>
	</div>
</div>
</div>
<script>
  $.validate({
      lang : 'pt',
      modules : 'sanitize',
      modules : 'file'
  });  
</script>