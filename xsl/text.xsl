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

 <xsl:include href="xhtml.xsl"/>
   
 <xsl:template match="prose//l">
  <p class="pr"><xsl:apply-templates/></p>
 </xsl:template>

 <xsl:template match="stihi//l">
  <p class="sti"><xsl:if test="@sp &gt; 0"><xsl:call-template name="stihi-sp"><xsl:with-param name="i"><xsl:value-of select="@sp"/></xsl:with-param></xsl:call-template></xsl:if>
  <xsl:apply-templates/></p>
 </xsl:template>
 
 <xsl:template match="plain">
 	<p class="pl"><xsl:apply-templates/></p>
 </xsl:template>
 
 <xsl:template match="plain//l">
 	<xsl:apply-templates/><br/>
 </xsl:template>
 
 <xsl:template match="plain//prose//l">
 	<p class="pr"><xsl:apply-templates/></p>
 </xsl:template>
 
 <xsl:template match="plain//stihi//l">
  <p class="sti"><xsl:if test="@sp &gt; 0"><xsl:call-template name="stihi-sp"><xsl:with-param name="i"><xsl:value-of select="@sp"/></xsl:with-param></xsl:call-template></xsl:if>
  <xsl:apply-templates/></p>
 </xsl:template>

 <xsl:template name="stihi-sp">
  <xsl:param name="i"/>
  <xsl:call-template name="nbsp"/>
  <xsl:if test="$i &gt; 0">
   <xsl:call-template name="stihi-sp"><xsl:with-param name="i"><xsl:value-of select="$i - 1"/></xsl:with-param></xsl:call-template>
  </xsl:if>
 </xsl:template>

 <xsl:template match="separator">
  <br />
 </xsl:template>

 <xsl:template match="c">
  <h3><xsl:apply-templates/></h3>
 </xsl:template>

 <xsl:template match="align-center">
  <div align="center" class="align-center"><xsl:apply-templates/></div>
 </xsl:template>
 <xsl:template match="align-left">
  <div align="left" class="align-left"><xsl:apply-templates/></div>
 </xsl:template>
 <xsl:template match="align-right">
  <div align="right" class="align-right"><xsl:apply-templates/></div>
 </xsl:template>

 <xsl:template match="url">
  <xsl:choose>
   <xsl:when test="starts-with(@href, '/') or starts-with(@url, '/') or (not(@href) and starts-with(., '/'))">
    <a><xsl:call-template name="url-template"/></a>
   </xsl:when>
   <xsl:otherwise><noindex><a target="_blank"><xsl:call-template name="url-template"/></a></noindex></xsl:otherwise>
  </xsl:choose>
 </xsl:template>
 <xsl:template name="url-template">
  <xsl:attribute name="href"><xsl:choose><xsl:when test="not(@href) and not(@url)"><xsl:value-of select="self::node()"/></xsl:when><xsl:when test="not(@href)"><xsl:value-of select="@url"/></xsl:when><xsl:otherwise><xsl:value-of select="@href"/></xsl:otherwise></xsl:choose></xsl:attribute>
  <xsl:apply-templates/>
 </xsl:template>

 <xsl:template match="attachment">
  <xsl:value-of select="php:functionString('ws_attach::link', @id, self::node())" disable-output-escaping="yes"/>
 </xsl:template>
 
 <!--<xsl:template match="im">
  <xsl:value-of select="php:functionString('ws_im_item::im', self::node())" disable-output-escaping="yes"/>
 </xsl:template>-->
 
 <xsl:template match="ot">
  <span style="color:gray"><xsl:apply-templates/></span>
 </xsl:template>
 
 <xsl:template match="quote">
 	<div class="quote" title="Цитация"><xsl:apply-templates/></div>
 </xsl:template>
 
 
 
 <xsl:template name="dot">
  <xsl:text disable-output-escaping="yes">&amp;nbsp;&amp;#149;&amp;nbsp;</xsl:text>
 </xsl:template>
 <xsl:template name="nbsp">
  <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
 </xsl:template>
 <xsl:template match="e"><xsl:text disable-output-escaping="yes">&amp;</xsl:text><xsl:value-of select="@n"/>;</xsl:template>
 
  <xsl:template match="user">
  <a>
   <xsl:attribute name="href">
    <xsl:choose>
     <xsl:when test="@user !=''"><xsl:value-of select="php:function('ws_user::call', php:functionString('ws_user::getIdByLogin', @user), 'href')" disable-output-escaping='yes'/></xsl:when>
     <xsl:otherwise><xsl:value-of select="php:functionString('ws_user::call', @id, 'href')" disable-output-escaping='yes'/></xsl:otherwise>
    </xsl:choose>
   </xsl:attribute>
   <xsl:choose>
    <xsl:when test="self::node() != ''"><xsl:value-of select="self::node()"/></xsl:when>
    <xsl:when test="@user != ''"><xsl:value-of select="php:functionString('ws_user::call', php:functionString('ws_user::getIdByLogin', @user), 'name')"/></xsl:when>
    <xsl:otherwise><xsl:value-of select="php:functionString('ws_user::call', @id, 'name')"/></xsl:otherwise>
   </xsl:choose>
  </a>
 </xsl:template>
</xsl:stylesheet>