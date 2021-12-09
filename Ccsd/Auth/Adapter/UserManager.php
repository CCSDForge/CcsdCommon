<?php


namespace Ccsd\Auth\Adapter;

/**
 * Interface UserManager
 * @package Ccsd\Auth\Adapter
 */
interface UserManager
{
    /**
     * @return \Ccsd_User_Form_Accountcreate
     */
    public function getUserCreateForm();

    /**
     * @param \Hal_User $user
     * @return void
     */
    public function completeUserInfoIfNeeded($user);
}