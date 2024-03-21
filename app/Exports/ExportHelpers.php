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

    public static function prepare($start_cell, $count_routes, $max_part)
    {
        $part = 1;
        $left_routes = 10;
        $all_route = $count_routes;
        $title_array = [];
        while($part <= $max_part){
            if($all_route < 10){
                $left_routes = $all_route;
            }
            $title_array[] = self::generate_excel_title_array($left_routes * 2, $start_cell);
            $part++;
            $start_cell=$start_cell+3;
            $all_route = $all_route - $left_routes;
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
