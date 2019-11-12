<?php

class Ccsd_Referentiels_Exception_ReferentStructException extends Exception {

	public function __construct ($message = null, $code = null, $previous = null) 
	{
		$this->message = "Vous ne pouvez modifier que des structures dont vous êtes référent";
		$this->code = $code;
	}
	
}