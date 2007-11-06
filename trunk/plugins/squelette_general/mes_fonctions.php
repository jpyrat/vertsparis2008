<?php

//      Mes_fonctions.php3

/*
* les notes ont un petit bug :il manque parfois le <p> de debut
* cette fonction y remedie
* fonction utilisee aussi sur mon site pour la bio des auteurs notamment
* */

function nb_checkparas($str) {
    $str = trim($str);
    if($str!="") {
        if(substr($str, 0, 2)!="<p") {
            $str = "<p>" . $str;
        }
        if(substr($str, strlen($str)-4, 4)!="</p>") {
            $str .= "</p>";
        }
    }
    return $str;
}
// FIN du filtre nb_checkparas

/*
 *   +----------------------------------+
 *    Nom du Filtre : Sommaire de l'article
 *   +----------------------------------+
 *    Date : dimanche 9 f�vrier 2003
 *    Auteur :  Noplay (noplay@altern.org)
 *   +-------------------------------------+
 *    Fonctions de ce filtre :
 *      Cette modification permet d'afficher le sommaire de son article
 *      g�n�r� dynamiquement � partir du texte de l'article. Vous pouvez naviguer
 *      dans l'article en cliquant sur les titres du sommaire.
 *
 *      Tous ce qui ce trouve entre {{{ et }}} est consid�r� comme un titre � ajouter au sommaire de l'article.
 *   +-------------------------------------+
 *
 * Pour toute suggestion, remarque, proposition d'ajout
 * reportez-vous au forum de l'article :
 * http://www.uzine.net/spip_contrib/article.php3?id_article=76
*/
function sommaire_article($texte,$istxt=0)
{

	preg_match_all("|(\{[\{12345]\{)(.*)(\}[\}12345]\})|U", $texte, $regs);


	$nb=1;
	$lastniveau=0;
	if ($istxt==0) {
		$texte="<a name='SommaireAutomatique'></a>";
	for($j=0;$j<count($regs[2]);$j++)
	{
		$niveau=substr($regs[1][$j], 1, 1);
		if ($niveau=='{') {$niveau=1;}
		if ($niveau==$lastniveau) {
			$texte.="</li>\n";
		}
		if ($niveau>$lastniveau) {
			$texte.="<ul>";
			$lastniveau=$niveau;
		}
		if ($niveau<$lastniveau) {
			$texte.="</li>\n";
			for($ulli=$niveau;$ulli<$lastniveau;$ulli++) {
				$texte.="</ul></li>\n";
			}
			$lastniveau=$niveau;
		}

    		$texte.="<li><a href=\"#sommaire_".$nb."\">".$regs[2][$j]."</a>";
		$nb++;
    }
	for($j=0;$j<$niveau;$j++) {
		$texte.="</li></ul>\n";
	}
	} else {
		$texte="";
		for($j=0;$j<count($regs[2]);$j++)
		{
			$niveau=substr($regs[1][$j], 1, 1);
			if ($niveau=='{') { $puce="\n- ";}
			if ($niveau==2) { $puce=" � ";}
			if ($niveau==3) { $puce="  � ";}
			if ($niveau==4) { $puce="   � ";}
			if ($niveau==5) { $puce="    � ";}
			$texte.=$puce.$regs[2][$j]."\n";
		}

	}
	return $texte;
}

function sommaire_ancre($texte)
{
	$texte = preg_replace("|(<h[23456][^>]*>)(.*)(<\/h[23456]>)|U","<p class='retoursommaire'><a href='#SommaireAutomatique'>Retour Sommaire</a></p><a name=\"sommaire_#NB_TITRE_DE_MON_ARTICLE#\"></a>$1$2$3", $texte);

	$array = explode("#NB_TITRE_DE_MON_ARTICLE#" , $texte);
	$res =count($array);
	$i =1;
	$texte=$array[0];
	while($i<$res)
	{
		$texte.=$i.$array[$i];
		$i++;
	}
	$texte.="<p class='retoursommaire'><a href='#SommaireAutomatique'>Retour Sommaire</a></p>";
	return $texte;
}
//Fin filtre sommaire de l'article

function PyratprepareNLtexte($texte) {
	// Remplace tous les liens
	while (eregi("<a href=['\"]([^'\">]+)['\"][^>]*>([^<]+)</a>", $texte, $regs)) {
		$cleanReg1 = ereg_replace("\\?", "\?", $regs[1]);
		$cleanReg1 = ereg_replace("\\+", "\+", $cleanReg1);
		$cleanReg2 = ereg_replace("\\?", "\?", $regs[2]);
		$cleanReg2 = ereg_replace("\\+", "\+", $cleanReg2);
		if ($regs[1] == $regs[2]) {
		    $texte = eregi_replace("<a href=['\"]".$cleanReg1."['\"][^>]*>".$cleanReg1."</a>", $regs[1], $texte);
		} else {
		    if ($regs[1] == str_replace("&nbsp;?","?",$regs[2])) {
			    $texte = eregi_replace("<a href=['\"]".$cleanReg1."['\"][^>]*>".$cleanReg2."</a>", $regs[1], $texte);
		    } else {
			    $texte = eregi_replace("<a href=['\"]".$cleanReg1."['\"][^>]*>".$cleanReg2."</a>", str_replace("&nbsp;?","?",$regs[2])." (".$regs[1].")", $texte);
		    }
		}
	}
	$texte = preg_replace("|(<h[2]>)(.*)(<\/h[2]>)|U","<br /><br />----------------------------------------------------------------------<br />$1$2$3<br />----------------------------------------------------------------------<br />", $texte);
	$texte = preg_replace("|(<h[3456]>)(.*)(<\/h[3456]>)|U","<br />���� $1$2$3 ����", $texte);
	$texte = ereg_replace ('<li[^>]>', "\n".'-', $texte);
	$texte = ereg_replace ('&#8217;', '\'', $texte);
	$texte = ereg_replace ('&#171;', '"', $texte);
	$texte = ereg_replace ('&#187;', '"', $texte);
	$texte = ereg_replace ('&amp;', '&', $texte);
	$texte = textebrut($texte);
	$texte = wordwrap($texte, 70, "\n");
	return $texte;
}

function nettoyer_marqueur($texte) {
	include_spip('inc/charsets');
	$texte=translitteration($texte);
	// Enl�ve la conversion caract�res sp�ciaux HTML
	$trans_tbl = get_html_translation_table (HTML_ENTITIES);
	$trans_tbl = array_flip ($trans_tbl);
	$texte=strtr ($texte, $trans_tbl);
	$accents =
			/* A */ chr(192).chr(193).chr(194).chr(195).chr(196).chr(197).
			/* a */ chr(224).chr(225).chr(226).chr(227).chr(228).chr(229).
			/* O */ chr(210).chr(211).chr(212).chr(213).chr(214).chr(216).
			/* o */ chr(242).chr(243).chr(244).chr(245).chr(246).chr(248).
			/* E */ chr(200).chr(201).chr(202).chr(203).
			/* e */ chr(232).chr(233).chr(234).chr(235).
			/* Cc */ chr(199).chr(231).
			/* I */ chr(204).chr(205).chr(206).chr(207).
			/* i */ chr(236).chr(237).chr(238).chr(239).
			/* U */ chr(217).chr(218).chr(219).chr(220).
			/* u */ chr(249).chr(250).chr(251).chr(252).
			/* yNn */ chr(255).chr(209).chr(241);
	$texte = ereg_replace("<[^<]*>", "", $texte);
	$texte = ereg_replace("[^A-Za-z0-9]", "_", strtr($texte,$accents,"AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn"));
	$texte = ereg_replace("�", "_", $texte);
	$texte = ereg_replace(" ", "_", $texte);
	$texte = ereg_replace("_+", "_", $texte);
	return strtolower($texte);
}

function strip_img($x) {
     eregi("<img[^>]* src=['\"]([^'\"]+)['\"]", $x, $r);
     return $r[1];
}

// tronque un liens � $limit caract�res
function couper_liens($texte, $limit) {
  if (strlen($texte) <= $limit){
 return $texte;}
else return substr($texte, 0, $limit) . " (...)";
}

/* � appliquer au #TEXTE de forum */
function anti_glouton($texte) {
  $regexp = '|<a href=["\x27]([^"\x27]+)["\x27][^>]*>([^<]+)</a>|i';
  $replace = "\${2} [\${1}]";
  $texte = preg_replace($regexp, $replace, $texte);
  return $texte;
}

function jp_replace($texte,$search,$replace) {
	return trim(str_replace($search,$replace,$texte));
}

function jp_NewLine2str($texte) {
	return str_replace('"','\"',str_replace("\r",'',str_replace("\n",'\n',$texte)));
}

function DateAdd($d=null, $v, $f="Y-m-d"){
  return date($f,strtotime($v." days",strtotime($d)));
}

// function afficher_les_dates($dateDebut,$dateFin,$horaire, $distance=0, $en_cours=0, $duree=1, $format_court=1) {
// 	$distance = intval($distance);
// 	$en_cours = intval($en_cours);
// 	$duree = intval($duree);
// 	$format_court = intval($format_court);
// 	
// 	if ($horaire=='oui') {
// 		$heureDebut = affdate($dateDebut,'H:i');
// 		$heureFin = affdate($dateFin,'H:i');
// 	} else {
// 		$heureDebut = '00:00';
// 		$heureFin = '00:00';
// 	}
// 	$dateDebut = affdate($dateDebut,'Y-m-d');
// 	$dateFin = affdate($dateFin,'Y-m-d');
// 	
// 	$str = '';
// 	$msg = '';
// 	
// 	if ($format_court == 1) {
// 		$str .= "<acronym title='".nom_jour($dateDebut)."' class='spip_acronym'>".substr(nom_jour($dateDebut),0,2).'</acronym> '.affdate($dateDebut,'d');
// 		if ( $dateDebut != $dateFin ) {
// 			$str .= ' &ndash; '."<acronym title='".nom_jour($dateFin)."' class='spip_acronym'>".substr(nom_jour($dateFin),0,2).'</acronym> '.affdate($dateFin,'d');
// 		}
// 	} else {
// 		if ( $dateDebut == $dateFin ) {
// 			$str .=  _T('pyrat:agenda_le');
// 		} else {
// 			$str .= _T('pyrat:agenda_du');
// 		}
// 		$str .= nom_jour($dateDebut).' '.affdate($dateDebut);
// 		
// 		if ( $dateDebut != $dateFin ) {
// 			if ($horaire=='oui') {
// 				$str .= _T('pyrat:agenda_a').$heureDebut;
// 			}
// 			$str .= _T('pyrat:agenda_au').nom_jour($dateFin).' '.affdate($dateFin);
// 			if ($horaire=='oui') {
// 				$str .= _T('pyrat:agenda_a').$heureFin;
// 			}
// 		} else {
// 			if ($horaire=='oui' && $heureDebut != $heureFin) {
// 				$str .= _T('pyrat:agenda_de').$heureDebut._T('pyrat:agenda_a').$heureFin;
// 			} elseif ($horaire=='oui') {
// 				$str .= _T('pyrat:agenda_a').$heureDebut;
// 			}
// 		} 
// 		
// 		//combien de jour et de mois et d'ann�es
// 		$nb_an = intVal((strtotime($dateDebut) - strtotime(date('Y-m-d',time()))) / (3600*24*365.25));
// 		$reste = intVal((strtotime($dateDebut) - strtotime(date('Y-m-d',time()))) % (3600*24*365.25));
// 		
// 		$nb_mois = intVal(($reste) / (3600*24*30));
// 		$reste = intVal(($reste) % (3600*24*30));
// 		
// 		$nb_jour = intVal( ($reste) / (3600*24));
// 		
// 		//affiche une phrase entre parenth�se type "dans 1 an, 3 mois et 4 jours".
// 		if (strtotime($dateDebut.' '.$heureDebut.':00') < time() && strtotime($dateFin.' '.$heureFin.':00') < time()) {
// 			$msg = '';
// 		} elseif (strtotime($dateDebut.' '.$heureDebut.':00') < time() && strtotime($dateFin.' '.$heureFin.':00') > time()) {
// 				if ($en_cours==1) $msg .= _T('pyrat:agenda_en_ce_moment');
// 		} else {
// 			if ($distance==1 && ($nb_an || $nb_mois || $nb_jour)){
// 				$msg .= '('._T('pyrat:agenda_dans');
// 				if ($nb_an) {
// 					if ($nb_an==1) {
// 						$msg .= $nb_an._T('pyrat:agenda_an');
// 					} else {
// 						$msg .= $nb_an._T('pyrat:agenda_ans');
// 					}
// 				}
// 				if ($nb_mois) {
// 					if ($nb_mois==1) {
// 						$msg .= $nb_mois._T('pyrat:agenda_mois');
// 					} else {
// 						$msg .= $nb_mois._T('pyrat:agenda_mois_pluriel');
// 					}
// 				}
// 				if ($nb_jour) {
// 					if ($nb_jour==1) {
// 						$msg .= $nb_jour._T('pyrat:agenda_jour');
// 					} else {
// 						$msg .= $nb_jour._T('pyrat:agenda_jours');
// 					}
// 				}
// 			$msg = substr($msg,0,-2).')';
// 			} 
// 		}	 
// 		if ($duree==1) {
// 			$msg .= ' ('.intVal((strtotime($dateFin)-strtotime($dateDebut)) / (3600*24))._T('pyrat:agenda_jours_seul').')';
// 		}
// 		
// 	}
// 	
// 	return $str.' '.$msg;
// }

function annee_scolaire($ladate) {
	$annee = annee($ladate);
	$mois = mois($ladate);
	if ($mois <= 8) $annee -= 1;
	return $annee;
}

function aff_img_propre($src, $width, $height, $alt, $link='') {
	if ($src) {
		$src = reduire_image($src, $width, $height); 
		$width = extraire_attribut($src, 'width');
		$height = extraire_attribut($src, 'height');
		$src = extraire_attribut($src, 'src');
		if ($link) {
			return "<a href=\"$link\"><img$classe src=\"/$src\" width=\"$width\" height=\"$height\" alt=\"$alt\" /></a>";
		} else {
			return "<img$classe src=\"/$src\" width=\"$width\" height=\"$height\" alt=\"$alt\" />";
		}
	}
}

function garder_body($texte) {
	$texte = eregi_replace('^.*<body[^>]*>', '', $texte);
	$texte = eregi_replace('</body[^>]*>.*$', '', $texte);
	return $texte;
}

?>