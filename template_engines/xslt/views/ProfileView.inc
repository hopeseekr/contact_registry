<?php

class ProfileView extends XSLTView
{
    public function __construct($tmpl_file, $zone = null)
    {
        $dom = new QuickDOM();

        $node1 = $dom->addQuickNode('root');
            $dom->addQuickNode('form_action');
                $dom->addQuickText(site_uri('login', 'login'));
        $dom->addQuickNode('username', $node1);
                $dom->addQuickText('tsmith');

        $this->handleRawHTML();
        $this->doc = $dom->getDoc();
        
        return parent::__construct($tmpl_file);
    }
}
