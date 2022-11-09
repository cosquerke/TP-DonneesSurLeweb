<?xml version="1.0" encoding="UTF-8"?> <!-- ok -->
<!-- java -jar ./saxon9he.jar -xsl:tp.xsl -s:tp.xml -->
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="2.0">
  <xsl:output method="html" indent="yes" doctype-system="res.dtd"/>


  <xsl:template match="/">
    <xsl:element name="liste-présidents">
      <xsl:apply-templates select="/déplacements/liste-personnes/personne[fonction/@type = 'Président de la République']"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="personne">
    <xsl:element name="président">
      <xsl:attribute name="nom" select="@nom"/>
    </xsl:element>
  <xsl:apply-templates select="/déplacements/liste-visites/visite[@personne = current()/fonction/@xml:id]"/>
  <!--<xsl:apply-templates select="id(/déplacements/liste-visites/visite[@personne = current()/fonction/@xml:id]/@pays)[encompassed/@continent='africa']"/>-->

  </xsl:template>

  <xsl:template match="visite">
    <xsl:element name="pays">
      <xsl:attribute name="durée" select="@debut - @fin"/>
      <xsl:apply-templates select="id(@pays)[encompassed/@continent='africa']"/>
    </xsl:element>
</xsl:template>

  <xsl:template match="pays">
      <xsl:choose>
        <xsl:when test="language/text() = 'French'">
          <xsl:attribute name="franchophone" select="Officiel"/>
        </xsl:when>
        <xsl:when test="language/text() = 'French' and  language/@percentage >= 30">
          <xsl:attribute name="franchophone" select="EnPartie"/>
        </xsl:when>
      </xsl:choose>
      <xsl:attribute name="nom" select="@nom"/>
  </xsl:template>


</xsl:transform>
