<?php
/**
 * Created by PhpStorm.
 * User: tournoy
 * SSH archivage connection exceptions
 */

class Ccsd_Archivage_Connection_Exception extends Exception
{

    const SSH_CONN_FAILED = 'SSH connection failed';
    const SSH_AUTH_FAILED = 'SSH auth. failed';
    const SFTP_INIT_FAILED = 'SSH: Could not initialize SFTP subsystem';

    public function __construct($code = null, $message = null, $previous = null)
    {

        switch ($code) {

            case self::SSH_CONN_FAILED:
                $this->message = self::SSH_CONN_FAILED;
                break;
            case self::SSH_AUTH_FAILED:
                $this->message = self::SSH_AUTH_FAILED;
                break;
            case self::SFTP_INIT_FAILED:
                $this->message = self::SFTP_INIT_FAILED;
                break;
            default:
                $this->message = 'Unknown Error';
                break;

        }

        $this->message = $this->message . '. ' . $message;

    }


}