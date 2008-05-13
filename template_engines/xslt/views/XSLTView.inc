<?php


class XSLTView
{
    protected $xsl_file;
    protected $doc;

    public function __construct()
    {
        $xdom_file = realpath(dirname(__FILE__) . '/../lib/XDOMDocument.inc');
        require_once($xdom_file);
    }

    public function parse()
    {
        $xsl = new XSLTProcessor();
        $xsl->importStylesheet(DOMDocument::load($this->xsl_file));

        echo $xsl->transformToXML($this->doc);
    }
}

?>