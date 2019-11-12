<?php

class Ccsd_Sword_Servicedocument {
	public $status; // The HTTP status code returned
	public $xml; // The XML of the service doucment
	public $statusmessage; // The human readable status code
	public $version; // The version of the SWORD server
	public $verbose; // Whether or not verbose output is supported
	public $noop; // Whether or not the noOp command is supported
	public $maxuploadsize; // The max upload size of deposits
	public $workspaces; // Workspaces in the servicedocument
	                    
	// Construct a new servicedocument by passing in the http status code
	public function __construct($_status, $thexml) {
		$this->status = $_status;
		$this->xml = $thexml;
		switch ($this->status) {
			case 200 :
				$this->statusmessage = "OK";
				break;
			case 401 :
				$this->statusmessage = "Unauthorized";
				break;
			case 404 :
				$this->statusmessage = "Service document not found";
				break;
			default :
				$this->statusmessage = "Unknown erorr (status code " . $this->status . ")";
				break;
		}
	}
	public function buildhierarchy($ws, $ns) {
		foreach ( $ws as $workspace ) {
			$newworkspace = new Ccsd_Sword_Workspace ( $workspace->children ( $ns['atom'] )->title );
			$newworkspace->buildhierarchy ( $workspace->children(), $ns );
			$this->workspaces[] = $newworkspace;
		}
	}
}

?>