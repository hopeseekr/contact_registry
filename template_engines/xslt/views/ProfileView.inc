<?php

class ProfileView extends XSLTView
{
    public function __construct($tmpl_file, $zone = null)
    {
		parent::__construct($tmpl_file);
        
		$dom = new QuickDOM($this->doc);
		$dom->addQuickNode('form_action', $this->doc->lastChild);
			$dom->addQuickText(site_uri('login', 'login'));
		$dom->addQuickNode('username', $this->doc->lastChild);
			$dom->addQuickText('tsmith');
    }
}
