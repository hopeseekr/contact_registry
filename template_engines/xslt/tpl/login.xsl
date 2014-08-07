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
                <title>Consultant Login - XSLT | SBConsultants</title>
                <link rel="stylesheet" type="text/css" href="main.css"/>
            </head>
            <body>
                <h2>Consultant Login - XSLT Template</h2>
<!--            {{ BEGIN debug }}
                <pre>{{ $print_r }}</pre>
            {{ END }}
            {{ BEGIN loginFailed }}
                <div id="errors">
                    <h4>Login failed: {{ $err_msg }}</h4>
                </div>
            {{ END }}-->
                <form method="post" action="{form_action}">
                    <table id="login">
                        <tr>
                            <th>Username:</th>
                            <td><input type="text" id="username" name="username" value="{username}"/></td>
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
    <xsl:template match="rawHTML">
        <div id="warnings">
            <xsl:value-of select="."/>
        </div>
    </xsl:template>
</xsl:stylesheet>