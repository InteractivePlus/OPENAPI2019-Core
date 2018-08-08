<?php
namespace BoostPHP{
    require_once __DIR__ . "/internal/BoostPHP.internal.php";
    Class FastCompare{
        /**
        * Sort Array by Native Quick Sort Method(Partition)
        * About 20 times faster then quicksort implemented by the PHP code.
        * @param array $array array to be sorted
        * @return void
        */
        public static function quickSort(&$array){
            sort($array);
            return;
        }
        
        /**
         * Sort Array by Native Quick Sort Method(Partition)
         * The function compares two elements in the array.
         * @param array $array array to be sorted
         * @return void
         */
        public static function customCompareSort(&$array, $functionName){
            usort($array, $functionName);
            return;
        }
    }
}