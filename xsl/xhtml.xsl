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

 <!--
  Минимум xhtml-тегов с аттрибутами
 -->

 <xsl:template match="br">
  <br>
   <xsl:call-template name="set-class"/>
  </br>
 </xsl:template>

 <xsl:template match="l">
  <xsl:apply-templates/><br />
 </xsl:template>

 <xsl:template match="a">
  <a>
   <xsl:call-template name="set-class"/>
   <xsl:call-template name="set-id"/>
   <xsl:choose>
    <xsl:when test="@href != ''">
     <xsl:attribute name="href"><xsl:value-of select="@href" disable-output-escaping="yes"/></xsl:attribute>
     <xsl:if test="@target != ''">
      <xsl:attribute name="target"><xsl:value-of select="@target"/></xsl:attribute>
     </xsl:if>
     <xsl:if test="@onclick != ''">
      <xsl:attribute name="onclick"><xsl:value-of select="@onclick"/></xsl:attribute>
     </xsl:if>
     <xsl:apply-templates/>
    </xsl:when>
    <xsl:when test="@name != ''">
     <xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
     <xsl:text> </xsl:text>
    </xsl:when>
   </xsl:choose>
  </a>
 </xsl:template>

 <xsl:template match="img">
  <img src="{@src}" border="0" alt="{@alt}" title="{@alt}">
   <xsl:call-template name="set-class"/>
   <xsl:call-template name="set-width"/><xsl:call-template name="set-height"/>
  </img>
 </xsl:template>

 <xsl:template match="noindex">
  <noindex><xsl:apply-templates/></noindex>
 </xsl:template>

 <!-- Строчные теги, заголовки -->
 <xsl:template match="b">
  <b><xsl:apply-templates/></b>
 </xsl:template>
 <xsl:template match="center">
  <center><xsl:apply-templates/></center>
 </xsl:template>
 <xsl:template match="i">
  <i><xsl:apply-templates/></i>
 </xsl:template>
 <xsl:template match="u">
  <u><xsl:apply-templates/></u>
 </xsl:template>
 <xsl:template match="tt">
  <tt><xsl:apply-templates/></tt>
 </xsl:template>
 <xsl:template match="s">
  <s><xsl:apply-templates/></s>
 </xsl:template>
 <xsl:template match="strong">
  <strong><xsl:apply-templates/></strong>
 </xsl:template>
 <xsl:template match="big">
  <big><xsl:call-template name="set-class"/><xsl:apply-templates/></big>
 </xsl:template>
 <xsl:template match="small">
  <small><xsl:call-template name="set-class"/><xsl:apply-templates/></small>
 </xsl:template>
 <xsl:template match="sup">
  <sup><xsl:call-template name="set-class"/><xsl:apply-templates/></sup>
 </xsl:template>
 <xsl:template match="sub">
  <sub><xsl:call-template name="set-class"/><xsl:apply-templates/></sub>
 </xsl:template>
 <xsl:template match="h1">
  <h1><xsl:call-template name="set-class"/><xsl:apply-templates/></h1>
 </xsl:template>
 <xsl:template match="h2">
  <h2><xsl:call-template name="set-class"/><xsl:apply-templates/></h2>
 </xsl:template>
 <xsl:template match="h3">
  <h3><xsl:call-template name="set-class"/><xsl:apply-templates/></h3>
 </xsl:template>
 <xsl:template match="h4">
  <h4><xsl:call-template name="set-class"/><xsl:apply-templates/></h4>
 </xsl:template>
 <xsl:template match="h5">
  <h5><xsl:call-template name="set-class"/><xsl:apply-templates/></h5>
 </xsl:template>
 <xsl:template match="h6">
  <h6><xsl:call-template name="set-class"/><xsl:apply-templates/></h6>
 </xsl:template>
 <xsl:template match="span">
  <span><xsl:call-template name="set-class"/><xsl:call-template name="set-id"/><xsl:apply-templates/></span>
 </xsl:template>

 <!-- Списки -->
 <xsl:template match="ul">
  <ul>
   <xsl:call-template name="set-class"/><xsl:call-template name="set-id"/>
   <xsl:apply-templates select="li"/>
  </ul>
 </xsl:template>
 <xsl:template match="ol">
  <ol type="{@type}">
   <xsl:call-template name="set-class"/><xsl:call-template name="set-id"/>
   <xsl:apply-templates select="li"/>
  </ol>
 </xsl:template>
 <xsl:template match="li">
  <li>
   <xsl:call-template name="set-class"/><xsl:call-template name="set-id"/>
   <xsl:apply-templates/>
  </li>
 </xsl:template>

 <!-- Блочные теги -->
 <xsl:template match="div">
  <div>
   <xsl:if test="@style != ''">
    <xsl:attribute name="style"><xsl:value-of select="@style"/></xsl:attribute>
   </xsl:if>
   <xsl:call-template name="set-class"/><xsl:call-template name="set-id"/><xsl:apply-templates/></div>
 </xsl:template>
 <xsl:template match="p">
  <p><xsl:call-template name="set-class"/><xsl:call-template name="set-id"/><xsl:apply-templates/></p>
 </xsl:template>
 <xsl:template match="table">
  <table>
   <xsl:call-template name="set-class"/><xsl:call-template name="set-id"/><xsl:call-template name="set-width"/><xsl:call-template name="set-height"/>
   <xsl:if test="@cellpadding != ''">
    <xsl:attribute name="cellpadding"><xsl:value-of select="@cellpadding"/></xsl:attribute>
   </xsl:if>
   <xsl:if test="@cellspacing != ''">
    <xsl:attribute name="cellspacing"><xsl:value-of select="@cellspacing"/></xsl:attribute>
   </xsl:if>
   <xsl:if test="@border != ''">
    <xsl:attribute name="border"><xsl:value-of select="@border"/></xsl:attribute>
   </xsl:if>
   <xsl:if test="caption != ''"><caption><xsl:apply-templates select="caption"/></caption></xsl:if>
   <xsl:if test="count(colgroup) &gt; 0">
    <xsl:for-each select="colgroup">
     <colgroup>
      <xsl:call-template name="set-width"/>
      <xsl:call-template name="set-valign"/>
      <xsl:call-template name="set-halign"/>
      <xsl:call-template name="set-class"/>
      <xsl:for-each select="col">
       <col>
        <xsl:call-template name="set-width"/>
        <xsl:call-template name="set-valign"/>
        <xsl:call-template name="set-halign"/>
        <xsl:call-template name="set-class"/>
       </col>
      </xsl:for-each>
     </colgroup>
    </xsl:for-each>
   </xsl:if>
   <xsl:for-each select="tr">
    <tr>
     <xsl:call-template name="set-width"/>
     <xsl:call-template name="set-height"/>
     <xsl:call-template name="set-valign"/>
     <xsl:call-template name="set-halign"/>
     <xsl:call-template name="set-class"/>
     <xsl:apply-templates select="td|th|cache"/>
    </tr>
   </xsl:for-each>
  </table>
 </xsl:template>
 <xsl:template match="td">
  <td>
   <xsl:call-template name="set-width"/>
   <xsl:call-template name="set-valign"/>
   <xsl:call-template name="set-halign"/>
   <xsl:call-template name="set-class"/>
   <xsl:call-template name="set-id"/>
   <xsl:if test="@colspan != ''"><xsl:attribute name="colspan"><xsl:value-of select="@colspan"/></xsl:attribute></xsl:if>
   <xsl:if test="@rowspan != ''"><xsl:attribute name="rowspan"><xsl:value-of select="@rowspan"/></xsl:attribute></xsl:if>
   <xsl:apply-templates/>
  </td>
 </xsl:template>
 <xsl:template match="th">
  <th>
   <xsl:call-template name="set-width"/>
   <xsl:call-template name="set-valign"/>
   <xsl:call-template name="set-halign"/>
   <xsl:call-template name="set-class"/>
   <xsl:call-template name="set-id"/>
   <xsl:if test="@colspan != ''"><xsl:attribute name="colspan"><xsl:value-of select="@colspan"/></xsl:attribute></xsl:if>
   <xsl:if test="@rowspan != ''"><xsl:attribute name="rowspan"><xsl:value-of select="@rowspan"/></xsl:attribute></xsl:if>
   <xsl:apply-templates/>
  </th>
 </xsl:template>

 <xsl:template name="set-class">
  <xsl:if test="@class != ''">
   <xsl:attribute name="class"><xsl:value-of select="@class"/></xsl:attribute>
  </xsl:if>
 </xsl:template>
 <xsl:template name="set-id">
  <xsl:if test="@id != ''">
   <xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
  </xsl:if>
 </xsl:template>

 <xsl:template name="set-halign">
  <xsl:if test="@align != ''">
   <xsl:attribute name="align"><xsl:value-of select="@align"/></xsl:attribute>
  </xsl:if>
 </xsl:template>
 <xsl:template name="set-valign">
  <xsl:if test="@valign != ''">
   <xsl:attribute name="valign"><xsl:value-of select="@valign"/></xsl:attribute>
  </xsl:if>
 </xsl:template>

 <xsl:template name="set-width">
  <xsl:if test="@width != ''">
   <xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute>
  </xsl:if>
 </xsl:template>
 <xsl:template name="set-height">
  <xsl:if test="@height != ''">
   <xsl:attribute name="height"><xsl:value-of select="@height"/></xsl:attribute>
  </xsl:if>
 </xsl:template>
</xsl:stylesheet>