<?php

class Ccsd_Sword_Errordocument extends Ccsd_Sword_Entry {
	public $erroruri; // The error URI
	                  
	// Construct a new deposit response by passing in the http status code
	public function __construct($_status, $thexml) {
		parent::__construct ( $_status, $thexml );
	}
	
	// Build the error document hierarchy
	public function buildhierarchy(SimpleXMLElement $dr, $ns) {
		parent::buildhierarchy ( $dr, $ns );
		$this->erroruri = ( string ) $dr->attributes ()->href;
	}
}
