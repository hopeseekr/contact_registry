<?php

class LoginView extends XSLTView
{
    public function __construct($tmpl_file)
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
