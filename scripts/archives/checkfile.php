<?php
set_time_limit(0);
ini_set('memory_limit','8144M');
set_include_path(implode(PATH_SEPARATOR, array_merge(array('/sites/phplib', '/sites/library'), array(get_include_path()))));

header('Content-Type: text/html; charset=UTF-8');

require 'Zend/Debug.php';
require 'Zend/Db.php';
require 'Zend/Db/Table/Abstract.php';
require 'Ccsd/Tools.php';
require 'Ccsd/File.php';

//Paramètres
$debug = true;
// V3
$paramHALV3 = array(
    'adapter'         =>   "Pdo_MySQL",
    'persistent'      =>   true,
    'host'            =>   "ccsddb04.in2p3.fr",
    'dbname'          =>   "HALV3",
    'username'        =>   "root",
    'password'        =>   'e+d<HAL',
    'driver_options'  =>   array(1002 => "SET NAMES utf8"),
    'charset'         =>   "utf8",
    'profiler'        =>   array('enabled' => false),
    'path'            =>   '/docs/'
);

$dbHALV3 = new PDO('mysql:host='. $paramHALV3['host'] .';dbname='. $paramHALV3['dbname'] .'', $paramHALV3['username'], $paramHALV3['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"));
Zend_Db_Table_Abstract::setDefaultAdapter(Zend_Db::factory('Pdo_Mysql', $paramHALV3));

$files = $dbHALV3->query("SELECT * FROM `DOC_FILE` WHERE 1 ORDER BY DOCID ASC");
foreach($files->fetchAll() as $file ) {
    $pathDirV3 = $paramHALV3['path'].wordwrap(sprintf("%08d", $file['DOCID']), 2, DIRECTORY_SEPARATOR, 1);
    $pathDirV2 = '/documents/'.wordwrap(sprintf("%08d", $file['DOCID']), 2, DIRECTORY_SEPARATOR, 1);
    if ( !is_file($pathDirV3.DIRECTORY_SEPARATOR.$file['FILENAME']) ) {
        echo $file['DOCID'].' - '.$file['FILENAME'].': ';
        if ( is_file($pathDirV2.'/PDF/'.$file['FILENAME']) ) {
            if ( is_file($pathDirV2.'/archives/BAK_'.$file['FILENAME']) ) {
                println((int)copy($pathDirV2 . '/archives/BAK_/' . $file['FILENAME'], $pathDirV3 . DIRECTORY_SEPARATOR . $file['FILENAME']));
            } else {
                println((int)copy($pathDirV2 . '/PDF/' . $file['FILENAME'], $pathDirV3 . DIRECTORY_SEPARATOR . $file['FILENAME']));
            }
        } else {
            println('pas trouvé !!!');
        }
    }
}

println();

function println($s = '')
{
	print $s . "\n";
}

function array_values_notnull($tableau, $keep_identic_values = false, $reindex = false) {
	$ret = array();
	if ( is_array($tableau) && count($tableau) > 0 ) {
		if ( $reindex ) {
			$i = 0;
		}
		foreach( $tableau as $key=>$value ) {
			if ( mb_strlen(trim($value)) ) {
				if ( $keep_identic_values ) {
					if ( $reindex ) {
						$ret[$i++] = $value;
					} else {
						$ret[$key] = $value;
					}
				} else {
					if ( !in_array($value, $ret) ) {
						if ( $reindex ) {
							$ret[$i++] = $value;
						} else {
							$ret[$key] = $value;
						}
					}
				}
			}
		}
	}
	return( $ret );
}

function decrypt_file($file) {
    if ( is_readable($file) && is_file($file) ) {
        @touch($tmpfile = $file.".tmp");
        if ( $fp = fopen($file, "r") ) {
            $filecontents = fread($fp, filesize($file));
            fclose($fp);
            if ( $fp = fopen($tmpfile, "w") ) {
                $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
                if ( fwrite($fp, mcrypt_decrypt(MCRYPT_RIJNDAEL_256, "yh#t23i%o78", base64_decode($filecontents), MCRYPT_MODE_ECB, $iv)) ) {
                    fclose($fp);
                    unlink($file);
                    rename($tmpfile, $file);
                    return TRUE;
                }
                fclose($fp);
                return FALSE;
            }
            return FALSE;
        }
        return FALSE;
    }
    return FALSE;
}

function decrypt_dir_content($path) {
    // end with a / ?
    while ( mb_substr($path, -1) == "/" ) {
        $path = mb_substr($path, 0, mb_strlen($path)-1);
    }
    if ( is_file($path) ) {
        return decrypt_file($path);
    }
    if ( is_dir($path) ) {
        $dh = opendir($path);
        while ( $file = readdir($dh) ) {
            if ( $file != '.' && $file != '..' ) {
                $fullpath = $path."/".$file;
                if ( is_dir($fullpath) ) {
                    decrypt_dir_content($fullpath);
                }
                if ( is_file($fullpath) ) {
                    decrypt_file($fullpath);
                }
            }
        }
        closedir($dh);
    }
}

function get_all_dir($rootdirpath, $sauf_dir=array()) {
    $rootdirpath = preg_replace("~/[/]*~", '/', $rootdirpath.'/');
    $res = array();
    if ( $dir = @opendir($rootdirpath) ) {
        $res[] = $rootdirpath;
        while ( ($file = @readdir($dir)) !== false ) {
            if ( is_readable($rootdirpath.$file) && is_dir($rootdirpath.$file) && $file != "." && $file != ".." && !in_array($file, $sauf_dir) ) {
                $res = array_merge( $res, get_all_dir($rootdirpath.$file, $sauf_dir) );
            }
        }
        closedir($dir);
    }
    return $res;
}

function list_file($racine, $sauf_dir=array(), $with_racine=true, $filename_regexp='') {
    $files = array();
    $racine = preg_replace("~/[/]*~", "/", $racine."/");
    if ( is_readable($racine) && is_dir($racine) ) {
        $all_dir_in_racine = get_all_dir($racine, $sauf_dir);
        for ( $i=0; $i<count($all_dir_in_racine); $i++ ) {
            $current_dir = $all_dir_in_racine[$i];
            $dir = @opendir($current_dir);
            while ( ($file = @readdir($dir)) !== false ) {
                if ( is_readable($current_dir.$file) && is_file($current_dir.$file) && $file != "." && $file != ".." ) {
                    if ( $filename_regexp != '' ) {
                        if ( preg_match($filename_regexp, $file) ) {
                            $files[] = ($with_racine) ? $current_dir.utf8_encode($file) : utf8_encode($file);
                        }
                    } else {
                        $files[] = ($with_racine) ? $current_dir.utf8_encode($file) : utf8_encode($file);
                    }
                }
            }
            closedir($dir);
        }
    }
    return($files);
}