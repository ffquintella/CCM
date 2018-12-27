<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 04/03/14
 * Time: 00:18
 */

namespace gcc;


class logFactory
{

    static function getLogger()
    {
        include_once LOGCLASS;
        return new log();
    }
} 