<?php
class XDOMDocument extends DOMDocument {

    function __construct($version = null, $encoding = null) {
        parent::__construct($version, $encoding);
    }

    public function createElement($tagName, $value=null) {
        $node=parent::createElement($tagName);
        if (!is_null($value)) {
            $node->appendChild(
                $this->createTextNode(utf8_encode($value))
            );
        }
        return $node;
    }
   
    public function createElementNS($namespaceURI, $qualifiedName, $value=null) {
        $node=parent::createElementNS($namespaceURI, $qualifiedName);
        if (!is_null($value)) {
            $node->appendChild(
                $this->createTextNode(utf8_encode($value))
            );
        }
        return $node;
    }
}
?>
