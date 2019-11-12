<?php

/**
 * Export data as CSV
 */
class Ccsd_Export_Csv
{
    public static function export($data, $filename = 'export', $seperator = ';', $surrounded = '"', $endLine = "\r\n", $display = true)
    {
        $return = '';
        if ($display) {
            // Send  Header
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/csv; charset=utf-8");
            header("Content-Disposition: attachment;filename=$filename.csv ");
            header("Content-Transfer-Encoding: binary ");
        }
        $informations = array();

        foreach ($data as $row => $values) {
            foreach ($values as $q => $a) {
                $informations[$q][$row] = $a;
            }
        }

        if (count($informations) != 0) {
            $return .= $surrounded . implode($surrounded . $seperator . $surrounded, array_keys($informations)) . $surrounded . $endLine;
            for ($i = 0; $i < count($data); $i++) {
                foreach ($informations as $q => $row) {
                    if (array_key_exists($i, $row)) {
                        if (is_array($row[$i])) {
                            $return .= $surrounded . str_replace("\"", "\"\"", implode(";", $row[$i])) . $surrounded . $seperator;
                        } else {
                            $return .= $surrounded . str_replace("\"", "\"\"", $row[$i]) . $surrounded . $seperator;
                        }
                    } else {
                        $return .= $surrounded . " " . $surrounded . $seperator;
                    }
                }
                $return .= $endLine;
            }
        }
        if ($display) {
            echo utf8_decode($return);
            exit;
        }
        return $return;
    }
}