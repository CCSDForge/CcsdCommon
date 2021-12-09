<?php

interface Ccsd_Form_Interface_Javascript
{
    public function addDocumentReady ($code);
    public function addFunction ($function);
    public function getJavascript ($type = null, $name = null);
    public function setJavascript ($js, $type = null, $name = null);
    public function clearJavascript ();
}