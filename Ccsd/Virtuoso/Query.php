<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 10/12/18
 * Time: 11:33
 */

namespace Ccsd\Virtuoso;

/**
 * Class Query
 * @package Ccsd\Virtuoso
 */
class Query
{
    /** @var string  */
    private $queryString = '';
    /**
     * @param string $str
     */
    protected function __construct($str) {
        $this->set($str);
    }
    /**
     * @return string
     */
    public function get(): string
    {
        return $this->queryString;
    }
    /**
     * @param string $queryString
     */
    protected function set(string $queryString)
    {
        $this->queryString = $queryString;
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get();

    }
}