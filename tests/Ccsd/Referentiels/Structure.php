<?php

class Ccsd_Referentiels_Structure_Test extends PHPUnit_Framework_TestCase
{


    public function setUp()
    {

    }

    // Le comportement de cette fonction dépend de Ccsd_Referentiels_Author::getAcceptedValues
    // Les valeurs acceptées sont mise à jour si on passe une valeur
    // Les valeurs acceptées non mise à jour sont mise à null
    // Les valeurs préexistantes conservent leur valeur précédente
    // Les autres valeurs sont filtrées
    public function testIsWellFormed() {
        $struct = new Ccsd_Referentiels_Structure(0, [
            'sigle' 	 =>'A',
            'structname' =>'Equipe A',
            'address' 	 =>'',
            'paysid'	 =>'fr',
            'typestruct' => Ccsd_Referentiels_Structure::TYPE_RESEARCHTEAM,
            'valid'		 => 1
        ]);
        $institution = new Ccsd_Referentiels_Structure(0, [
            'sigle' 	 =>'B',
            'structname' =>'Equipe B',
            'paysid'	 =>'fr',
            'typestruct' =>Ccsd_Referentiels_Structure::TYPE_INSTITUTION,
            'valid'		 => 1,
        ]);
        $team2  = new Ccsd_Referentiels_Structure(0, [
            'sigle' 	 =>'C',
            'structname' =>'Equipe C',
            'address' 	 =>'',
            'paysid'	 =>'fr',
            'typestruct' =>Ccsd_Referentiels_Structure::TYPE_RESEARCHTEAM,
            'valid'		 => 1,
        ]);
        $this -> assertFalse($struct -> isWellFormed());
        // Ajout parent institution
        $struct -> addParent($institution, null);
        $this -> assertTrue($struct -> isWellFormed());
        // Ajout mauvais parent
        $struct -> addParent($team2, null);
        $this -> assertFalse($struct -> isWellFormed());

    }


}

