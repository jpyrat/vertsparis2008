<?php
// Pour pouvoir styler en appliquant : http://www.sovavsiti.cz/css/hr.html
$GLOBALS['ligne_horizontale'] = "\n<div class='hrspip'><hr class='spip' /></div>\n";

// Ne pas permettre de passer en interface simplifiee
$_GET["set_options"] = $GLOBALS["set_options"] = 'avancees';

# Envoi de mail aux contributeurs d'un forum si reponse a leur message
define('_SUIVI_FORUM_THREAD', true);

$table_des_traitements['TITRE'][]= 'typo(trim(supprimer_numero(%s)))';
$GLOBALS['type_urls'] = 'propres2';

?>