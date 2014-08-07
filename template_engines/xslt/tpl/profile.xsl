<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                exclude-result-prefixes="php"
                xmlns:php="http://php.net/xsl">
    <xsl:output method="xml"
                encoding="UTF-8"
                xml:lang="en_US"
                indent="yes"
                omit-xml-declaration="yes"/>
    <xsl:template match="/root">
        <html>
            <head>
                <title>Agent Profile - XSLT | Contact Registry</title>
                <link rel="stylesheet" type="text/css" href="main.css"/>
            </head>
            <body>
                <h2>Agent Login - XSLT Template</h2>
				<xsl:apply-templates select="rawHTML"/>
                <xsl:apply-templates select="loginFailed"/>
                <form method="post" action="{form_action}">
                    <table id="login">
                        <tr>
                            <th>Username:</th>
                            <td><xsl:value-of select="username"/></td>
                        </tr>
                        <tr>
                            <th>Password:</th>
                            <td><input type="password" id="password" name="password"/></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <input type="submit" value="Log in" class="submit"/>
                            </td>
                        </tr>
                    </table>
                </form>
            </body>
        </html>
    </xsl:template>
    <xsl:template match="loginFailed">
        <div id="errors">
            <h4>Login failed: <xsl:value-of select="err_msg"/></h4>
        </div>
    </xsl:template>
    <xsl:template match="rawHTML">
        <div id="warnings" class="debug">
            <xsl:value-of select="." disable-output-escaping="yes"/>
        </div>
    </xsl:template>
</xsl:stylesheet>