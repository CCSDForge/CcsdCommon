<?php

class Ccsd_Sword_Workspace {
	public $workspacetitle; // The title of the workspace
	public $collections; // Collections in the workspace
	                     
	// Construct a new workspace by passing in a title
	public function __construct($_title) {
		$this->workspacetitle = $_title;
	}
	
	// Build the collection hierarchy
	public function buildhierarchy($colls, $ns) {
		foreach ( $colls as $collection ) {
			$newcollection = new Ccsd_Sword_Collection ( Ccsd_Tools::space_clean ( $collection->children ( $ns ['atom'] )->title ) );
			// The location of the service document
			$newcollection->href = $collection['href'];
			// An array of the accepted deposit types
			foreach ( $collection->accept as $accept ) {
				$newcollection->accept[] = $accept;
			}
			// An array of the accepted packages
			foreach ( $collection->xpath ( "sword:acceptPackaging" ) as $acceptpackaging ) {
				$newcollection->addAcceptPackaging ( $acceptpackaging [0] );
			}
			// Add the collection policy
			$newcollection->collpolicy = Ccsd_Tools::space_clean ( $collection->children ( $ns ['sword'] )->collectionPolicy );
			// Add the collection abstract
			$newcollection->abstract = Ccsd_Tools::space_clean ( $collection->children ( $ns ['dcterms'] )->abstract );
			// Find out if mediation is allowed
			if ($collection->children ( $ns['sword'] )->mediation == 'true') {
				$newcollection->mediation = true;
			} else {
				$newcollection->mediation = false;
			}
			// Add a nested service document if there is one
			$newcollection->service = Ccsd_Tools::space_clean ( $collection->children ( $ns ['sword'] )->service );
			// Add to the collections in this workspace
			$this->collections [] = $newcollection;
		}
	}
}
