<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 03/03/14
 * Time: 15:56
 */
/**
 * @param $length - Size of the random string to be generated
 * @param int $mode 0 - all ascii characters 1- alpha-numerics 2- letters 3- letters small case
 * @return string The random string generated
 */
function get_random_string($length, $mode = 1)
{


    if ($mode == 0) $valid_chars = "0123456789abcdefghijklmnoprstuvxyzABCDEFGHIJKLMNOPQRSTUVXYZ!@#$%^&*?;:";
    if ($mode == 1) $valid_chars = "0123456789abcdefghijklmnoprstuvxyzABCDEFGHIJKLMNOPQRSTUVXYZ";
    if ($mode == 2) $valid_chars = "abcdefghijklmnoprstuvxyzABCDEFGHIJKLMNOPQRSTUVXYZ";
    if ($mode == 3) $valid_chars = "abcdefghijklmnoprstuvxyz";

    $first_char = "abcdefghijklmnoprstuvxyzABCDEFGHIJKLMNOPQRSTUVXYZ";
    $num_fist_char = strlen($first_char);
    $random_pick = mt_rand(1, $num_fist_char);
    $random_string = $first_char[$random_pick - 1];

    $num_valid_chars = strlen($valid_chars);

    for ($i = 1; $i < $length; $i++) {
        $random_pick = mt_rand(1, $num_valid_chars);
        $random_char = $valid_chars[$random_pick - 1];

        $random_string .= $random_char;
    }

    return $random_string;
}