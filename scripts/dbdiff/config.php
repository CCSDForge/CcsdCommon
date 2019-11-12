<?php

/**
 * This file should contain the configuration of databases.
 * 
 * $dbs_config is an array of database configurations. Each element of the
 * array should provide details for a database which will be selectable from
 * a list.
 * 
 * This is arguably more secure and convenient than submitting database
 * details with an HTML form (and sending them over an unsecured channel).
 * 
 * Refer to the 'Demo Configuration' below for reference.
 */

set_time_limit(0);
ini_set('memory_limit','6144M');
set_include_path(implode(PATH_SEPARATOR, array_merge(array('/sites/phplib_test', '/sites/library_test'), array(get_include_path()))));

define ('HOST', 'ccsddb04.in2p3.fr');
define ('USER', 'root');
define ('PASSWORD', '');
