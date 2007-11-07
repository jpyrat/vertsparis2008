<?php
//
// Configurateur de mots clefs et de rubriques bas'e sur
// Configurateur Squelette Epona - 2004 Nov 10 - Marc Lebas.
// Realisation : Achille : achille@artisandelatoile.com, merci jacques ;p
//

function refus($msg) {
  echo "<b><font color=red>$msg</font></b><br>";
  echo "Action non commenc�e; rectifier les conditions initiales avant de reprendre<br>";
}

function abandon($msg) {
  echo "<b><font color=red>$msg</font></b><br>";
  echo "Action abandonn�e en cours d'�x�cution<br>";
  echo "Rectifier le probl�me et r�tablir des conditions initiales avant de reprendre<br>";
}

function avertir($msg) { echo "<font color=orange>$msg</font><br>"; }

//
// Fonctions pour mot-cl�s
//
function id_groupe($titre) {
	$result = spip_query("SELECT id_groupe FROM spip_groupes_mots WHERE titre='$titre'");
	if ($row = spip_fetch_array($result)) return $row['id_groupe'];
	return 0;
}

function id_mot($titre) {
	$result = spip_query("SELECT id_mot FROM spip_mots WHERE titre='$titre'");
	if ($row = spip_fetch_array($result)) return $row['id_mot'];
	return 0;
}

function id_rubrique($titre) {
	$result = spip_query("SELECT id_rubrique FROM spip_rubriques WHERE titre='$titre'");
	if ($row = spip_fetch_array($result)) return $row['id_rubrique'];
	return 0;
}

function id_article($titre) {
	$result = spip_query("SELECT id_article FROM spip_articles WHERE titre='$titre'");
	if ($row = spip_fetch_array($result)) return $row['id_article'];
	return 0;
}

function create_groupe($groupe, $descriptif='', $texte='', $unseul='non', $obligatoire='non', $articles='oui', $breves='non', $rubriques='non', $syndic='non', $evenements='non', $minirezo='oui', $comite='oui', $forum='non') {
	$groupe = importer_charset($groupe);
	$id_groupe=id_groupe($groupe);
	$texte = importer_charset($texte);
	$descriptif = importer_charset($descriptif);
	if ($id_groupe == 0) {
		//Cr�ation groupe + mots cl�
		$query = "INSERT INTO spip_groupes_mots SET titre='$groupe', descriptif='$descriptif', texte='$texte', unseul='$unseul', obligatoire='$obligatoire',
			articles='$articles', breves='$breves', rubriques='$rubriques', syndic='$syndic', evenements='$evenements',
			minirezo='$minirezo', comite='$comite', forum='$forum'";
		spip_query($query);
		$id_groupe = spip_insert_id();
	} else {
		// Mise � jour
		spip_query("UPDATE spip_groupes_mots SET descriptif='$descriptif', texte='$texte', unseul='$unseul', obligatoire='$obligatoire',
			articles='$articles', breves='$breves', rubriques='$rubriques', syndic='$syndic', evenements='$evenements',
			minirezo='$minirezo', comite='$comite', forum='$forum' WHERE id_groupe=$id_groupe");
	}
	$groupe = stripslashes($groupe);
	global $ul_ouvert;
	if ($ul_ouvert)	{
		echo "</ul>\n";
		$ul_ouvert = false;
	}
	echo "<h2>Groupe: $groupe (<a href='?exec=mots_type&amp;id_groupe=$id_groupe'>$id_groupe</a>)</h2>\n";
	return $id_groupe;
}

function create_mot($groupe, $mot, $descriptif='', $texte='') {
	$groupe = importer_charset($groupe);
	$id_groupe=id_groupe($groupe);
	$mot = importer_charset($mot);
	$texte = importer_charset($texte);
	$descriptif = importer_charset($descriptif);
	if ($id_groupe != 0) {
		$id_mot=id_mot($mot);
		if ($id_mot == 0 ) {
			spip_query("INSERT INTO spip_mots (type, titre, id_groupe, descriptif, texte) VALUES ('$groupe', '$mot', '$id_groupe', '$descriptif', '$texte')");
			$id_mot = spip_insert_id();
		} else {
			// Mise � jour
			spip_query("UPDATE spip_mots SET type='$groupe', id_groupe='$id_groupe', descriptif='$descriptif', texte='$texte' WHERE id_mot=$id_mot");
		}
	}
	$mot = stripslashes($mot);
	global $ul_ouvert;
	if (!$ul_ouvert)	{
		echo "<ul>\n";
		$ul_ouvert = true;
	}

	echo "<li>Mot: $mot (<a href='?exec=mots_edit&amp;id_mot=$id_mot&amp;redirect=%3Fexec%3Dmots_tous'>$id_mot</a>)</li>\n";
	return $id_mot;
}

function create_rubrique($titre, $id_parent='0', $descriptif='') {
	$id_rubrique = id_rubrique($titre);
	if ($id_rubrique==0) {
		$titre = importer_charset($titre);
		$descriptif = importer_charset($descriptif);
		$query="INSERT INTO spip_rubriques (titre, id_parent, descriptif) VALUES ('$titre', '$id_parent', '$descriptif')";
		spip_query($query);
		$id_rubrique = spip_insert_id();
	}
	$titre = stripslashes($titre);
	echo "<li>Rubrique: $titre (<a href='?exec=naviguer&amp;id_rubrique=$id_rubrique'>$id_rubrique</a>)</li>";
	return $id_rubrique;
}

function create_article($titre, $id_parent='0', $descriptif='') {
	$id_rubrique = id_article($titre);
	if ($id_rubrique==0) {
		$titre = importer_charset($titre);
		$descriptif = importer_charset($descriptif);
		$query="INSERT INTO spip_articles (titre, descriptif) VALUES ('$titre', '$descriptif')";
		spip_query($query);
		$id_article = spip_insert_id();
	}
	$titre = stripslashes($titre);
	echo "<li>Articles: $titre (<a href='?exec=naviguer&amp;id_article=$id_article'>$id_article</a>)</li>";
	return $id_article;
}

function create_rubrique_mot($rubrique, $mot) {
	$id_rubrique = id_rubrique($rubrique);
	$id_mot=id_mot($mot);
	if ($id_rubrique!=0 && $id_mot!=0) {
		$query="SELECT count(*) as nb_rub_mot FROM spip_mots_rubriques WHERE id_mot='$id_mot' AND id_rubrique='$id_rubrique'";
		$result=spip_query($query);
		if ($row = spip_fetch_array($result)) {
			if ($row['nb_rub_mot']==0) {
				$query="INSERT INTO spip_mots_rubriques (id_mot, id_rubrique) VALUES ('$id_mot', '$id_rubrique')";
				spip_query($query);
			}
		}
	}
	echo "<li>Liaison entre Rubrique (<a href='?exec=naviguer&amp;id_rubrique=$id_rubrique'>$id_rubrique</a>) et Mot (<a href='?exec=mots_edit&amp;id_mot=$id_mot&amp;redirect=%3Fexec%3Dmots_tous'>$id_mot</a>)</li>\n";
	return TRUE;
}

function config_site() {
	include_spip('inc/minipres');
	
	echo install_debut_html("Configurateur des mots clefs");
	// Autorisations dates ant�rieures et gestion avanc�e des mots cl�
	spip_query("REPLACE spip_meta (nom, valeur) VALUES ('config_precise_groupes', 'oui')");
	spip_query("REPLACE spip_meta (nom, valeur) VALUES ('articles_redac', 'oui')");
	// Cr�ation rubriques
	
	echo "<h2>Cr&eacute;ation des rubriques sp&eacute;ciales</h2><ul>";
	create_rubrique('900. Agenda', '0');
	echo '</ul>';
	
## -------------------------------------------->

	create_groupe("_ConfigSite", "Pour des configuration générale du site", "", 'non', 'non', 'non', 'non', 'non', 'non', 'non', 'non', 'non', 'non');

		create_mot("_ConfigSite", "PictoCampagneSommaire", "La Campagne - Saison 1", "");
		create_mot("_ConfigSite", "PictoMilieuSommaire", "Mettre en logo l\'image du milieu dans la page sommaire (celle qui sépare l\'édito et la suite)", "");


	create_groupe("_SpecialisationArticle", "Un mot clef pris dans ce groupe permettra de modifier\n\nle comportement d\'un article particulier", "", 'non', 'non', 'oui', 'non', 'non', 'non', 'non', 'oui', 'non', 'non');

		create_mot("_SpecialisationArticle", "Adherer", "Pour définir l\'article qui aura le formulaire de contact", "");
		create_mot("_SpecialisationArticle", "ALaUne", "Article qui doit rester  la une du site", "");

		create_mot("_SpecialisationArticle", "Campagne", "", "");
		create_mot("_SpecialisationArticle", "Engager", "", "");
		create_mot("_SpecialisationArticle", "Footer", "Les articles qui doivent être en lien dans le footer.", "");

		create_mot("_SpecialisationArticle", "Newsletter", "", "");
		create_mot("_SpecialisationArticle", "presse", "", "");
		create_mot("_SpecialisationArticle", "Proposition", "Pour tous les articles qui sont une proposition", "");

		create_mot("_SpecialisationArticle", "video", "À affecter aux articles destinés à être pris au hasard pour afficher un lien vers une vidéo.\n\nLa vidéo doit avoir une vignette personnalisée.", "");
		create_mot("_SpecialisationArticle", "Courrier_libre", "À affecter aux articles destinés à être pris pour la newsletter","");

	create_groupe("_SpecialisationRubrique", "Un mot clef pris dans ce groupe permettra de modifier le comportement dune rubrique (et de ses articles)", "", 'non', 'non', 'non', 'non', 'oui', 'non', 'non', 'oui', 'non', 'non');

		create_mot("_SpecialisationRubrique", "Agenda", "Pour dire qu\'une rubrique est dans l\'Agenda", "");
		create_mot("_SpecialisationRubrique", "PasDansMenu", "Pour interdire que la rubrique soi(en)t dans le menu de gauche", "");

		create_mot("_SpecialisationRubrique", "PasDansPlan", "Permet de masquer une rubrique, et tout son contenu (y compris les sous-rubriques) du plan du site", "");

	create_groupe("_squelette", "Pour changer le comportement des rubriques", "", 'oui', 'non', 'non', 'non', 'oui', 'non', 'non', 'oui', 'non', 'non');

		create_mot("_squelette", "Agenda", "Pour appeller :\n-* inc-rub-Agenda\n-* inc-art-Agenda", "");
		create_mot("_squelette", "Campagne", "", "pour les affiches, tracts...");

		create_mot("_squelette", "candidat", "", "");
		create_mot("_squelette", "Debat", "", "");
		create_mot("_squelette", "Dossier", "Pour définir la rubrique des dossiers", "");


## <--------------------------------------------
	global $ul_ouvert;	
	if ($ul_ouvert)	{
		echo "</ul>\n";
		$ul_ouvert = false;
	}

	// Liaison entre rubrique et mot cl�
	echo "<ul>\n";
	create_rubrique_mot('900. Agenda', 'PasDansMenu');
	echo "</ul>\n";
	
	echo "<h1>Installation termin&eacute;</h1><p>Vous pouvez revenir &agrave; l'<a href='./'>administration du site</a></p>";
	echo install_fin_html();
	
	return TRUE;
}	

function exec_postconfig() {
	if (!defined("_ECRIRE_INC_VERSION")) return;

	include_spip('inc/admin');
	include_spip("inc/lang");
	include_spip("inc/charsets");

	debut_admin("postconfig", "Configurateur des mots clefs", "Voulez-vous vraiment installer (ou r&eacute;-installer) les mots clefs du site ?");
	config_site();
	fin_admin("Configurateur site");
}
?>