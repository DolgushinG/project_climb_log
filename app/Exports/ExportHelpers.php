<?php

namespace App\Exports;

class ExportHelpers
{
    public static function generate_excel_title_array($length, $start_cell) {
        $title_array = [];
        $start_col = 0;
        $end_col = 1;
        while ($end_col <= $length) {
            $title_array[] = self::column_letter($start_col) . $start_cell.':' . self::column_letter($end_col) . $start_cell;
            $start_col += 2;
            $end_col += 2;
        }
        return $title_array;
    }
    public static function merge_arrays($arr) {
        $merged = [];

        foreach ($arr as $item) {
            if (is_array($item)) {
                $merged = array_merge($merged, self::merge_arrays($item));
            } else {
                $merged[] = $item;
            }
        }
        return $merged;
    }

    public static function countCell($numbers) {
        $res = 0;
        $count = 1;
        for ($i = 1; $i <= $numbers; $i++) {
            if ($count === 10) {
                $res++;
                $count = 0;
            }
            $count++;
        }
        // Если в данных остались элементы
        if ($res * 10 === $numbers) {
           return $res;
        } else {
            return $res + 1;
        }
    }

    public static function column_letter($n) {
        // Converts a 0-indexed column number to Excel-style column letter.
        $result = '';
        while ($n >= 0) {
            $remainder = $n % 26;
            $result = chr(65 + $remainder) . $result;
            $n = floor($n / 26) - 1;
        }
        return $result;
    }

}
