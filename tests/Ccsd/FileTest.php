<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 28/08/17
 * Time: 09:45
 */

/**
 * Class Ccsd_FileTest
 */
class Ccsd_FileTest extends PHPUnit_Framework_TestCase
{

    private $svgfileOk = FIXTUREDIR . "/testConvert.svg";

    public function setUp()
    {
    }

    /**
     *
     */
    public function testconvertSvgToPngOk()
    {
        @unlink('/tmp/testConvert.png');
        Ccsd_File::convertSvgToPng($this->svgfileOk, "/tmp");
        $this -> assertFileExists( '/tmp/testConvert.png');
        $this -> assertNotEquals(0, filesize('/tmp/testConvert.png'));
    }

    /**
     *
     */
    public function testCanconvert() {
        $this -> assertEquals(true, Ccsd_File::canConvertImg($this->svgfileOk));
        $this -> assertEquals(false, Ccsd_File::canConvert($this->svgfileOk));
    }

    /**
     *
     */
    public function testgetFilename() {
        $this -> assertEquals('testConvert.svg', Ccsd_File::getFilename($this->svgfileOk));

    }

    /**
     *
     */
    public function testgetExtension()
    {
        $this -> assertEquals('svg', Ccsd_File::getExtension($this->svgfileOk));
        $this -> assertEquals('png', Ccsd_File::getExtension("/tmp/testfile.PnG"));
        $this -> assertEquals('pdf', Ccsd_File::getExtension("/tmp/testfile.Png.PDF"));
    }

    /**
     *
     */
    public function testgetDirectory() {
        $this -> assertEquals(FIXTUREDIR, Ccsd_File::getDirectory($this->svgfileOk));
        $this -> assertEquals('/tmp', Ccsd_File::getDirectory("/tmp/testfile.PnG"));
        $this -> assertEquals('.', Ccsd_File::getDirectory("testfile.Png.PDF"));
    }

    /**
     * @param string $result
     * @param string $filename
     * @param string $newExt
     *
     * @dataProvider provideFileExtensions
     */
    public function testreplaceFileExtension($result, $filename, $newExt)
    {
        $this->assertEquals($result, Ccsd_File::replaceFileExtension($filename, $newExt));
    }

    /**
     * @return array
     */
    public function provideFileExtensions()
    {
        return [
            'extension minuscule' => ['test.jpg', 'test.png', 'jpg'],
            'extension majuscule' => ['TEST.jpg', 'TEST.PNG', 'jpg'],
            'extension mixte' => ['tEst.jpg', 'tEst.Png', 'jpg']
        ];
    }
}
