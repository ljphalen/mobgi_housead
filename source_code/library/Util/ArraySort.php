<?php
/**
 * 数组排序
 * @author wupeng
 */
class Util_ArraySort {

    /**
    $sortCriteria = 
        array(
            'field1' => array(SORT_DESC, SORT_NUMERIC), 
            'field2' => array(SORT_DESC, SORT_NUMERIC)
        );
    $sortedData = Util_ArraySort::multiSort($data, $sortCriteria);
    */
    public static function multiSort($data, $sortCriteria, $caseInSensitive = true) {
        if (! is_array($data) || ! is_array($sortCriteria)) {
            return false;
        }
        $args = array();
        $i = 0;
        foreach ($sortCriteria as $sortColumn => $sortAttributes) {
            $colList = array();
            foreach ($data as $key => $row) {
                $convertToLower = $caseInSensitive && (in_array(SORT_STRING, $sortAttributes) || in_array(SORT_REGULAR, $sortAttributes));
                $rowData = $convertToLower ? strtolower($row[$sortColumn]) : $row[$sortColumn];
                $colLists[$sortColumn][$key] = $rowData;
            }
            $args[] = &$colLists[$sortColumn];
            
            foreach ($sortAttributes as $sortAttribute) {
                $tmp[$i] = $sortAttribute;
                $args[] = &$tmp[$i];
                $i ++;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return end($args);
    }
    
}