<?php

/**
 * Table gérant spécifiquement les tables de type arborescent
 * 
 * @author Ln
 *
 *
 *STRUCTURE
 *	ID
 *	CODE
 *	BORNE_INF
 *	BORNE_SUP
 */

Class Ccsd_Referentiels_Db_ThesaurusTable extends Ccsd_Referentiels_Db_Table {
	
	/* mise à jour automatique des colonnes bornes
	 **/
	
	/* J'insere toujours un element à gauche d'un autre où 
	 * borne_inf et borne_sup sont les caractéristiques de cet élément
	 * 
	 * 
	 * 
	 * UPDATE SET BORNE_INF = BORNE_INF +2
	 * 	BORNE_INF >= borne_inf
	 * 
	 * UPDATE SET BORNE_SUP = BORNE_SUP +2
	 *  BORNE_SUP >= borne_inf
	 * 
	 *  
	 *  INSERT BORNE_INF = borne_inf
	 *  	   BORNE_SUP = borne_inf +1
	 *  	   CODE = CODE	
	 * 
	 * $data['CODE'] = 'Mon Code';
	 * $data_element_present['CODE']
	 * $data_element_present['BORNE_INF']
	 * $data_element_present['BORNE_SUP']
	 */
	
	function ajoute($data, $data_element_present)
	{
		// $data contient le code du nouveau noeud
		//$data_element_present est l'element avant lequel j'insère
		
		$expression = array('BORNE_INF'=> new Zend_Db_Expr('BORNE_INF +2'));
		$where = "BORNE_INF >= '". $data_element_present['BORNE_INF']."'";
		parent::update($expression, $where);

		$expression = array('BORNE_SUP'=> new Zend_Db_Expr('BORNE_SUP +2'));
		$where = "BORNE_SUP >= '". $data_element_present['BORNE_SUP']."'";
		parent::update($expression, $where);
		
		$data['BORNE_INF'] = $data_element_present['BORNE_INF'];
		$data['BORNE_SUP'] = $data['BORNE_INF'] + 1;
		parent::insert($data);
	}

	
	//suppression de data et ajout
	// il faut que les tableaux data et $data_element_present soit complet
	function deplace($data, $data_element_present)
	{
	    $intervalle = (int) $data['BORNE_SUP'] - (int) $data['BORNE_INF'];
	    if ($intervalle > 1 ) {
	        ("Opération non valide : déplacement de feuille uniquement\n");
	    } else { 
	        $this->delete($data);
	        unset($data['ID']);
	        $this->ajoute($data, $data_element_present);
	    }
	}
	/*
	 * On renumerote l'arbre après suppression
	 * On récupère la borne INF et la borne SUP et on efface tout
	 * On renumerote les élements des bornes SUP 
	 * 
	 * 
	 * Je supprime l'element en recuperant ses bornes
	 * 
	 * SI IL S'AGIT D'UNE FEUILLE (borne_sup - borne_inf = 1) 
	 * DELETE WHERE ID = id
	 * UPDATE SET BORNE_INF = BORNE_INF -2
	 * 	BORNE_INF >= borne_inf
	 * 
	 * UPDATE SET BORNE_SUP = BORNE_SUP -2
	 *  BORNE_SUP >= borne_sup
	 * 
	 * ****
	 *  SINON SOUS-ARBORESCENCE
	 *  
	 *  DELETE WHERE 
	 *  BORNE_INF >= borne_inf AND BORNE_SUP <= borne_sup
	 *  
	 *  UPDATE SET BORNE_SUP = BORNE_SUP -2
	 *  BORNE_SUP >= borne_sup
	 *  
	 * UPDATE SET BORNE_INF = BORNE_INF -2
	 * 	BORNE_INF > borne_inf
	 *  
	 *  $data['ID']
	 *  $data['CODE']
	 *  $data['BORNE_INF']
	 *  $data['BORNE_SUP']
	 */
	
	function delete($data)
	{
		
		
		$intervalle = (int) $data['BORNE_SUP'] - (int) $data['BORNE_INF'];
		
		if ( $intervalle == 1 ) {
			
			$where = "ID = " . $data['ID'] . "";
			parent::delete($where);
				
			$expression = array('BORNE_INF'=> new Zend_Db_Expr('BORNE_INF -2'));
			$where = "BORNE_INF >= ". $data['BORNE_INF'];
			parent::update($expression, $where);
			
			$expression = array('BORNE_SUP'=> new Zend_Db_Expr('BORNE_SUP -2'));
			$where = "BORNE_SUP >= ". $data['BORNE_SUP'];
			parent::update($expression, $where);
			
		} else {
			$where = "BORNE_INF >= " . $data['BORNE_INF']. " AND BORNE_SUP <= " . $data['BORNE_SUP'];
			parent::delete($where);
			
			$ecart = $data['BORNE_SUP'] - $data['BORNE_INF'] + 1;
			$expression = array('BORNE_SUP'=> new Zend_Db_Expr('BORNE_SUP -' . $ecart));
			Zend_Debug::dump($expression);
			
			$where = "BORNE_SUP >= '". $data['BORNE_SUP']."'";
			parent::update($expression, $where);			
			
			$expression = array('BORNE_INF'=> new Zend_Db_Expr('BORNE_INF -' . $ecart));
			$where = "BORNE_INF > '". $data['BORNE_INF']."'";
			parent::update($expression, $where);
				
		}	
	}
			
	
	/* récupération des ID d'un sous-élement 
	 * par défaut on cherche les elements au niveau en dessous
	 * si profondeur = 0 on descend dans toute l'arborescence
	 */
	
	function rechercheSousElements($element, $profondeur = 1){
	    
	    if ( get_class($element) != 'Ccsd_Referentiels_Db_Table_Row' ) {
	        
	        print("Element attendu de type Ccsd_Referenriels_Db_Table_Row");
	        
	        return;
	        
	    } else {
	    
    	    $clauseNiveau = '';
    	    
    	    if ($profondeur > 0 ) {
    	        $niveau = (int) $element['NIVEAU'];
    	        $clauseNiveau = ' AND NIVEAU IN(';
    	        for($i=1; $i<= $profondeur; $i++)
    	        {   
    	            if ($i > 1) {
    	                $clauseNiveau .= ',';
    	            }    
    		        $clauseNiveau .= $niveau+$i;
    	        }
    	        $clauseNiveau .= ")";   
    	    }
    	    
    		$where = "BORNE_INF > " . $element['BORNE_INF']
    				. " AND BORNE_SUP < " . $element['BORNE_SUP']
    		        . $clauseNiveau;
    		
    		$orderby = array('BORNE_INF', 'NIVEAU');
    		
    		return ($this->fetchAll($where, $orderby));
   	    }		
	}
	
	/*
	 * Affichage des domaines
	 */
	function afficheListe()
	{
	    $niveau = 0;
	    $where = "NIVEAU = 0";
	    $elementRacine = $this->fetchRow($where);
	    
	    $listeComplete = $this->rechercheSousElements($elementRacine,0);
	    
	    //$listeTexte = "<ul class=\"ordonnable\">\n";
	    $listeTexte = "";
	    foreach ($listeComplete as $elementCourant)
	    {
	        $niveauElementCourant = $elementCourant->offsetGet('NIVEAU');
	        if ($niveauElementCourant > $niveau)
	        {
	            // je rajoute un ul on permet d'ordonner dans les niveaux inférieurs à un
	            $listeTexte .= ($niveauElementCourant > 1)? "<ul>\n" : "<ul class=\"ordonnable\">\n";
	            $niveau++;
	        }
	        if ($niveauElementCourant < $niveau)
	        {
	            // je ferme le ul
	            $listeTexte .= "</ul>\n";
	            $niveau--;
	        }
	        $listeTexte .= $elementCourant->toLi() . "\n";
	        $premier=FALSE;
	    }
	    // a la fin je ferme autant de ul que de niveau
	    for ($i=0; $i < $niveau; $i++)
	    {    
	        $listeTexte .= "</ul>\n";
	    }    
	    return $listeTexte;
	    	  
	}
	
}

?>