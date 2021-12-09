<?php


class Ccsd_Externdoc_Crossref extends Ccsd_Externdoc
{
    /**
     * @var string
     */
    protected $_idtype = "doi";

    /**
     * Clé : Le XPATH qui permet de repérer la classe => Valeur : La classe à créer
     * TODO : remplir dynamiquement au chargement de la classe... trouver comment faire !
     * @var array
     */
    static public $_existing_types = [];

    protected $_xmlNamespace = array();

    /**
     * @var DOMXPath
     */
    protected $_domXPath = null;

    /**
     * Création d'un Doc Crossref à partir d'un XPATH
     * L'objet Crossref est seulement une factory pour un sous-type réel.
     * @param string $id
     * @param DOMDocument $xmlDom
     */
    static public function createFromXML($id, $xmlDom)
    {
        $domxpath = new DOMXPath($xmlDom);

        // On recherche le type de document associé au DOI à partir du XPATH de référence
        foreach (self::$_existing_types as $order => $xpath2class) {
            /**
             * @var string  $xpath
             * @var Ccsd_Externdoc $type
             */
            foreach ($xpath2class as $xpath => $type) {

                if ($domxpath->query($xpath)->length > 0) {
                    return $type::createFromXML($id, $xmlDom);
                }
            }
        }

        return null;
    }

    /**
     * @param $year
     * @param $month
     * @param $day
     * @return string
     */
    protected function formateDate($year, $month, $day)
    {
        $dateString = "";

        if (!empty($year)) {
            $dateString .= $this->arrayToString($year);
        }

        if (!empty($month)) {
            if (!empty($dateString)) {
                $dateString .= '-';
            }

            $dateString .= self::addZeroInDate($this->arrayToString($month));
        }

        if (!empty($day)) {
            if (!empty($dateString)) {
                $dateString .= '-';
            }

            $dateString .= self::addZeroInDate($this->arrayToString($day));
        }

        return Ccsd_Tools::str2date($dateString);
    }

    /**
     * Formatage des pages premiere - derniere
     * @param $first
     * @param $last
     * @return string
     */
    protected function formatePage($first, $last)
    {
        if (!empty($first) && !empty($last)) {
            return $first . "-" . $last;
        }

        if (!empty($first)) {
            return $first;
        }

        return $last;
    }

    /**
     * @param $type
     */
    static public function registerType($xpath, $type, $order = 50)
    {
        self::$_existing_types[$order][$xpath] = $type;
        // Il faut trier suivant l'ordre car PHP ne tri pas numeriquement par defaut
        ksort(self::$_existing_types);
    }
}


foreach (glob(__DIR__."/Crossref/*.php") as $filename)
{
    require_once($filename);
}