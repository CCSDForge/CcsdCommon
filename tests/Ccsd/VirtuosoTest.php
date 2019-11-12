<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 10/12/18
 * Time: 17:43
 */

namespace Ccsd;
/**
 * Class Virtuoso_Test
 * @package Ccsd
 */
class Virtuoso_Test extends \PHPUnit_Framework_TestCase
{
    /**
     */
    public function testVirtuoso()
    {
        $virtuoso = new \Ccsd\Virtuoso();
        $virtuosoQuery = new Virtuoso\SparqlQuery("select distinct ?Concept where {[] a ?Concept} ");
        $response = $virtuoso -> send($virtuosoQuery);
        $json = $response -> getBody();
        $obj = json_decode($json);
        $this->assertAttributeNotEmpty('results', $obj, 'Results not present in response' );
        $this->assertAttributeNotEmpty('value', $obj->results->bindings[0]->Concept);

        $virtuosoQuery = new Virtuoso\SparqlQuery("select distinct ?g, count(*) as ?count where  { graph ?g {  ?s ?p ?v  } } ");
        $response = $virtuoso -> send($virtuosoQuery);
        $json = $response -> getBody();
        $obj = json_decode($json);
        $this->assertAttributeNotEmpty('results', $obj, 'Results not present in response for graph' );
        foreach ($response -> getRows() as $row) {
            $value = $row->count;
            $this -> assertTrue(is_int($value));
            if (preg_match('/author/', $row->g )) {
                $this -> assertGreaterThan(100000, $value);
            }
        }
        $list = $virtuoso -> listGraphNames();
        $this -> assertTrue(in_array('https://data.archives-ouvertes.fr/author/', $list));
    }

}