<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 10/12/18
 * Time: 11:34
 */

namespace Ccsd\Virtuoso;

/**
 * Class SparqlQuery
 * @package Ccsd\Virtuoso
 */
class SparqlQuery extends Query
{
    /**
     * @param string $str
     */
    public function __construct($str) {
        parent::__construct($str);
    }

}