<?php

/**
 * Referentiel Type de document
 * @author Yb
 *
 */

Class Ccsd_Referentiels_Typdoc
{

    /**
     * Liste des types de document connus
     */

    const TYPE_ART      =   'ART';
    const TYPE_THESE    =   'THESE';
    const TYPE_BOOK     =   'BOOK';
    const TYPE_COMM     =   'COMM';
    const TYPE_IMG      =   'IMG';
    const TYPE_SON      =   'SON';
    const TYPE_PATENT   =   'PATENT';
    const TYPE_VIDEO    =   'VIDEO';
    const TYPE_MAP      =   'MAP';
    const TYPE_COUV     =   'COUV';
    const TYPE_REPORT   =   'REPORT';
    const TYPE_NOTE     =   'NOTE';
    const TYPE_UNDEFINED=   'UNDEFINED';
    const TYPE_LECTURE  =   'LECTURE';
    const TYPE_POSTER   =   'POSTER';
    const TYPE_OUV      =   'OUV';
    const TYPE_OTHER    =   'OTHER';
    const TYPE_MEM      =   'MEM';
    const TYPE_PRESCONF =   'PRESCONF';
    const TYPE_OTHERREPORT   =   'OTHERREPORT';
    const TYPE_REPACT   =   'REPACT';
    const TYPE_DOUV     =   'DOUV';
    const TYPE_HDR      =   'HDR';
    const TYPE_MINUTES  =   'MINUTES';
    const TYPE_SYNTHESE =   'SYNTHESE';

    /**
     * @var string identifiant du type de document
     */
    protected $_code = null;


    /**
     * Ccsd_Referentiels_Typdoc constructor.
     * @param $code
     */
    public function __construct($code = null)
    {
        $this->_code = $code;
    }

    /**
     * retourne l'URI d'un type de document
     * @return string
     */
    public function getUri()
    {
        return AUREHAL_URL . "/typdoc/{$this->_code}";
    }

    /**
     * Indique si le code est valide
     * @param $code
     * @return bool
     */
    public function isValid($code)
    {
        return in_array($code, $this->getIds());
    }

    /**
     * Indique si le code du type de document existe
     * @param $code
     * @return bool
     */
    public function exist($code)
    {
        return $this->isValid($code);
    }

    /**
     * Retourne la liste des types de documents existants
     * @return array
     */
    public function getIds()
    {
        $typdocs = [];
        $obj = new ReflectionClass(__CLASS__);
        foreach ($obj->getConstants() as $name => $value) {
            if (strpos($name, 'TYPE_') !== false) {
                $typdocs[] = $value;
            }
        }
        return $typdocs;
    }
}
