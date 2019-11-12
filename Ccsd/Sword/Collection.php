<?php

class Ccsd_Sword_Collection {
	public $colltitle; // The title of the collection
	public $href; // The URL of the collection
	public $accept; // The types of content accepted
	public $acceptpackaging; // The accepted packaging formats
	public $collpolicy; // The collection policy
	public $abstract; // The collection abstract
	public $mediation; // Whether mediation is allowed or not
	public $service; // A nested service document

    public function __construct($_title) {
		$this->colltitle = space_clean ( $_title );
		$accept = array ();
		$acceptpackaging = array ();
	}

	public function addAcceptPackaging($ap) {
		$format = ( string ) $ap [0];
		$q = ( string ) $ap [0] ['q'];
		if (empty ( $q )) {
			$q = "1.0";
		}
		$this->acceptpackaging [$format] = $q;
	}
}
