<?php
interface ComplexManagerInterface
{
    public static function load($record_in = null);
    public function fetchCount();
    public function fetchDetails();
}
?>