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
    
    protected function handleRawHTML()
    {
        /* --- See if there is non-templated output. --- */
        if (ob_get_length() > 0)
        {
            trigger_error('There is raw, non-templated HTML output!', E_USER_NOTICE);
            //$rawHTML = ob_get_clean();
            // Restart ob_start()
            ob_start();

            //$this->block('rawHTML', array('rawHTML' => $rawHTML));
        }
    }
}

?>