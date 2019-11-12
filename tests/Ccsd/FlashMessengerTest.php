<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 05/07/17
 * Time: 11:27
 */

require_once "Ccsd/FlashMessenger.php";

/**
 * @deprecated 
 * Class Ccsd_FlashMessenger_Test
 */
class Ccsd_FlashMessenger_Test extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function testItem()
    {
        $item = new Ccsd_FlashMessengerItem("danger", "Message de danger");
        $this->assertEquals("danger", $item -> getSeverity());
        $this->assertEquals("Message de danger", $item -> getMessage());

        // Yet accept everything as severity... Bad!
        $item = new Ccsd_FlashMessengerItem("Youpi", "Message de danger");
        $this->assertEquals("Youpi", $item -> getSeverity());
    }

    /**
     * @test
     */
    public function testArray() {
        $fm = new Ccsd_FlashMessenger("info", "message1");
        $fm -> addMessage("warning", "message2");
        $fm -> addMessage("danger", "message2");
        $fm -> addMessage("info", "message4");

        $a = [];
        foreach ($fm as $key => $item) {
            $sev = $item -> getSeverity();
            $msg = $item -> getMessage();

            $a[] = [$sev , $msg];
        }
        $res = [
            ["info", "message1"],
            ["warning", "message2"],
            ["danger", "message2"],
            ["info", "message4"],
        ];
        // Ccsd_Flash_Messenger a ete iterable, il ne l'est plus
        // $this -> assertEquals($res, $a);
    }
}