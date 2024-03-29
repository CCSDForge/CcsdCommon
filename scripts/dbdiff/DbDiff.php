<?php

/**
 * Compare the schemas of between databases.
 * 
 * For two database schemas to be considered the same, they must have the same
 * tables, where each table has the same fields, and each field has the same
 * parameters.
 * 
 * Field parameters that are compared are those that are given by the MySQL
 * 'SHOW COLUMNS' command. These are: the field's name, it's type, whether the
 * field can store null values, whether the column is indexed, the default
 * values and whether the field was created with the 'auto_increment' keyword.
 * 
 * More information on this tool can be found at:
 * http://joefreeman.co.uk/blog/2009/07/php-script-to-compare-mysql-database-schemas/
 * 
 * Copyright (C) 2009, Joe Freeman <joe.freeman@bitroot.com>
 * Available under http://en.wikipedia.org/wiki/MIT_License
 * 
 * @package default
 * @author Joe Freeman
 */
class DbDiff {
	
	/**
	 * Export the schema of the database into an array.
	 *
	 * @param string $name Name of the database
	 * @param PDO $db SQL database.
	 * @return mixed|string An array structure of the exported schema, or an error string.
	 */
	function export($name, $db) {


        $result = $db->query("SHOW TABLES");
        while ($row = $result->fetchColumn()) {
            $tables[$row] = array();
        }

        foreach ($tables as $table_name => $fields) {
            $result = $db->query("SHOW COLUMNS FROM `" . $table_name . "`");
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $tables[$table_name][$row['Field']] = $row;

            }
        }


        $split1 = explode("(",$name);
        $split2 = explode("@", $name);
        $nameExploded = trim($split1[0]) . "@" .  trim($split2[1],")");
		
		$data = array(
			'name' => $nameExploded,
			'tables' => $tables
		);

		return $data;
	}


    /**
     * Returns an 's' character if the count is not 1.
     *
     * This is useful for adding plurals.
     *
     * @return string An 's' character or an empty string
    **/
    function s($count) {
        return $count != 1 ? 's' : '';
    }


	/**
	 * Compare two schemas (as generated by the 'export' method.)
	 *
	 * @param string $schema1 The first database schema.
	 * @param string $schema2 The second database schema.
	 * @return mixed
	 */
	function compare($schema1, $schema2) {

		$tables1 = array_keys($schema1['tables']);
		$tables2 = array_keys($schema2['tables']);
		$tables = array_unique(array_merge($tables1, $tables2));
		$results = array();
		foreach ($tables as $table_name) { //travaille par table

            $boolDiff = false; //savoir si on continue dans la recherche de différence, erreur si index inexistant

			// Check tables exist in both databases
            //table 1
            $res = $this->checkTable($schema1, $table_name);
			if (! is_bool($res)) { //boolean si aucune diff, ou si une diff mais pas de debug
                $results['results'][$table_name][]=$res; //add to results the trace(if debug)
			}

            if ($res) {
                $boolDiff = true;
            }

            //table 2
            $res = $this->checkTable($schema2, $table_name);
            if (! is_bool($res)) { //false si aucune diff, ou true si une diff mais pas de debug
                $results['results'][$table_name][]=$res; //add to results the trace (if debug)

            }
			if ($res) { //true ou valeur trace = une diff détectée / absence d'une table

                $boolDiff = true;

                //boucle pour récup la valeur de nom de table sans problème d'index/de case

                $table_name2= $this->getTableName($schema2, $table_name);
                $boolCase = strcasecmp($table_name,$table_name2)==0 && strcmp($table_name,$table_name2)!=0; //retourne 1 si même chaine avec différence de casse

                if (!$boolCase) {

                    $results['sql'][]=$this->syncTable($schema1, $table_name);

                } else { //Cas où on veut modifier le nom @case => rename table
                    /*
                    $res = $db->exec("RENAME TABLE `" . $table_name2 . "` TO `" . $table_name . "`");
                    if (!$res && $debug) {
                        print_r($db->errorInfo());
                    } else {
                        $boolDiff = false;
                    }
                    */

                    $results['sql'][]="RENAME TABLE `" . $table_name2 . "` TO `" . $table_name . "`";
                }
			}






           if (!$boolDiff) { //If needed to check both, useless if one table is absent

                // Check fields exist in both tables
                $fields = array_merge($schema1['tables'][$table_name], $schema2['tables'][$table_name]);

                foreach ($fields as $field_name => $field) {

                    //fields table 1
                    $res = $this->checkField($schema1, $table_name, $field_name);

                    if (! is_bool($res)) {
                        $results['results'][$table_name][] = $res;
                    }

                    //fields table 2
                    $res = $this->checkField($schema2, $table_name, $field_name);

                    if (! is_bool($res)) {
                        $results['results'][$table_name][] = $res;
                    }


                    if ($res) {

                        $boolDiff = true;

                        $field_name2= $this->getFieldName($schema2, $table_name, $field_name);
                        $type=$this->getFieldType($schema1, $table_name, $field_name);

                        $boolCase = strcasecmp($field_name,$field_name2)==0 && strcmp($field_name,$field_name2)!=0; //retourne 1 si chaine similaire avec casse diff



                        if (!$boolCase) {
                            /*
                            $res = $db->exec("ALTER TABLE `" . $table_name . "` ADD `" . $field_name . "` " . $type);
                            if (!$res && $debug) {
                                print_r($db->errorInfo());
                            }
                            */
                            $results['sql'][]="ALTER TABLE `" . $table_name . "` ADD `" . $field_name . "` " . $type . " NOT NULL";
                        } else {
                            /*
                            $res = $db->exec("ALTER TABLE `" . $table_name . "` CHANGE `" . $field_name2 . "` `" . $field_name . "` " . $type);
                            if (!$res && $debug) {
                                print_r($db->errorInfo());
                            }
                            */
                            $results['sql'][]="ALTER TABLE `" . $table_name . "` CHANGE `" . $field_name2 . "` `" . $field_name . "` " . $type . " NOT NULL";
                        }


                    }

                    if (!$boolDiff) {

                        // Check that the specific parameters of the fields match

                        $s1_params = $schema1['tables'][$table_name][$field_name];
                        $s2_params = $schema2['tables'][$table_name][$field_name];

                        if (is_array($s1_params)) {
                            foreach ($s1_params as $name => $details) {
                                if ($s1_params[$name] != $s2_params[$name]) {
                                    $results['results'][$table_name][] = $schema2['name'] . "(DB2) has $field_name parameter $name with $s2_params[$name] instead of $s1_params[$name] from " . $schema1['name'] . "(DB1)";

                                    /*'Field ' . $field_name
                                        . ' differs between databases for parameter \''
                                        . $name . '\'. ' . $schema1['name']
                                        . ' has \'' . $s1_params[$name]
                                        . '\' and ' . $schema2['name']
                                        . ' has \'' . $s2_params[$name] . '\'.';*/
                                    if ($name=="Type") {
                                        $results['sql'][] = "ALTER TABLE `" . $table_name . "` MODIFY COLUMN `" . $field_name . "` " . $s1_params[$name] . " NOT NULL";
                                    }
                                }
                            }
                        }
                    }
                }
            }
		}

		return $results;
	}



    /*
     * Compare each table of the schema with the one in check, return the good one with no case issue
     * @param string $schema Schema of the database
     * @param string $table_name Name of the table actually in check
     * @return string
     */
    function getTableName($schema, $table_name){
        foreach (array_keys($schema["tables"]) as $x) {
            if (strcasecmp($table_name,$x)==0) {
                return $x;
            }
        }
        return null;
    }


    /*
     * Compare each field of the schema with the one in check, return the good one with no case issue
     * @param string $schema Schema of the database
     * @param string $table_name Name of the table actually in check
     * @param string $field_name Name of the field in check
     * @return string
     */
    function  getFieldName($schema, $table_name, $field_name) {
        foreach (array_keys($schema["tables"][$table_name]) as $x) {
            if (strcasecmp($field_name,$x)==0) {
                return $x;
            }
        }
        return null;
    }


    /*
     * Return the type of the field
     * @param string $schema Schema of the database
     * @param string $table_name Name of the table actually in check
     * @param string $field_name Name of the field in check
     * @return string
     */
    function getFieldType($schema, $table_name, $field_name) { //array
        $tmp = null;

        foreach (array_keys($schema["tables"][$table_name]) as $x) {
            if (strcasecmp($field_name,$x)==0) {
                $tmp = $x;
                break;
            }
        }
        if ($tmp!=null) {
            $tmp = $schema["tables"][$table_name][$tmp]["Type"];
        }
        return $tmp;
    }


    /*
     * Check if the table has differences
     * @param string $schema Schema of the database
     * @param string $table_name Name of the table actually in check
     * @return string
     */
    function checkTable($schema, $table_name) {
        $ret = false;
        if (!isset($schema['tables'][$table_name])) {
            $name = $this->getTableName($schema, $table_name);
            if (strcasecmp($name, $table_name) == 0) { //manque une table, lié à la casse
                $ret = $schema['name'] . ' has the table: ' . $table_name . ' but has a case difference';
            } else {
                $ret = $schema['name'] . ' is missing table: ' . $table_name;
            }
        }
        return $ret;
    }


    /*
     * Create the sql string to execute
     * @param string $schema Schema of the database
     * @param string $table_name Name of the table actually in check
     * @return string
     */
    function syncTable($schema, $table_name) {

        $data="";
        foreach ($schema['tables'][$table_name] as $field_name => $field) {
            if (isset($schema['tables'][$table_name][$field_name])) {

                $s1_params = $schema['tables'][$table_name][$field_name];
                $data=$data . "," . $field_name . " " . $s1_params["Type"]; //,URL varchar(20), etc..

            }
        }
        /*var_dump("CREATE TABLE " . $table_name . "(" . ltrim($data,",") . ")");
        $res = $db->exec("CREATE TABLE " . $table_name . "(" . ltrim($data,",") . ")"); //suppr la premiere virgule à gauche de data
        if (!$res && $debug) {
            print_r($db->errorInfo());
        }*/
        return "CREATE TABLE " . $table_name . "(" . ltrim($data,",") . ")";
    }




    /*
     * Check if the field has differences
     * @param string $schema Schema of the database
     * @param string $table_name Name of the table actually in check
     * @param string $field_name Name of the field in check
     * @return string
     */
    function checkField($schema, $table_name, $field_name) {
        $ret = false;
        if (!isset($schema['tables'][$table_name][$field_name])) {
            $ret = $schema['name'] . ' is missing field: ' . $field_name;
        }
        return $ret;
    }



}
