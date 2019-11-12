<?php

/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 21/08/17
 * Time: 16:08
 */
use PHPUnit\Framework\TestCase;

class Ccsd_GlobalesTest extends PHPUnit_Framework_TestCase {

    /** @var  Ccsd_Globales */
    private $glob;

    public function setUp() {
        $this -> glob = new Ccsd_Globales();
    }
    /**
      * @expectedException     Ccsd_Globales_Exception
     */
    public function testNotRecorded() {
        $g = $this -> glob;
        $g->C = 3;
    }

    public function testOk() {
        $g = $this -> glob;
        $g -> record('C');
        $g->C = 3;
        $this -> assertEquals(3, $g -> C);
        $g -> reset('C', 6);
        $this -> assertEquals(6, $g -> C);
    }
    /**
     * @expectedException     Ccsd_Globales_Exception
     * @expectedExceptionMessage Allready defined
     */
    public function testReAssign() {
        $g = $this -> glob;
        $g -> record('D');
        $g->D = 3;
        $this -> assertEquals(3, $g -> D);
        $g->D =5;
    }
    /**
     * @expectedException     Ccsd_Globales_Exception
     * @expectedExceptionMessage Variable NotRecorded not recorded
     */
    public function testGetNotRecorded() {
        $g = $this -> glob;
        $this -> assertEquals(3, $g -> NotRecorded);
    }

}
