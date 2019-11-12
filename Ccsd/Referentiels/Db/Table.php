<?php

/**
 * Ajout des mecanisme de timestamp à chaque insertion modification
 * On suppose que les colonnes DATE_CREATION et DATE_MODIF sont toujours présentes
 * @author Ln
 *
 */

Class Ccsd_Referentiels_Db_Table extends Zend_Db_Table_Abstract {

	/* tableau contenant les type d'éléments de la classe
	 * indispensable pour la création des formulaires
	 * et leur validation */
 
    
	public $elementsDate = array();
	public $elementsRequis = array();
	public $elementsNumeriques = array();
	public $elementsISSN = array();
	public $elementsEmail = array();
	public $elementsTailleLimitee = array();
	public $elementsNonModifiables = array();
	public $instance = 'REFHAL';
	
	const MAX_RESULT = 20;	
	
	protected $_rowClass = 'Ccsd_Referentiels_Db_Table_Row';
	

	public function nomTable()
	{
	    return $this->_name;
	}

	public function setOptions(array $options)
	{
	    parent::setOptions($options);
	    if (array_key_exists('instance', $options) ) {
	        $this->instance = $options['instance'];
	    }
	}
	
    public function search_solr_pagine($chaine, $core, $nbResultatsParPage=0){
        //Ccsd_Tools::debug("critere : " . $chaine . " core : " . $core);
        $monAdapter = new Ccsd_Paginator_Adapter_Curl($chaine, $core);
        $paginator = new Zend_Paginator($monAdapter);
        $paginator->setItemCountPerPage( ($nbResultatsParPage > 0) ? $nbResultatsParPage : self::MAX_RESULT );           
        return ($paginator);

    }
    
    public function search_base_pagine($select, $nbResultatsParPage=0) {
        $monAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($monAdapter);
        $paginator->setItemCountPerPage( ($nbResultatsParPage > 0) ? $nbResultatsParPage : self::MAX_RESULT );
        return ($paginator);
    }

    public function search_unique_solr($id, $core){
           
        $resultat = self::search("docid:" . $id);
         if ( count($resultat) != 1) {
            return FALSE;
        }     
        return ($resultat);
    
    }
    
/*
 * Recherche solR commune 
 * q= text:monNom,valid_s:VALID
 */
    

   public static function search ($q,  $nbResultats = 10)
   {
        $return = array ();
    
        try {

           $queryString = "q=" . $q . "&rows=" . $nbResultats. "&wt=phps";
           $queryString = rtrim ($queryString, ",");

           $d = Ccsd_Tools::solrCurl($queryString, static::$_core);
           $d = unserialize($d);

           return $d['response']['docs'];

        } catch (Exception $e) {
            return array ();
        }
         
        return $return;
    }
    
    /*
     * Renvoie un objet de type Ccsd_Referentiel_Table_Row_Journal
    */
    public function getBase($id) {
        $o = $this->find($id);
         
        if (!$o) {
            return false;
        }
         
        return $o->current();
    }

    /**
     * @TODO à tester
     * @param unknown_type $value
     * @return boolean
     */
    public function isValid ($value) {
        if (!is_array ($value)) {
            $value = array ($value);
        }
         
        $valid = true;
        
        foreach ($value as $v) {
            if (!$v instanceof Zend_Db_Table_Row_Abstract) {
                continue;
            }
            
            $valid = $valid && $v->getForm()->isValid($v->toArray());
        }
         
        return $valid;
    }
    
}