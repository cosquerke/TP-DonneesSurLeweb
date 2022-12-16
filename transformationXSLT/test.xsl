<?xml version="1.0" encoding="UTF-8"?> <!-- ok -->
<!-- java -jar ./saxon9he.jar -xsl:test.xsl -s:tp.xml -->
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="2.0"
  xmlns:fn="http://www.w3.org/2005/xpath-functions"
  xmlns:xs="http://www.w3.org/2001/XMLSchema">

  <xsl:output method="html" indent="yes" doctype-system="res.dtd"/>


  <xsl:template match="/">
    <xsl:element name="liste-présidents">
      <xsl:apply-templates select="/déplacements/liste-personnes/personne[fonction/@type = 'Président de la République']"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="personne">
    <xsl:element name="président">
      <xsl:attribute name="nom" select="@nom"/>
      <xsl:apply-templates select="/déplacements/liste-pays/pays[encompassed/@continent='africa']">
      <xsl:with-param name="president" select = "fonction/@xml:id"/>
  </xsl:apply-templates>
    </xsl:element>
  </xsl:template>

  <xsl:template match="pays">
    <xsl:param name = "president" />
    <xsl:element name="pays">

      <!--  <xsl:attribute name="duree" select="idref(@xml:id)/..[@personne = $president]/../fn:sum(visite/xs:dayTimeDuration(xs:date(@fin) - xs:date(@debut)))"/> -->
      <xsl:variable name="duree" select="fn:sum(/déplacements/liste-visites/visite[@pays = current()/@xml:id and @personne = $president]/days-from-duration(xs:date(@fin) - xs:date(@debut))) + count(/déplacements/liste-visites/visite[@pays = current()/@xml:id and @personne = $president])"/>
      <!--<xsl:attribute name="duree" select="/déplacements/liste-visites/visite[@pays = current()/@xml:id and @personne = $president]/concat(@debut,':',@fin)"/> -->


      <xsl:choose>
          <xsl:when test="$duree > 0">
              <xsl:attribute name="durée" select="concat('P',$duree,'D')"/>
          </xsl:when>
          <xsl:otherwise>
              <xsl:attribute name="durée" select="0"/>
          </xsl:otherwise>
      </xsl:choose>

      
  <xsl:apply-templates select="current()/language[./text() = 'French']"/>
      <xsl:attribute name="nom" select="@nom"/>

    </xsl:element>
  </xsl:template>

  <xsl:template match="language">
    <xsl:choose>
          <xsl:when test="not(@percentage)">
            <xsl:attribute name="franchophone">Officiel</xsl:attribute>
          </xsl:when>
            <xsl:when test="@percentage >= 30">
              <xsl:attribute name="franchophone">EnPartie</xsl:attribute>
            </xsl:when>
          </xsl:choose>
  </xsl:template>





</xsl:transform>
