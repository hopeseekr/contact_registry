<?php
class LoginView extends Blitz
{
    public function __construct($tmpl_file)
    {
        $tmpl_file = realpath(dirname(__FILE__) . '/../tpl/' . $tmpl_file);

        if (!file_exists($tmpl_file))
        {
            throw new Exception('Cannot find Blitz template file: ' . $tmpl_file);
        }

        $status = parent::Blitz($tmpl_file);

        $this->set(array('title' => 'Agent Login'));
        $this->handleRawHTML();

        return $status;
    }

    private function handleRawHTML()
    {
        /* --- See if there is non-templated output. --- */
        if (ob_get_length() > 0)
        {
            trigger_error('There is raw, non-templated HTML output!', E_USER_NOTICE);
            $rawHTML = ob_get_clean();
            // Restart ob_start()
            ob_start();

            $this->block('rawHTML', array('rawHTML' => $rawHTML));
        }
    }
}
?>