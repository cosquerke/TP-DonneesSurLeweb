<?php
  header('Content-type: text/plain');
  include('Sax4PHP/Sax4PHP.php');


  class visu_sax extends DefaultHandler  {

    private $ContinentFlag = False;
    private $PresidentFlag = False;
    private $FrLanguageFlag = False;
    private $OffLanguageFlag = False;
    private $ListePays;
    private $ListeVisites;
    private $ListePersonnes;
    private $Pays_xmlId;
    private $Pays_language;
    private $Visites_pays;
    private $Visites_personne;
    private $Visites_duree;
    private $Personnes_Nom;
    private $Fonctions_xmlId;
    private $FonctionPresidentFlag;
    private $BuggedStartDateFlag;

      function startDocument() {
        $this->ListePays = [];
        $this->ListeVisites = [];
        $this->ListePersonnes = [];
        $this->ContinentFlag = False;
        $this->PresidentFlag = False;
        $this->FrLanguageFlag = False;
        $this->OffLanguageFlag = False;

        echo "<?xml version='1.0' encoding='UTF-8'?>\n";
        echo "<liste-présidents>\n";
      }

      function endDocument() {

        $TmpDureeFlag = False;

        foreach ($this->ListePersonnes as $CurrentPersonneKey => $CurrentPersonneValue) {
          echo "\t<président nom=".$CurrentPersonneKey.">\n" ;
          foreach ($this->ListePays as $CurrentPaysKey => $CurrentPaysValue) {
            echo "\t\t<pays nom = ".$CurrentPaysKey." ";
            foreach ($this->ListePersonnes[$CurrentPersonneKey] as $CurrentPersonneVisite) {
              if ($CurrentPersonneVisite['Pays'] == $CurrentPaysKey) {
                $TmpDureeFlag = True;
              }
              else {
                $TmpDureeFlag = False;
              }
            }
            if ($TmpDureeFlag) {
              echo "duree = P".$CurrentPersonneVisite['Durée']."D ";
            } else {
              echo "duree = 0 ";
            }
            if ($CurrentPaysValue != "0") {
              echo "francophone = ".$CurrentPaysValue;
            }
            echo "/>\n";


          }
        }
        echo '</liste-présidents>';
      }

      function startElement($nom,$att) {
          switch(utf8_decode($nom)){
            case "pays" :
              //Variables and flags init
              $this->ContinentFlag = False;
              $this->PresidentFlag = False;
              $this->FrLanguageFlag = False;
              $this->OffLanguageFlag = False;
              $this->Pays_language = "0";
              $this->Visites_pays = "NONE";   //Init
              $this->Visites_personne = "NONE";
              //End

              $langTab = [$language => "null"];
              $this->Pays_xmlId = $att['xml:id'];
              break;

            case "encompassed" :
              if ($att['continent'] == 'africa') {
                $this->ContinentFlag = True;
              }
              else {
                $this->ContinentFlag = False;
              }
            break;

            case "language" :
              $this->FrLanguageFlag = False;
              $this->OffLanguageFlag = False;

              if($this->ContinentFlag){
                  if($att['percentage'] >= 30){
                    $this->FrLanguageFlag = True;
                  }
                  if ($att['percentage'] == NULL) {
                    $this->OffLanguageFlag = True;
                  }
              }
            break;

            case "visite" :
              $this->BuggedStartDateFlag = False;
              $this->PresidentFlag = False;
              $this->Visites_pays = $att['pays'];
              $this->Visites_personne = $att['personne'];
              try {
                $Visites_debut = new DateTime($att['debut']);
                $Visites_fin = new DateTime($att['fin']);
              } catch (\Exception $e) {
                $this->BuggedStartDateFlag = True;
              }

              if (!$this->BuggedStartDateFlag) {
                $this->Visites_duree = intval($Visites_fin->diff($Visites_debut)->format("%a"))+1;
              }
              else {
                $this->Visites_duree = -1;
              }

              if(strpos($att['personne'], "Président") != False) {
                $this->PresidentFlag = True;
              }
            break;

            case "personne":
              $this->FonctionPresidentFlag = False;
              $this->Personnes_Nom = $att['nom'];
            break;

            case "fonction":
              if($att['type'] == "Président de la République") {
                $this->FonctionPresidentFlag = True;
                $this->Fonctions_xmlId = $att['xml:id'];
              }
            break;

          }
      }

      function characters($txt) {
        $txt = trim($txt);
        if (($this->OffLanguageFlag and $txt == "French")) {
          $this->Pays_language = "officielle";
        }
        elseif (($this->FrLanguageFlag and $txt == "French")) {
          $this->Pays_language = "francophone";
        }
      }

      function endElement($nom) {
          switch ($nom) {
            case "pays":
              if ($this->ContinentFlag) {
                $this->ListePays += [$this->Pays_xmlId => $this->Pays_language];
              }
            break;


            case "visite" :

              $VisiteFound = False;
              $TmpVisiteID = $this->Visites_pays.$this->Visites_personne;

              if($this->PresidentFlag){
                foreach ($this->ListeVisites as $CurrentVisiteKey => $CurrentVisiteValue) {
                  if ($TmpVisiteID == $CurrentVisiteKey) {
                    $VisiteFound = True;
                    $this->ListeVisites[$CurrentVisiteKey]['Durée'] += ($this->Visites_duree);
                  }
                }

                if (!$VisiteFound) {
                  $this->ListeVisites = $this->ListeVisites + [ $TmpVisiteID => ['Pays' => $this->Visites_pays, 'Personne' => $this->Visites_personne, 'Durée' => $this->Visites_duree]];
                }
              }
            break;

            case "personne" :
              $TmpTabPersonne = [];
              if($this->FonctionPresidentFlag){
                foreach ($this->ListeVisites as $CurrentVisiteKey => $CurrentVisiteValue) {
                  $TmpVisiteAfriqueFlag = False;
                  foreach ($this->ListePays as $CurrentPaysKey => $CurrentPaysValue) {
                    if ($this->ListeVisites[$CurrentVisiteKey]['Pays'] == $CurrentPaysKey) {
                      $this->ListeVisites[$CurrentVisiteKey] = $this->ListeVisites[$CurrentVisiteKey] + ['Langue' => $CurrentPaysValue];
                      $TmpVisiteAfriqueFlag = True;
                    }
                  }
                  if ($this->ListeVisites[$CurrentVisiteKey]['Personne'] == $this->Fonctions_xmlId and $TmpVisiteAfriqueFlag) {
                    array_push($TmpTabPersonne, $this->ListeVisites[$CurrentVisiteKey]);
                  }
                }
                $this->ListePersonnes = $this->ListePersonnes + [$this->Personnes_Nom => $TmpTabPersonne];
            }
            break;
        }
      }

  }

    $visu = file_get_contents('tp.xml');

    try {
      $sax = new SaxParser(new visu_sax());
      $sax->parse($visu);
      }

    catch(SAXException $e){ echo "\n",$e;}
    catch(Exception $e) {echo "Capture l'exception par défaut\n", $e;}
?>
