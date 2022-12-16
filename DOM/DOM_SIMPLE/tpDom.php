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
	$noeudListeVisite = $racineDocXml->childNodes[3]->childNodes;

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
	$listeLangueByPays = array();

	foreach ($noeudListePays as $pays) {
		$p = $pays->childNodes;
		foreach ($p as $node) {
			if ($node->nodeName == "encompassed" && $node->getAttribute("continent") == "africa") {
				array_push($listePaysVisite,$pays);

			//	echo $node->nextSibling->nextSibling->nodeName;
			}
		}

	}

	//var_dump($listePaysVisite);

	foreach ($listePresidents as $president) {
		$noeudPresident = $dom->createElement("président");

		$nomPresident = new DOMAttr("nom",$president->getAttribute("nom"));
		$noeudPresident->setAttributeNode($nomPresident);

		$idFonction = $president->childNodes[0]->getAttribute('xml:id');

		foreach ($listePaysVisite as $pays) {
			$idPays = $pays->getAttribute('xml:id');

			$listeVisite = array();

			foreach ($noeudListeVisite as $noeudVisite) {
			//	var_dump($noeudVisite->getAttribute("debut"));
				if (($noeudVisite->getAttribute("pays") == $idPays) && ($noeudVisite->getAttribute("personne") == $idFonction)) {
					array_push($listeVisite,$noeudVisite);
				}
			}
			$duree = count($listeVisite);

			foreach ($listeVisite as $visite) {
				$debut = new DateTime($visite->getAttribute("debut"));
				$fin = new DateTime($visite->getAttribute("fin"));
				$diff = $debut->diff($fin)->format("%r%a");

				if ($debut == $fin) {
					$diff = 1;
				}else {
					$diff = $debut->diff($fin)->format("%r%a");
				}

				$duree = $duree + intval($diff);
			}
			if ($duree == 0) {
				$df = "0";
			}else {
				$df = "P".$duree."D";
			}
			$noeudVisite = $dom->createElement("pays");
			$attributeDuree = new DOMAttr("duree",$df);
			$noeudVisite->setAttributeNode($attributeDuree);

			$elementsPays = $pays->childNodes;
			foreach ($elementsPays as $element) {
				if ($element->nodeName == "language" && $element->nodeValue == "French") {
					$francophone = "Officiel";
					if(floatval($element->getAttribute("percentage")) >= 30){
						$francophone = "Partiel";
					}
					$attributeFrancophone = new DOMAttr("francophone",$francophone);
					$noeudVisite->setAttributeNode($attributeFrancophone);
				}
			}


			$attributeNom = new DOMAttr("nom",$pays->getAttribute("nom"));
			$noeudVisite->setAttributeNode($attributeNom);

			$noeudPresident->appendChild($noeudVisite);
			unset($listeVisite);
		}

		$racine->appendChild($noeudPresident);
	}

/* Sauvegarde du résultat dans un fichier xml */
  $dom->save($xml_file_name);

?>
