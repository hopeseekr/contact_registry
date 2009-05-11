<?php
// This file is a part of the PHP xLibs Library.
//
// Copyright (c) 2006-2008 Theodore R. Smith
// Contact: hopeseekr@xmule.ws / http://www.xmule.ws/
// DSA-1024 Fingerprint: 10A0 6372 9092 85A2 BB7F 907B CB8B 654B E33B F1ED
//
// This file is licensed under a Creative Commons License:
// * License Details: Creative Commons Attribution License v3.0
//   * See http://creativecommons.org/ylicenses/by/3.0/us/ for details
//   * Key Points:
//       * You are free:
//           * to copy, distribute, display, and perform the work
//           * to make use of the work in its original or modified form
//       * Under the following conditions:
//           * Attribution.You must give the original author credit,
//           * If work is distributed to others, modifications to original
//             must be documented with a unified diff patch provided with
//             the source.

class QuickDOM
{
    private $doc;
    private $lastNode;

    public function __construct(DOMDocument $doc_in = null, $version_in = '1.0', $encoding_in = 'UTF-8')
    {
        if ($doc_in != null)
        {
            $this->setDoc($doc_in);
        }
        else
        {
            $this->setDoc(new DOMDocument($version_in, $encoding_in));
        }
    }

    public function setDoc(DOMDocument $doc_in)
    {
        $this->doc = $doc_in;
        $this->lastNode = $this->doc;
    }
    
    /**
     * @return DOMDocument
     */
    public function getDoc()
    {
        return $this->doc;
    }

    public function import($parent_in, $node_in)
    {
        $parent_in->appendChild($this->doc->importNode($node_in, true));
    }

    public static function load($in)
    {
        try
        {
            $doc = new DOMDocument('1.0');
            $doc->load($in);
        }
        catch(Exception $e)
        {
            backtrace();
            return null;
        }

        return $doc;
    }

    public static function loadXML($in)
    {
        try
        {
            $doc = new DOMDocument('1.0');
            $doc->loadXML($in);
        }
        catch(Exception $e)
        {
            backtrace();
            return null;
        }

        return $doc;
    }
    
    public function addAttr(&$node, $attrs_in=null)
    {
        if (is_array($attrs_in))
        {
            foreach ($attrs_in as $name => $value)
            {
                $node->setAttribute($name, $value);
            }
        }
    }

    public function addNode($input, $attrs_in=null)
    {
        if (is_array($input) == false)
        {
            $input = array($input, null);
        }

        $tmp_node = $this->doc->createElement($input[0]);
        $this->addAttr($tmp_node, $attrs_in);

        if ($input[1] != null)
        {
            $txt_node = $this->doc->createTextNode($input[1]);
            $tmp_node->appendChild($txt_node);
        }

        return $tmp_node;
    }

    public function addQuickText($input, &$parent = null)
    {
        if ($parent == null) { $parent = $this->lastNode; }
        $txt_node = $this->doc->createTextNode($input);

		return $parent->appendChild($txt_node);
    }

    public function addQuickNode($input, &$parent = null, $attrs_in = null)
    {
        if ($parent == null) { $parent = $this->lastNode; }
        if (!$parent)
        {
            trigger_error('$parent is a non-object', E_USER_ERROR);
        }

        if (is_object($input) === false)
        {
            $input = $this->addNode($input, $attrs_in);
        }

        $this->lastNode = $parent->appendChild($input);
        
        return $this->lastNode;
    }

    public function replaceNodes(&$parent, &$child, $input, $attrs_in=null)
    {
        $parent->removeChild($child);
        $tmp_node = $this->addNode($input, $attrs_in);

        return $this->addQuickNode($tmp_node, null, $parent);
    }
}
?>
