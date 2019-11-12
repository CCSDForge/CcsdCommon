<?php

class Ccsd_Sword_Entry {
	public $status; // The HTTP status code returned
	public $xml; // The XML returned by the deposit
	public $statusmessage; // The human readable status code
	public $id; // The atom:id identifier
	public $content_src; // The atom:content value
	public $content_type; // The atom:content value
	public $authors; // The authors
	public $contributors; // The contributors
	public $links; // The links
	public $edit; // The edit URL link
	public $alternate; // The alternate URL link
	public $title; // The title
	public $summary; // The summary
	public $rights; // The rights
	public $updated; // The update date
	public $packaging; // The packaging format used
	public $generator; // The generator
	public $generator_uri; // The uir generator
	public $useragent; // The user agent
	public $noOp; // The noOp status
	              
	// Construct a new deposit response by passing in the http status code
	public function __construct($_status, $thexml) {
		$this->status = $_status;
		$this->xml = $thexml;
		switch ($this->status) {
			case 201 :
			case 202 :
				$this->statusmessage = "Created";
				break;
			case 401 :
				$this->statusmessage = "Unauthorized";
				break;
			case 412 :
				$this->statusmessage = "Precondition failed";
				break;
			case 413 :
				$this->statusmessage = "Request entity too large";
				break;
			case 415 :
				$this->statusmessage = "Unsupported media type";
				break;
			default :
				$this->statusmessage = "Unknown erorr (status code " . $this->status . ")";
				break;
		}
		$this->authors = array ();
		$this->contributors = array ();
		$this->links = array ();
		$this->alternate = '';
		$this->noOp = false;
	}
	
	// Build the workspace hierarchy
	public function buildhierarchy(SimpleXMLElement $dr, $ns) {
		$dr->registerXPathNamespace ( 'atom', 'http://www.w3.org/2005/Atom' );
		// Parse the results
		$this->id = $dr->children(Ccsd_Tools::ifsetor($ns['atom'], ''))->id;
		$contentbits = $dr->xpath ( "atom:content" );
		$this->content_src = isset ( $contentbits[0]['src'] ) ? $contentbits[0]['src'] : '';
		$this->content_type = isset ( $contentbits[0]['type'] ) ? $contentbits[0]['type'] : '';
		// Store the authors
		foreach ( $dr->children ( Ccsd_Tools::ifsetor ( $ns ['atom'], '' ) )->author as $author ) {
			$theauthor = $author->children ( Ccsd_Tools::ifsetor ( $ns ['atom'], '' ) )->name . "";
			$this->authors [] = $theauthor;
		}
		// Store the contributors
		foreach ( $dr->children ( Ccsd_Tools::ifsetor ( $ns ['atom'], '' ) )->contributor as $contributor ) {
			$thecontributor = $contributor->children ( Ccsd_Tools::ifsetor ( $ns ['atom'], '' ) )->name . "";
			$this->contributors [] = $thecontributor;
		}
		// Store the links
		foreach ( $dr->xpath ( "atom:link" ) as $link ) {
			$this->links [] = Ccsd_Tools::space_clean ( $link [0] ['href'] );
			if ($link [0] ['rel'] == 'edit') {
				$this->edit = Ccsd_Tools::space_clean ( $link [0] ['href'] );
			}
			if ($link [0] ['rel'] == 'alternate') {
				$this->alternate = Ccsd_Tools::space_clean ( $link [0] ['href'] );
			}
		}
		// Store the title and summary
		$this->title = Ccsd_Tools::space_clean ( $dr->children ( Ccsd_Tools::ifsetor ( $ns ['atom'], '' ) )->title );
		$this->summary = Ccsd_Tools::space_clean ( $dr->children ( Ccsd_Tools::ifsetor ( $ns ['atom'], '' ) )->summary );
		// Store the updated date
		$this->updated = $dr->children ( Ccsd_Tools::ifsetor ( $ns ['atom'], '' ) )->updated;
		// Store the rights
		$this->rights = Ccsd_Tools::space_clean ( $dr->children ( Ccsd_Tools::ifsetor ( $ns ['atom'], '' ) )->rights );
		// Store the format namespace
		$this->packaging = $dr->children ( Ccsd_Tools::ifsetor ( $ns ['atom'], '' ) )->packaging;
		// Store the generator
		$this->generator = Ccsd_Tools::space_clean ( $dr->children ( Ccsd_Tools::ifsetor ( $ns ['atom'], '' ) )->generator );
		$gen = $dr->xpath ( "atom:generator" );
		$this->generator_uri = isset ( $gen [0] ['uri'] ) ? $gen [0] ['uri'] : '';
		// Store the user agent
		$this->useragent = Ccsd_Tools::space_clean ( $dr->children ( Ccsd_Tools::ifsetor ( $ns ['atom'], '' ) )->userAgent );
		// Store the noOp status
		if (strtolower ( ( string ) $dr->children ( Ccsd_Tools::ifsetor ( $ns ['atom'], '' ) )->noOp ) == 'true') {
			$this->noOp = true;
		}
	}
}
