<?php
/* Lecture du fichier source xml */
	$doc = new DOMDocument();
	$doc->validateOnParse = true;
	$doc->preserveWhiteSpace = false;
  $doc->load('tp.xml');

/* Creation du document xml en sortie */
  $dom = new DOMDocument();
  $dom->encoding = 'utf-8';
  $dom->xmlVersion = '1.0';
  $dom->formatOutput = true;
	$xml_file_name = 'resultDom.xml';


/* Création de la racine */
  $racine = $dom->createElement("liste-présidents");
  $dom->appendChild($racine);

/* Création du noeud Président */
	$racineDocXml = $doc->childNodes[1];
	$noeudListePersonnes = $racineDocXml->lastChild->childNodes;
	$noeudListePays = $racineDocXml->childNodes[1]->childNodes;

	$listePresidents = array();

	foreach ($noeudListePersonnes as $personne) {
		$listeFonction = $personne->childNodes;
		foreach ($listeFonction as $fonction) {
			if ($fonction->getAttribute("type") == "Président de la République") {
				array_push($listePresidents,$personne);
			}
		}
	}

	$listePaysVisite = array();

	foreach ($noeudListePays as $pays) {
		$p = $pays->childNodes;
		foreach ($p as $node) {
			if ($node->nodeName == "encompassed" && $node->getAttribute("continent") == "africa") {
				array_push($listePaysVisite,$pays);
			}
		}

	}

	foreach ($listePresidents as $president) {
		$noeudPresident = $dom->createElement("président");

		$nomPresident = new DOMAttr("nom",$president->getAttribute("nom"));
		$noeudPresident->setAttributeNode($nomPresident);

		$idFonction = $president->childNodes[0]->getAttribute('xml:id');

		foreach ($listePaysVisite as $pays) {
			$noeudVisite = $dom->createElement("pays");
			$idPays = $pays->getAttribute('xml:id');

			// ECRIRE A PARTIR L37 DE DOM_XPATH/tp.DOM.php
		}

		$racine->appendChild($noeudPresident);
	}

/* Sauvegarde du résultat dans un fichier xml */
  $dom->save($xml_file_name);

?>
