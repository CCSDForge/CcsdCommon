<?php


class Ccsd_Externdoc_Inra_ResearchReport extends Ccsd_Externdoc_Inra
{

    /**
     * @var string
     */
    protected $_type = "REPORT";

    protected $array_mapping_type = [
      'Rapport de recherche'=>'Rapport de recherche',
      'Rapport de fin de contrat, Réponse à appel d\'offre, Rapport intermédiaire de projet, Livrable, Rapport annuel de projet' => 'Rapport contrat/projet',
      'Rapport d\'expertise/EsCo'=>'Rapport contrat/projet',
      'Avis'=>'Avis',
      'Rapport de prospective'=>'Autre',
      'Autres rapports'=>'Autre',
      'Rapport d\'étude'=>'Rapport technique',
      'Compte rendu de mission'=>'Autre',
      'Rapport technique'=>'Rapport technique',
      'Etat de l\'art/Analyse bibliographique'=>'Etat de l\'art/Analyse bibliographique',
      'Rapport d\'audit'=>'Rapport d\'audit'
    ];

    protected $defaut_reportType='Rapport de recherche';

    protected $_specific_wantedTags = [
        self::META_REPORTNUMBER,
        self::META_REPORTTYPE,
        self::META_NBPAGES_INRA,
        self::META_DATE,
        self::META_JELCODE,
        self::META_RESEARCHREPORTTYPE,
        /**
        self::META_COMMANDITAIRE,
        self::META_PARTNER,
         **/
        self::META_PUBLISHER,
        self::META_REPORTNUMBER,
        /**
         * self::META_COMMENT_COLLECTION,
         *
         */
        self::META_DOCUMENTLOCATION

        ];


    /**
     * @param string $id
     * @param DOMDocument $xmlDom
     * @return Ccsd_Externdoc_Inra_ResearchReport
     */
    static public function createFromXML($id, $xmlDom)
    {
        $doc = new Ccsd_Externdoc_Inra_ResearchReport($id);

        $domxpath = self::dom2xpath($xmlDom);

        $doc->setDomPath($domxpath);
        return $doc;
    }


    public function getReportType()
    {
        $reportType = parent::getReportType();
        if (array_key_exists($reportType,$this->array_mapping_type)) {
            $reportType = $this->array_mapping_type[$reportType];
        }
        else {
            $reportType=$this->defaut_reportType;
        }
        return $reportType;
    }


    /**
     * @return array
     */
    public function getMetadatas()
    {

        if (!empty($this->_metas)) {
            return $this->_metas;
        }

        $this->_metas = parent::getMetadatas();




        foreach ($this->_specific_wantedTags as $metakey) {

            $meta = "";

            switch ($metakey) {
                case self::META_REPORTNUMBER :
                    $meta = $this->getReportNumber();
                    break;
                case self::META_REPORTTYPE :
                    $meta = $this->getReportType();
                    break;
                default:
                    break;
            }

            if (!empty($meta)) {
                $this->_metas[self::META][$metakey] = $meta;
            }
        }


        return $this->_metas;


    }
}


Ccsd_Externdoc_Inra::registerType("/produit/record[@xsi:type='ns2:researchReport']", "Ccsd_Externdoc_Inra_ResearchReport");