<?php

class Ccsd_Referentiels_Exception_IDHalException extends Exception {

	public function __construct ($message = null, $code = null, $previous = null) 
	{
		$this->message = "les IdHals des auteurs sélectionnés sont différents et ne peuvent donc pas être remplacés ensemble";
		$this->code = $code;
	}
	
}