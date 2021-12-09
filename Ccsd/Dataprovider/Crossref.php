<?php

/*
 * La classe Ccsd_Dataprovider_Crossref permet de récupérer le XML de Crossref correspondant à un identifiant
 *
 *
 * @author S. Denoux
 */

class Ccsd_Dataprovider_Crossref extends Ccsd_Dataprovider
{

    const DOI_NAME = "inria";
    const DOI_PWD = "inria518";
    const DOI_URL = "https://www.crossref.org/openurl";

    public $_URL = "http://dx.doi.org";

    /**
     * @param $id
     * @return null
     */
    public function getDocument($id)
    {
        //-----------
        $var_post = array();
        $var_post["pid"] = self::DOI_NAME . ":" . self::DOI_PWD;
        $var_post["format"] = "unixref";
        $var_post["id"] = ("doi:" . urlencode($id));
        $var_post["noredirect"] = "true";

        $url = self::DOI_URL . "?";

        foreach ($var_post as $key => $val) {
            $url .= "$key=$val&";
        }

        $url = rtrim($url, "&");

        $xmlDom = $this->requestXml($url);

        if (!isset($xmlDom)) {
            $this->setError('library_meta_nourl');
            return null;
        }

        $doc = Ccsd_Externdoc_Crossref::createFromXML($id, $xmlDom);

        if (isset($doc)) {
            return $doc;
        }

        $this->setError('library_meta_nourl');
        return null;
    }
}