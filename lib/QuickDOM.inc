<?php
// This file is a part of the PHP xLibs Library.
//
// Copyright (c) 2006, 2007 Theodore R. Smith
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

require_once('debug.inc');

class QuickDOM
{
    private $doc;

    public function __construct($doc_in=null)
    {
        if ($doc_in != null)
        {
            $this->doc = $doc_in;
        }
    }

    public function SetDoc($doc_in)
    {
        $this->doc = $doc_in;
    }

    public function QuickImport($parent_in, $node_in)
    {
        $parent_in->appendChild($this->doc->importNode($node_in, true));

    }

    public function QuickAttr(&$node, $attrs_in=null)
    {
        if (is_array($attrs_in))
        {
            foreach ($attrs_in as $name => $value)
            {
                $node->setAttribute($name, $value);
            }
        }
    }

    public function FastNode($input, $attrs_in=null)
    {
        if (is_array($input) == false)
        {
            $input = array($input, null);
        }

        $tmp_node = $this->doc->createElement($input[0]);
        $this->QuickAttr($tmp_node, $attrs_in);

        if ($input[1] != null)
        {
            $txt_node = $this->doc->createTextNode($input[1]);
            $tmp_node->appendChild($txt_node);
        }

        return $tmp_node;
    }

    public function QuickText(&$parent, $input)
    {
        $txt_node = $this->doc->createTextNode($input);

		return $parent->appendChild($txt_node);
    }

    public function QuickNode(&$parent, $input, $attrs_in=null)
    {
        if (!$parent)
        {
            trigger_error('$parent is a non-object', E_USER_ERROR);
        }

        if (is_object($input) === false)
        {
            $input = $this->FastNode($input, $attrs_in);
        }

        return $parent->appendChild($input);
    }

    public function QuickTextNode(&$parent, $xml_in, $txt_in, $attrs_in=null)
    {
        trigger_error('<em>XNA::QuickTextNode is deprecated:</em> use XNA::QuickNode instead.<br/>');

        return $this->QuickNode($parent, array($xml_in, $txt_in), $attrs_in);
    }

    public function QuickReplace(&$parent, &$child, $input, $attrs_in=null)
    {
        $parent->removeChild($child);
        $tmp_node = $this->FastNode($input, $attrs_in);

        return $this->QuickNode($parent, $tmp_node);
    }

    public static function load($in)
    {
        restore_error_handler();

        $error_level = ini_get('error_reporting');
        error_reporting($error_level & ~E_STRICT);

        try
        {
            $doc = DOMDocument::load($in);
        }
        catch(Exception $e)
        {
            backtrace();
            return null;
        }

        error_reporting($error_level);
        /* --- I really don't like this hack, but what are my options? :-/ */
        set_error_handler('error_report');
        return $doc;
    }

    public static function loadXML($in)
    {
        restore_error_handler();

        $error_level = ini_get('error_reporting');
        error_reporting($error_level & ~E_STRICT);

        try
        {
            $doc = DOMDocument::loadXML($in);
        }
        catch(Exception $e)
        {
            backtrace();
            return null;
        }

        error_reporting($error_level);
        /* --- I really don't like this hack, but what are my options? :-/ */
        set_error_handler('error_report');
        return $doc;
    }
}
?>
