<?php
/**
 * Created by PhpStorm.
 * User: sdenoux
 * Date: 11/04/18
 * Time: 16:48
 */


use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../Ccsd/User/Models/UserTokens.php';

class Ccsd_User_Models_UserTokensTest extends PHPUnit_Framework_TestCase
{
    /**
     * Validation de format de date
     * @param $date
     * @param string $format
     * @return bool
     */
    function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public function testTimeFormat()
    {
        $userTokens = new Ccsd_User_Models_UserTokens();
        $timeModified = $userTokens->getTime_modified();
        $this->assertTrue($this->validateDate($timeModified, 'Y-m-d H:i:s'));
    }
}