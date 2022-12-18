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

        foreach ($this->ListePersonnes as $CurrentPersonneKey => $CurrentPersonneValue) {                                   //for each person
          echo "\t<président nom=".$CurrentPersonneKey.">\n" ;                                                              //we create an element with the list person key as a name attribute (the president name)
          foreach ($this->ListePays as $CurrentPaysKey => $CurrentPaysValue) {                                              //for each country
            echo "\t\t<pays nom = ".$CurrentPaysKey." ";                                                                    //create an "Pays" element (useless loop, see optimized version)
            foreach ($this->ListePersonnes[$CurrentPersonneKey] as $CurrentPersonneVisite) {                                //for each visit of the current person in the ListePersonnes array
              if ($CurrentPersonneVisite['Pays'] == $CurrentPaysKey) {                                                      //if the current country from ListePays is equal to the visited country from the ListePersonnes array
                $TmpDureeFlag = True;                                                                                       //Then we indicate that we're gonna need to process a duration
              }
              else {
                $TmpDureeFlag = False;                                                                                      //Else not
              }
            }
            if ($TmpDureeFlag) {                                                                                            //If that flag is up
              echo "duree = P".$CurrentPersonneVisite['Durée']."D ";                                                        //Then we write down the duration in an "duree" attribute
            } else {
              echo "duree = 0 ";                                                                                            //Else the "duree" attribute is set to 0
            }
            if ($CurrentPaysValue != "0") {                                                                                 //If for the current country from ListePays, if the status of french is not 0
              echo "francophone = ".$CurrentPaysValue;                                                                      //Then we write down an "francophone" attribute with the current country value
            }
            echo "/>\n";


          }
        }
        echo '</liste-présidents>';
      }

      function startElement($nom,$att) {
          switch(utf8_decode($nom)){

            /*           START PAYS               */
            case "pays" :
              //Variables and flags init
                $this->ContinentFlag = False;
                $this->PresidentFlag = False;
                $this->FrLanguageFlag = False;
                $this->OffLanguageFlag = False;
                $this->Pays_language = "0";
                $this->Visites_pays = "NONE";
                $this->Visites_personne = "NONE";
              //End

              //Saving data and attributes that'll later be needed
              $langTab = [$language => "null"];
              $this->Pays_xmlId = $att['xml:id'];
              break;

            //           START ENCOMPASSED
            // There we're raising a flag if the country's continent is afirca, that flag will be later used in END ENCOMPASSED
            // $ContinentFlag is initialized at each country (in START PAYS)
            case "encompassed" :
              if ($att['continent'] == 'africa') {
                $this->ContinentFlag = True;
              }
              else {
                $this->ContinentFlag = False;
              }
            break;

            //           START LANGUAGE
            // There we're raising two flag to signal the status of french in the country
            case "language" :
              //Variables and flags init
                $this->FrLanguageFlag = False;
                $this->OffLanguageFlag = False;
              //End

              if($this->ContinentFlag){
                  if($att['percentage'] >= 30){
                    $this->FrLanguageFlag = True;
                  }
                  if ($att['percentage'] == NULL) {
                    $this->OffLanguageFlag = True;
                  }
              }
            break;

            //           START VISITE
            case "visite" :

              //Variables and flags init
                $this->BuggedStartDateFlag = False;
                $this->PresidentFlag = False;
              //End


              $this->Visites_pays = $att['pays'];
              $this->Visites_personne = $att['personne'];

              try {                                                              //The try catch structure there aims to
                $Visites_debut = new DateTime($att['debut']);                    //avoid some exception in data source where
                $Visites_fin = new DateTime($att['fin']);                        //dates were invalid thus cannot be processed
              } catch (\Exception $e) {                                          //properly. Therefore we raise a flag as soon
                $this->BuggedStartDateFlag = True;                               //an error is detected in order to indicate later
              }                                                                  //that we won't process this one element.

              if (!$this->BuggedStartDateFlag) {
                $this->Visites_duree = intval($Visites_fin->diff($Visites_debut)->format("%a"))+1;    //If the previously set flag is up, then we calculate the interval
              }
              else {
                $this->Visites_duree = -1;                                                            //Else we set it to -1
              }

              if(strpos($att['personne'], "Président") != False) {              //In order to process the least elements possible
                $this->PresidentFlag = True;                                    //We will raise a flag if the person process isn't a president
              }                                                                 //Later on we will be able to ignore them in the process thus reducing the pcomplexity
            break;

            //           START PERSONNE
            case "personne":
              $this->FonctionPresidentFlag = False;
              $this->Personnes_Nom = $att['nom'];
            break;

            //           START FONCTION
            case "fonction":                                                    //Raising a flag when the president role is present for a certain person
              if($att['type'] == "Président de la République") {                //Therefore there'll be only the president processed and displayed in the final result
                $this->FonctionPresidentFlag = True;                            //Plus saving his name
                $this->Fonctions_xmlId = $att['xml:id'];
              }
            break;

          }
      }

      function characters($txt) {
        $txt = trim($txt);
        if (($this->OffLanguageFlag and $txt == "French")) {                    //This part process the countries' language because they are text nodes
          $this->Pays_language = "officielle";                                  //in addtion to the previously raised flags we need to verify that the
        }                                                                       //language in the current element is actually 'French' and not something else
        elseif (($this->FrLanguageFlag and $txt == "French")) {                 //if that condition is verified and the flag raised we can save into a property
          $this->Pays_language = "En-Partie";                                   //the status of French in the country
        }
      }

      function endElement($nom) {
          switch ($nom) {

            //           END PAYS
            case "pays":
              if ($this->ContinentFlag) {                                       //There we create an array where we'll save, as the keys, the county names
                $this->ListePays += [$this->Pays_xmlId => $this->Pays_language];//and as values the status of french in the country
              }
            break;

            //           END VISITE
            case "visite" :

              $VisiteFound = False;                                                                       //Setting a local variable as False
              $TmpVisiteID = $this->Visites_pays.$this->Visites_personne;                                 //Setting an build ID that'll be used as key in the array storing the visits (Country+Person)

              if($this->PresidentFlag){                                                                   //We're using the previously set presidentFlag so we only process presidents' visits
                foreach ($this->ListeVisites as $CurrentVisiteKey => $CurrentVisiteValue) {               //We browse our visits array in order to detect if the current visite already exists in
                  if ($TmpVisiteID == $CurrentVisiteKey) {                                                //the array (the person visited this country multiple times)
                    $VisiteFound = True;                                                                  //if so we set a boolean to indicate we found one
                    $this->ListeVisites[$CurrentVisiteKey]['Durée'] += ($this->Visites_duree);            //and then we're adding the duration of the current (in the tree) visit to the current one (in the array) then for a person and a country we have only one entry in the array
                  }
                }

                if (!$VisiteFound) {
                  $this->ListeVisites = $this->ListeVisites + [ $TmpVisiteID => ['Pays' => $this->Visites_pays, 'Personne' => $this->Visites_personne, 'Durée' => $this->Visites_duree]];  //if the Country+Person hasn't been found in the array, we add it in the visits array with the built ID as the key and the visit informations (country,duration,person) as value
                }
              }
            break;

            //           END PERSON
            case "personne" :
              $TmpTabPersonne = [];                                                                                                     //Array that'll be used to store one person's visits
              if($this->FonctionPresidentFlag){                                                                                         //Only processing persons with president as a role
                foreach ($this->ListeVisites as $CurrentVisiteKey => $CurrentVisiteValue) {                                             //For each visit that we stored previously in ListeVisites (A)
                  $TmpVisiteAfriqueFlag = False;                                                                                        //Flag that'll later on be used to avoid pushing non africa visits into the final array
                  foreach ($this->ListePays as $CurrentPaysKey => $CurrentPaysValue) {                                                  //For each country in the previously created array ListePays(B)
                    if ($this->ListeVisites[$CurrentVisiteKey]['Pays'] == $CurrentPaysKey) {                                            //If the visited country from (A) is the same as the current one from (B)
                      $this->ListeVisites[$CurrentVisiteKey] = $this->ListeVisites[$CurrentVisiteKey] + ['Langue' => $CurrentPaysValue];//Then we add a new value in the current visit of (A) which is the status of french
                      $TmpVisiteAfriqueFlag = True;                                                                                     //In the (B) array we only have african countries, meaning for each one of them we raise the flag that indicate we're processing an african country
                    }
                  }
                  if ($this->ListeVisites[$CurrentVisiteKey]['Personne'] == $this->Fonctions_xmlId and $TmpVisiteAfriqueFlag) {         //If the person that has done the current visit is the same as the current person processed in the xml tree AND that the visit has been done in an african country
                    array_push($TmpTabPersonne, $this->ListeVisites[$CurrentVisiteKey]);                                                //We push in our temporary array the visit
                  }
                }
                $this->ListePersonnes = $this->ListePersonnes + [$this->Personnes_Nom => $TmpTabPersonne];                              //finally for each person in the tree we're pushing the previously built array that contains all of his visits in the ListePersonnes as the Value, and his name as the key
            }
            break;
        }
      }

  }

    $visu = file_get_contents('tp.xml');                                                                                                //Sax4PHP Library part

    try {
      $sax = new SaxParser(new visu_sax());
      $sax->parse($visu);
      }

    catch(SAXException $e){ echo "\n",$e;}
    catch(Exception $e) {echo "Capture l'exception par défaut\n", $e;}
?>
