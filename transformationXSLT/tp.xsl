<?xml version="1.0" encoding="UTF-8"?> <!-- ok -->
<!-- java -jar ./saxon9he.jar -xsl:tp.xsl -s:tp.xml -->
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
  <!--      <xsl:apply-templates select="/déplacements/liste-visites/visite[(@personne = current()/fonction/@xml:id) and (id(@pays)[encompassed/@continent='africa'])]"/> -->
      <xsl:apply-templates select="/déplacements/liste-visites/visite[(id(@pays)[encompassed/@continent='africa'])]"/>
  <!--<xsl:apply-templates select="id(/déplacements/liste-visites/visite[@personne = current()/fonction/@xml:id]/@pays)[encompassed/@continent='africa']"/>-->
    </xsl:element>
  </xsl:template>

  <xsl:template match="visite">
    <xsl:variable name="EcartAnnee" select="number(substring(@fin, 1, 4)) - number(substring(@debut, 1, 4)) "/>
    <xsl:variable name="EcartMois" select="number(substring(@fin, 6, 2)) - number(substring(@debut, 6, 2)) "/>
    <xsl:variable name="EcartJour" select="number(substring(@fin , 9, 2)) - number(substring(@debut, 9, 2)) "/>


    <xsl:element name="pays">
      <xsl:attribute name="duree" select="concat('P',$EcartAnnee,'Y',$EcartMois,'M',$EcartJour,'D')"/>
      <xsl:apply-templates select="id(@pays)">
        <xsl:sort select="@nom"/>
      </xsl:apply-templates>
    </xsl:element>
</xsl:template>

  <xsl:template match="pays">
    <xsl:attribute name="nom" select="@nom"/>
      <xsl:choose>
        <xsl:when test="language/text() = 'French'">
          <xsl:attribute name="franchophone">Officiel</xsl:attribute>
        </xsl:when>
        <xsl:when test="language/text() = 'French' and  language/@percentage >= 30">
          <xsl:attribute name="franchophone">EnPartie</xsl:attribute>
        </xsl:when>
      </xsl:choose>

  </xsl:template>


</xsl:transform>
