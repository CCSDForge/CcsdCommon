<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 10/12/18
 * Time: 11:10
 */

namespace Ccsd\Virtuoso;

use Ccsd\Virtuoso;

/**
 * Class Graph
 * @package Ccsd\Virtuoso
 */
class Graph
{
    /** @var string */
    private $name = null;
    /** @var Virtuoso|null  */
    private $virtuosoConnection = null;

     /**
     * A graph name is like  https://data.archives-ouvertes.fr/revue/
     * @param Virtuoso $virtuoso
     * @param string $name
     * @throws Exception
     */
    public function __construct(Virtuoso $virtuoso, string $name) {
        $this -> virtuosoConnection = $virtuoso;
        if (preg_match(';^https?://[a-zA-Z0-1\-._/]+$;', $name)) {
            $this->name = $name;
        } else {
            throw new Exception("$name is not a valid sparql graph name");
        }
    }
    /**
     * @throws \Zend_Http_Exception
     */
    public function create() {
        $query = new SparqlQuery("create graph <" . $this->name . ">");
        $this->virtuosoConnection->send($query);
    }
    /**
     * @throws \Zend_Http_Exception
     */
    public function delete() {
        $query = new SparqlQuery("drop silent graph <" . $this->name . ">");
        $this->virtuosoConnection->send($query);
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}