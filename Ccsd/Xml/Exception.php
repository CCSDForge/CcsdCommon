<?php

Class Ccsd_Xml_Exception
{

	public static function HandleXmlError($errno, $errstr, $errfile, $errline)
    {
        if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0)) {
            throw new Exception('source XML incorrecte');
        } else {
            return false;
        }
    }
}