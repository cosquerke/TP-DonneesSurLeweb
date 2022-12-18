xquery version '1.0';

<liste-présidents>
{
for $personne in doc("tp.xml")/déplacements/liste-personnes/personne
let $Pnom := $personne/@nom
where $personne/fonction/@type = "Président de la République"
return 
    <président nom="{$Pnom}">
        {
        for $pays in doc("tp.xml")/déplacements/liste-pays/pays
        let $PAnom := $pays/@nom
        where $pays/encompassed/@continent = "africa"
        return
            element pays {
                attribute nom {$PAnom},
                attribute duree {
                      for $visite in doc("tp.xml")/déplacements/liste-visites/visite
                      let $Vdebut := xs:date($visite/@debut)
                      let $Vfin :=  xs:date($visite/@fin)
                      let $Vduree := ($Vfin - $Vdebut) div xs:dayTimeDuration("P1D")
                      where ($visite/@personne = $personne/fonction/@xml:id) and ($visite/@pays = $pays/@xml:id)
                      return sum($Vduree)
                }
            }
            
        }
    </président>
}
</liste-présidents>