<?php

class Ccsd_Oai_Client {
	
	protected $baseUrl = null;
	private $_outputFormat = null; // format de sortie des verbes OAI : array or xml

	public function __construct( $url = null, $format = 'array')
	{
		if ( null != $url ) {
			$this->baseUrl = $url;
		}
		$this->_outputFormat = $format;
	}
	
	public function getBaseUrl()
	{
		return $this->baseUrl;
	}
	
	public function setBaseUrl( $url = null)
	{
		if ( null != $url ) {
			$this->baseUrl = $url;
		}
	}
	
	public function getOutputFormat()
	{
		return $this->_outputFormat;
	}
	
	public function setOutputFormat( $format = null)
	{
		if ( null != $format ) {
			$this->_outputFormat = $format;
		}
	}
	
	public function identify()
	{
		if ( null == $this->baseUrl ) {
			throw new Exception("URL du serveur OAI-PMH non définie", 0);
		}
		try {
			$dom = new DOMDocument();
			$dom->loadXML((string) file_get_contents($this->baseUrl.'?verb=Identify'));
			$xpath = new DOMXPath($dom);
			foreach (Ccsd_Tools::getNamespaces($dom->documentElement) as $id => $ns) {
				$xpath->registerNamespace($id, $ns);
			}
			$error = self::isOaiError($xpath->query('/xmlns:OAI-PMH/xmlns:error'));
			if ( $error ) {
				throw new Exception($error, 1);
			}
			$record = $xpath->query('//xmlns:Identify');
			if ( $record->length ) {
				return ( $this->_outputFormat == 'array') ? Ccsd_Tools::dom2array($record->item(0)) : $dom->saveXML($record->item(0));
			} else {
				throw new Exception("XML non valide", 0);
			}
		} catch ( Exception $e ) {
			throw new Exception($e->getMessage(), 0);
		}
	}

	public function getRecord($identifier, $format='oai_dc')
	{
		if ( null == $this->baseUrl ) {
			throw new Exception("URL du serveur OAI-PMH non définie", "0");
		}
		try {
			$dom = new DOMDocument();
			$dom->loadXML((string) file_get_contents($this->baseUrl.'?verb=GetRecord&identifier='.$identifier.'&metadataPrefix='.$format));
			$xpath = new DOMXPath($dom);
			foreach (Ccsd_Tools::getNamespaces($dom->documentElement) as $id => $ns) {
				$xpath->registerNamespace($id, $ns);
			}
			$error = self::isOaiError($xpath->query('/xmlns:OAI-PMH/xmlns:error'));
			if ( $error ) {
				throw new Exception($error, 1);
			}
			$record = $xpath->query('//xmlns:GetRecord/xmlns:record');
			if ( $record->length ) {
				return ( $this->_outputFormat == 'array') ? Ccsd_Tools::dom2array($record->item(0)) : $dom->saveXML($record->item(0));
			} else {
				throw new Exception("XML non valide", 0);
			}
		} catch ( Exception $e ) {
			throw new Exception($e->getMessage(), "0");
		}
	}

	private static function isOaiError( DOMNodeList $nodelist )
	{
		if ( $nodelist->length ) {
			return $nodelist->item(0)->getAttribute('code').': '.$nodelist->item(0)->nodeValue;
		}
		return false;
	}
}

