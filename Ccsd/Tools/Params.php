<?php

class Ccsd_Tools_Params
{
    public static function getScriptArguments($url)
    {
        $args = $params = array();

        if (strpos($url, '?') !== false || strpos($url, '&') !== false) {
            $arglist = '';
            if (strpos($url, '?') !== false) {
                list($target, $arglist) = preg_split("/\?/", $url);
            }
            $tmpargs = preg_split("/&/", $arglist);
            foreach ($tmpargs as $arg) {
                if (! $arg)continue;
                if (strpos($arg, '=') !== false) {
                    list($key, $val) = explode("=", $arg, 2);
                    if ($key) {
                        $args[preg_replace('/\[[0-9]*\]/', '', (string) $key)][] = urldecode($val);
                    }
                } else {
                    $args[preg_replace('/\[[0-9]*\]/', '', (string) $arg)][] = '';
                }
            }
            foreach ($args as $k=>$v) {
                if (is_array($v)) {
                    $params[$k] = count($v)>1 ? $v : $v[0] ;
                }
            }
        } else {
            $arglist = explode('/', trim($url, ' /'));
            if (count($arglist)%2 == 0) {
                // Présence controller/action
                unset($arglist[0], $arglist[1]);
            } else {
                // Réecriture
                unset($arglist[0]);
            }
            $arglist = array_values($arglist);
            foreach($arglist as $i => $elem) {
                if ($i%2 == 0) {
                    //Clé
                    if (!isset($params[$elem])) {
                        $params[$elem] = $arglist[$i + 1];
                    } else if (is_array($params[$elem])) {
                        $params[$elem][] = $arglist[$i + 1];
                    } else {
                        $params[$elem] = array($params[$elem], $arglist[$i + 1]);
                    }
                }
            }
        }
        return $params;
    }

}
