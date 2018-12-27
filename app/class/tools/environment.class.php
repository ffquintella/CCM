<?php
/**
 * Created by felipe. for gubd
 * Date: 13/10/14
 * Time: 17:51
 *
 * @author felipe
 *
 * @version 1.0
 */

namespace gcc\tools;


class environment
{

    static function getUserIP()
    {
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            if ($remote == "::1") $ip = "127.0.0.1";
            else $ip = $remote;
        }

        return $ip;
    }

} 