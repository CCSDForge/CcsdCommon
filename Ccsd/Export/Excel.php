<?php
class Ccsd_Export_Excel
{
    public static function export($data, $filename = 'export')
    {
        // Send Header
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/msexcel");
        header("Content-Disposition: attachment;filename=$filename.xls "); // à¹à¸¥à¹‰à¸§à¸™à¸µà¹ˆà¸à¹‡à¸Šà¸·à¹ˆà¸­à¹„à¸Ÿà¸¥à¹Œ
        header("Content-Transfer-Encoding: binary ");

        // XLS Data Cell
        self::xlsBOF();
        foreach (array_keys($data[0]) as $col => $field) {
            self::xlsWriteLabel(0, $col, $field);
        }
         
        foreach ($data as $row => $values) {
            $tmp = array_values($values);
            foreach ($tmp as $col => &$val) {
                if (is_array($val)) {
                    $val = implode(" - ", $val);
                }
                self::xlsWriteLabel($row+1,$col,"$val");
            }
        }
        self::xlsEOF();
    }


    private static function xlsBOF()
    {
        echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
    }

    private static function xlsEOF()
    {
        echo pack("ss", 0x0A, 0x00);
    }

    private static function xlsWriteNumber($Row, $Col, $Value)
    {
        echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
        echo pack("d", $Value);
    }

    private static function xlsWriteLabel($Row, $Col, $Value )
    {
        $L = strlen($Value);
        echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
        echo $Value;
    }
}