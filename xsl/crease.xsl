<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:mr="http://www.mirari.ru"
 xmlns:php="http://php.net/xsl"
  xsl:extension-element-prefixes="php mr">
 <xsl:output method="xml"
  omit-xml-declaration="yes"
  encoding="utf-8"
  />

 <xsl:include href="text.xsl"/>
   
 <xsl:template match="crease">
 	<center><span style="border-bottom:1px dashed black">
 	<xsl:choose>
 		<xsl:when test="@title"><xsl:value-of select="@title"/></xsl:when>
 		<xsl:otherwise>(Что-то спрятано в складку)</xsl:otherwise>
 	</xsl:choose>
 	</span></center>
 </xsl:template>
</xsl:stylesheet>