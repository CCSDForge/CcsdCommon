<?php
/**
 * Created by PhpStorm.
 * User: tournoy
 * Date: 13/07/18
 * Time: 13:57
 */

class Ccsd_FileConvert_Pdf
{
    /** @const PDF max file size to process */
    const PDF_MAX_FILE_SIZE = 1073741824;

    /**
     * pdftotext from poppler-utils
     * @see https://poppler.freedesktop.org/
     */
    const PDFTOTEXT_BIN = '/usr/bin/pdftotext';

    /**
     * @param string $filename
     * @param string $method
     * @param bool $useCache
     * @param string $cacheFilename
     * @return bool|string
     * @throws Ccsd_FileConvert_Exception
     */
    public static function convertPDFtoText($filename, $method = '', $useCache = false, $cacheFilename = '')
    {

        if (($useCache) && ($cacheFilename != '')) {
            $cachedContent = self::getCache($cacheFilename);
        }

        if ($cachedContent != '') {
            return $cachedContent;
        }


        if (!is_readable($filename)) {
            throw new Ccsd_FileConvert_Exception(Ccsd_FileConvert_Exception::FILE_NOT_READABLE);
        }

        $fileSize = @filesize($filename);

        if ($fileSize == false) {
            throw new Ccsd_FileConvert_Exception(Ccsd_FileConvert_Exception::FILE_EMPTY);
        }

        // 1 073 741 824
        if ($fileSize > static::PDF_MAX_FILE_SIZE) {
            throw new Ccsd_FileConvert_Exception(Ccsd_FileConvert_Exception::FILE_TOO_BIG);
        }


        switch ($method) {
            case 'poppler':
                $textFromPdf = self::convertPdfToTextWithPoppler($filename, $cacheFilename);
                break;
            case 'solr':
                $textFromPdf = self::convertPdfToTextWithSolr($filename);
                break;
            case 'grobid':
                $textFromPdf = self::convertPdfToTextWithGrobid($filename);
                break;
            default:
                throw new Ccsd_FileConvert_Exception(Ccsd_FileConvert_Exception::UNKNOWN_CONVERT_METHOD);
                break;
        }

        if (($useCache) && ($cacheFilename != '') && ($textFromPdf != '') && ($method != 'poppler')) {
            self::writeCache($textFromPdf, $cacheFilename);
        }

        return $textFromPdf;
    }

    /**
     * Get fulltext cache
     * @param $cacheFilename
     * @return string
     */
    private static function getCache($cacheFilename)
    {
        if (is_readable($cacheFilename)) {
            $content = file_get_contents($cacheFilename);
            if (!$content) {
                return '';
            }
        }

        return $content;
    }

    /**
     * Convert pdf to text with poppler-utils pdftotext
     * @param $pdfInputFile
     * @param string $fullTextCacheFile
     * @return bool|string
     * @throws Ccsd_FileConvert_Exception
     */
    public static function convertPdfToTextWithPoppler($pdfInputFile, $fullTextCacheFile = '')
    {
        if ($fullTextCacheFile == '') {
            throw new Ccsd_FileConvert_Exception(Ccsd_FileConvert_Exception::POPPLER_CACHE_MANDATORY, 'Invalid cache file: ' . $fullTextCacheFile);
        }

        $pdftotextOptions = ' -enc UTF-8 -q ';

        $escapedCommandToExec = self::PDFTOTEXT_BIN . $pdftotextOptions . escapeshellarg($pdfInputFile) . ' ' . $fullTextCacheFile;

        shell_exec($escapedCommandToExec);

        $fulltext = file_get_contents($fullTextCacheFile);

        if (!$fulltext) {
            return '';
        }

        return $fulltext;

    }


    /**
     * Convert pdf to text with apache tika via apache solr
     * Extrait le texte d'un PDF avec une requête à solr en utilisant tika pour
     * l'extraction
     * on peut faire ça avec une requête curl :
     * curl
     * "http://localhost:8080/solr/hal/update/extract?&extractOnly=true&indent=true&wt=php"
     * --data-binary @/documents/00/73/04/20/PDF/docsp089-Tranouez.pdf -H
     * 'Content-type:text/pdf'
     *
     * @see https://wiki.apache.org/solr/ExtractingRequestHandler
     *
     * @param string $pdfPath
     *            le chemin complet vers le fichier PDF sur le NAS
     * @return boolean string pas de texte ou la chaîne extraite sans XML
     */
    public static function convertPdfToTextWithSolr($pdfPath)
    {

        if (defined('APPLICATION_ENV')) {
            $options ['env'] = APPLICATION_ENV;
        }

        $options ['defaultEndpoint'] = Ccsd_Search_Solr::ENDPOINT_MASTER;
        $options ['core'] = 'hal';

        $s = new Ccsd_Search_Solr($options);


        $endpointArray = $s->getEndpoints();

        $url = 'http://' . $endpointArray ['endpoint'] ['master'] ['host'] . ':' . $endpointArray ['endpoint'] ['master'] ['port'] . $endpointArray ['endpoint'] ['master'] ['path'] . '/' . $endpointArray ['endpoint'] ['master'] ['core'] . '/update/extract?&extractOnly=true&indent=false&extractFormat=text&wt=phps';

        $postData = @file_get_contents($pdfPath);

        $curlConfig = [
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => [
                CURLOPT_USERPWD => $endpointArray ['endpoint'] ['master'] ['username'] . ":" . $endpointArray ['endpoint'] ['master'] ['password']
            ]
        ];

        $client = new Zend_Http_Client($url, $curlConfig);

        $client->setRawData($postData)->setEncType('text/pdf');

        try {
            $data = $client->request('POST')->getBody();
        } catch (Zend_Http_Client_Exception $e) {
            echo $e->getMessage();
        }

        $data = unserialize($data); // phps

        $data = $data [''];

        return $data;
    }


    /**
     * Convert pdf to text with grobid
     * @param string $pdfPath
     * @return string
     */
    public static function convertPdfToTextWithGrobid($pdfPath)
    {

        // Crée un gestionnaire cURL
        $ch = curl_init('http://ccsdgrobidtest.in2p3.fr:8080/api/processFulltextDocument');

        // Crée un objet CURLFile
        $cfileToPost = new CURLFile($pdfPath, 'text/pdf', 'iCanHazTextPlz.pdf');


        // Assigne les données POST
        $dataToPost = ['input' => $cfileToPost];
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataToPost);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $rawXmlOutput = curl_exec($ch);

        curl_close($ch);

        $xmlDocument = new SimpleXMLElement($rawXmlOutput);
        $bodyText = $xmlDocument->xpath("/*[name()='TEI']/*[name()='text']/*[name()='body']");

        $bodyString = '';

        foreach ($bodyText[0] as $bodyElement) {
            $bodyString .= $bodyElement->asXML();
        }

        $bodyString = strip_tags($bodyString);

        return $bodyString;
    }

    /**
     * write fulltext cache content
     * @param string $content
     * @param $cacheFilename
     * @return bool|int
     */
    static private function writeCache($content, $cacheFilename)
    {
        return file_put_contents($cacheFilename, $content);
    }

}