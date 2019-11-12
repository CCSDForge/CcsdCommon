<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 10/12/18
 * Time: 17:07
 */

namespace Ccsd\Virtuoso;

/**
 * Class Reponse
 * @package Ccsd\Virtuoso
 */
class Response extends \Zend_Http_Response
{
    /** @var Rows */
    protected $rows;

    /** @var string  */
    protected $json;
    /**
     * Reponse constructor.
     * @param int    $code Response code (200, 404, ...)
     * @param array  $headers Headers array
     * @param string $body Response body
     * @param string $version HTTP version
     * @param string $message Response code as text
     * @throws \Zend_Http_Exception
     */
    public function __construct($code, array $headers, $body = null, $version = '1.1', $message = null)
    {
        parent::__construct($code, $headers, $body, $version, $message);

    }
    /**
     * @param string $response_str
     * @return Response
     * @throws \Zend_Http_Exception
     */
    public static function fromString($response_str)
    {
        $code    = self::extractCode($response_str);
        $headers = self::extractHeaders($response_str);
        $body    = self::extractBody($response_str);
        $version = self::extractVersion($response_str);
        $message = self::extractMessage($response_str);
        $response = new Response($code, $headers, $body, $version, $message);
        try {
            $resultsets = json_decode($response->getBody());
        } catch (\Exception $e) {
            /** TODO Hum, si pas de json, que fait-on???
            Pour l'instant on mets un objet vide... */
            $resultsets = new \stdClass();

        }
        $response -> setResultsSets($resultsets);
        return $response;
    }

    /**
     * @param \stdClass $resultsets
     */
    private function setResultsSets($resultsets) {
        $this -> rows = new Rows($resultsets -> head -> link, $resultsets -> head -> vars, $resultsets -> results);
    }

    /**
     * @return Rows
     */
    public function getRows() {
        return $this->rows;
    }
}