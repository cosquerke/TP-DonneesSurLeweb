xquery version '1.0';

<liste-présidents>
{
for $personne in doc("tp.xml")/déplacements/liste-personnes/personne  (: Pour chacun des noeuds personne de tp.xml :)
let $Pnom := $personne/@nom                                           (: On enregistre dans une variable, l'attribut nom du noeud courant:)
where $personne/fonction/@type = "Président de la République"         (: Uniquement pour les noeuds dont l'attribut type du fils 'fonction' est 'Président de la République', dans le résultat final seul les visites des présidents doivent être sauvegardées :)
return                                                                
    <président nom="{$Pnom}">                                         
        {
        for $pays in doc("tp.xml")/déplacements/liste-pays/pays       (: Pour chacun des noeuds pays dans tp.xml :)
        let $PAnom := $pays/@nom                                      (: On sauvegarde dans une variable l'attribut nom du pays courant :)
        where $pays/encompassed/@continent = "africa"                 (: Pour chaque noeud où l'attribut continent de l'élément fils 'encompassed' est 'africa', le résultat final veut uniquement rapporter les visites en pays africains :)
        return                                                        (: On crée un élément pays avec un attribut nom correspondant au nom du pays courant sauvegardé plutôt et deux attributs calculés duree et francophone :)
            element pays {                                            
                attribute nom {$PAnom},
                attribute duree {
                    sum(                                                                    (: Pour calculer la durée de chaque visite on fait la somme des durées des visites soit la date de fin moins la date de début en considérant que les jours :)
                      for $visite in doc("tp.xml")/déplacements/liste-visites/visite        (: Pour chacun des noeuds visite :)
                      let $Vdebut := xs:date($visite/@debut)                                (: Nous sauvegardons, en les convertissant de string au type date les dates de fin et de début :)
                      let $Vfin :=  xs:date($visite/@fin) 
                      where ($visite/@personne = $personne/fonction/@xml:id) and ($visite/@pays = $pays/@xml:id)     (: Nous souhaitons, pour chaque visite dans un pays africain pour chaque président sauvegarder la durée, nous allons donc pour faire cette somme récupérer uniquement les noeuds visite du président courant pour le pays africain courant :)
                      return (($Vfin - $Vdebut) + xs:dayTimeDuration("P1D"))                                         (: Puis nous retournons la valeur de cette requête, en veillant à y ajouter 1 jour car le document de base ne comprend pas d'heure et induit donc un biais :)
                    )                                                                                                (: Chacun des résultats retournés, sera additionné avec les précédents :)
                },
                                                                                         
                      if($pays/language[./text() eq "French" and ./@percentage  >= 30])                            (: Pour chacun des pays, si le fils 'langage' indique 'French' et que son attribut 'percetage' est supérieur à 30 on considère le pays en partie francophone :) 
                         then attribute fracophone {"En-partie"}
                         else if($pays/language[./text() eq "French"])                                             (: Sinon (Quand l'attribut percentage n'existe pas) si le texte est français sans pourcentage alors le français es langue officiel :)
                                 then attribute fracophone {"Officiel"}
                                 else NULL                                                                           
                    
                }
            }
            
    </président>
}
</liste-présidents>
