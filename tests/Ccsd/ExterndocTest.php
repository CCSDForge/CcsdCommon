<?php

use PHPUnit\Framework\TestCase;

class Ccsd_DataproviderTest extends PHPUnit_Framework_TestCase {

    public function testMergeMetas()
    {
        $metaToMerge = array(
            'authors' => array(
                array(
                    Ccsd_Externdoc::AUTHORS_FIRST => 't',
                    Ccsd_Externdoc::AUTHORS_LAST => "machin",
                    Ccsd_Externdoc::AUTHORS_INITIALS => "TM"
                ),
                array(
                    Ccsd_Externdoc::AUTHORS_FIRST => 'thing',
                    Ccsd_Externdoc::AUTHORS_LAST => "thing",
                    Ccsd_Externdoc::AUTHORS_INITIALS => "TT"
            ))
        );

        $metaToKeep = array(
            'authors' => array(array(
                Ccsd_Externdoc::AUTHORS_FIRST => 'truc',
                Ccsd_Externdoc::AUTHORS_LAST => "machin"
                )
            )
        );

        $mergedMetas = Ccsd_Externdoc::mergeAuthorAndStructMetas($metaToMerge, $metaToKeep);

        $this->assertEquals(array(
            'authors' => array(
                array(
                    Ccsd_Externdoc::AUTHORS_FIRST => 'truc',
                    Ccsd_Externdoc::AUTHORS_LAST => "machin",
                    Ccsd_Externdoc::AUTHORS_INITIALS => "TM"
                ),
                array(
                    Ccsd_Externdoc::AUTHORS_FIRST => 'thing',
                    Ccsd_Externdoc::AUTHORS_LAST => "thing",
                    Ccsd_Externdoc::AUTHORS_INITIALS => "TT"
                )),
            'structures' => array(),
        ),$mergedMetas);
    }

    /**
     * @param $firstname
     * @param $result
     *
     * @dataProvider providefirstnames
     */
    public function testCleanfirstname ($firstname, $result)
    {
        $this->assertEquals($result, Ccsd_Externdoc::cleanFirstname($firstname));
    }

    public function providefirstnames()
    {
        return [
            'firstname unique' => ['Jean', 'Jean'],
            'firstname Séparé' => ['Jean Pierre', 'Jean Pierre'],
            'firstname avec initiale' => ['Jean P', 'Jean'],
            'firstname avec initiale et point' => ['Jean P.', 'Jean'],
            'firstname vide' => ['', '']
        ];
    }
}