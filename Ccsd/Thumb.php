<?php

class Ccsd_Thumb
{
    const THUMB_URL = 'http://thumb.ccsd.cnrs.fr/';
    const FORMAT_THUMB = 'thumb';
    const FORMAT_SMALL = 'small';
    const FORMAT_MEDIUM = 'medium';
    const FORMAT_LARGE = 'large';
    /**
     * IP de machines avec des accès particuliers aux document, par exemple voir un PDF sans sa page de garde pour générer la miniature
     */
    static $THUMB_IP = ['193.48.96.4', '193.48.96.5', '193.48.96.6', '193.48.96.13', '193.48.96.14', '192.168.176.63','192.168.176.64','192.168.176.65','192.168.176.66'];

    /**
     * Retourne l'adresse de l'image dans le format demandé
     * @param int
     * @param string
     * @return string
     */
    public static function get ($imgid, $format = self::FORMAT_SMALL)
    {
        if (! in_array($format, array(self::FORMAT_THUMB , self::FORMAT_SMALL , self::FORMAT_MEDIUM , self::FORMAT_LARGE))) {
            $format = self::FORMAT_SMALL;
        }
        return self::THUMB_URL . (int) $imgid . '/' . $format;
    }
    /**
     * Ajout d'une ressource dans le générateur de miniatures
     * @param string
     * @param string
     * @param int
     * @return mixed
     */
    public static function add ($key=null, $url, $option=0)
    {
        if ( null == $key ) {
            return false;
        }
        $curl = curl_init(self::THUMB_URL . 'make');
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($curl, CURLOPT_TIMEOUT, 300);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curl, CURLOPT_USERAGENT, "HAL Open Archive system");
        curl_setopt($curl, CURLOPT_COOKIEJAR, '');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $post = array('url'=>$url, 'key'=>$key);
        if ( $option !== 0 ) {
            $post['option'] = $option;
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $return = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ( in_array($http_status,  array(200, 201, 202, 301, 302, 303)) ) {
            try {
                $xml = @simplexml_load_string($return);
                if ($xml instanceof SimpleXMLElement) {
                    $value = $xml->xpath('/thumb/value');
                    return (string) $value[0];
                }
            } catch (Exception $e) {}
        }
        return false;
    }
    /**
     * Demande de création de miniature
     * @param int $imgid
     * @return bool
     */
    public static function process ($imgid)
    {
        $curl = curl_init(self::THUMB_URL . 'create.php?imgid=' . $imgid);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return ($http_status == 200);
    }
}
