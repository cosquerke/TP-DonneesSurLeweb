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

/* Création de l'environnement Xpath */
	$xpath = new DOMXPath($doc);

/* Création de la racine */
  $racine = $dom->createElement("liste-présidents");
  $dom->appendChild($racine);

	$listePresidents = $xpath->query("/déplacements/liste-personnes/personne[fonction/@type = 'Président de la République']");

/* Création du noeud Président */
	foreach ($listePresidents as $president) {
		$noeudPresident = $dom->createElement("président");
		/* Ajout de l'atrribut nom au noeud président */
		$nomPresident = new DOMAttr("nom",$president->getAttribute("nom"));
		$noeudPresident->setAttributeNode($nomPresident);

		$listePaysVisite = $xpath->query("/déplacements/liste-pays/pays[encompassed/@continent='africa']");
		$idFonction = $president->childNodes[0]->getAttribute('xml:id');
		foreach ($listePaysVisite as $pays) {
			$noeudVisite = $dom->createElement("pays");

			$idPays = $pays->getAttribute('xml:id');
			$listeVisite = $xpath->query("/déplacements/liste-visites/visite[@pays = '".$idPays."' and @personne = '".$idFonction."']");

			$duree = 0;

			foreach ($listeVisite as $visite) {
				$debut = new DateTime($visite->getAttribute("debut"));
				$fin = new DateTime($visite->getAttribute("fin"));
				$diff = $debut->diff($fin)->format("%r%a");

				$duree = $duree + intval($diff);
			}
			if ($duree == 0) {
				$df = "0";
			}else {
				$df = "P".$duree."D";
			}
			$attributeDuree = new DOMAttr("duree",$df);
			$noeudVisite->setAttributeNode($attributeDuree);
				$listeLangue = $xpath->query("id('".$idPays."')/language");

				foreach ($listeLangue as $langue) {
					if ($langue->nodeValue == "French") {
						$francophone = "Partiel";
						if(floatval($langue->getAttribute("percentage") >= 30)){
							$francophone = "Officiel";
						}
					 $attributeFrancophone = new DOMAttr("francophone",$francophone);
	 				 $noeudVisite->setAttributeNode($attributeFrancophone);
					}
				}

			$attributeNom = new DOMAttr("nom",$pays->getAttribute("nom"));
			$noeudVisite->setAttributeNode($attributeNom);

			$noeudPresident->appendChild($noeudVisite);
		}

		$racine->appendChild($noeudPresident);
	}




/* Sauvegarde du résultat dans un fichier xml */
  $dom->save($xml_file_name);

?>
