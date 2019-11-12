<?php

class Ccsd_Dns
{
    const DNS_URL = 'https://webdns.ccsd.cnrs.fr/api/';
    const DNS_DOMAINID = '1';
    const DNS_DOMAIN = 'sciencesconf.org';
    const DNS_CONTENT = '193.48.96.90';

    public static function ls()
    {
        $url = self::DNS_URL . '?method=listDomains';
        $curl = curl_init($url);
        Zend_Debug::dump($curl);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_USERAGENT, "CCSD");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CAINFO, __DIR__ . '/Certificates/CA/CNRS2-Standard.pem');
        curl_setopt($curl, CURLOPT_SSLCERT, __DIR__ . '/Certificates/client.pem');
        curl_setopt($curl, CURLOPT_SSLKEY, __DIR__ . '/Certificates/key.pem');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        Zend_Debug::dump(curl_exec($curl));
        Zend_Debug::dump(curl_error($curl));
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $http_status == 200 ;
    }
    public static function add($name, $domainid = self::DNS_DOMAINID, $domain = self::DNS_DOMAIN, $content = self::DNS_CONTENT)
    {
        $url = self::DNS_URL . '?method=addRecord&domain=' . $domainid . '&name=' . $name . '.' . $domain . '&content=' . $content ;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_USERAGENT, "CCSD");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CAINFO, __DIR__ . '/Certificates/CA/CNRS2-Standard.pem');
        curl_setopt($curl, CURLOPT_SSLCERT, __DIR__ . '/Certificates/client.pem');
        curl_setopt($curl, CURLOPT_SSLKEY, __DIR__ . '/Certificates/key.pem');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $http_status == 200 ;
    }
}
