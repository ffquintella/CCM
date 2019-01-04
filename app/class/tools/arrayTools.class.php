<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 13/09/2018
 * Time: 14:36
 */

namespace ccm\tools;

require_once "strTools.class.php";

class arrayTools
{
    /***
     * @param array $haystack
     * @param string $needle
     * @return bool
     */
    static function array_key_startsWith(array $haystack, string $needle):bool
    {

        foreach ($haystack as $key => $value){
            if(strTools::startsWith($key, $needle)) return true;
        }

        return false;
    }

    /**
     * Changes all values of input to lower- or upper-case.
     *
     * @param array $input The array to change values in
     * @param int   $case  The case - can be either CASE_UPPER or CASE_LOWER (constants)
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return array The resulting (processed) array
     */
    static function array_change_value_case($input, $case = CASE_LOWER)
    {
        $result = [];
        if (!is_array($input)) {
            return $result;
        }
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $result[$key] = array_change_value_case($value, $case);
                continue;
            }
            $result[$key] = $case == CASE_UPPER ? strtoupper($value) : strtolower($value);
        }
        return $result;
    }
}