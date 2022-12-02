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





/* Sauvegarde du résultat dans un fichier xml */
  $dom->save($xml_file_name);

?>
