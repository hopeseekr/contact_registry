<?php

require_once 'lib/QuickDOM.inc';

class XSLTView implements ViewInterface
{
    protected $xsl_file;
    protected $doc;

	public function __construct($tmpl_file)
	{
		$tmpl_file = realpath(dirname(__FILE__) . '/../tpl/' . $tmpl_file);

		if (!file_exists($tmpl_file))
		{
			throw new Exception('Cannot find XSLT template file: ' . $tmpl_file);
		}

		$this->xsl_file = $tmpl_file;
	}

	public function parse()
	{
		if (isset($_GET['xml']))
		{
			$this->doc->formatOutput = true;
			echo '<pre>', htmlspecialchars($this->doc->saveXML()), '</pre>';
			exit;
		}

		$xsl = new XSLTProcessor();
		$xsl->importStylesheet(DOMDocument::load($this->xsl_file));

		echo $xsl->transformToXML($this->doc);
	}
    
	public function block($context, array $params = null)
	{
		$dom = new QuickDOM($this->doc);

		$root = $this->doc->firstChild;
		$node1 = $dom->addQuickNode($context, $root);

		foreach ($params as $key => $value)
		{
			$dom->addQuickNode($key, $node1);
			$dom->addQuickText($value);
		}
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

            $this->block('rawHTML', array('rawHTML' => $rawHTML));
        }
    }
}

?>