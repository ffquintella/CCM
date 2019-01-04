<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 13/03/14
 * Time: 11:56
 */

namespace ccm;

require_once ROOT . "/class/liteMemcacheSharedStorage.class.php";
require_once ROOT . "/class/redisSharedStorage.class.php";
require_once ROOT . "/class/mockSharedStorage.class.php";

class sharedStorageFactory
{

    public static function getSharedStorage()
    {
        if (defined('UNIT_TESTING')) {
            return new mockSharedStorage();
        } else {
            switch (SHARED_VALUES_SERVER) {
                case 'memcache':
                    switch (SHARED_VALUES_IMPLEMENTATION) {
                        case 'liteMemcache':
                            return new liteMemcacheSharedStorage();
                            break;
                        case 'redis':
                            return new redisSharedStorage();
                            break;
                        default:
                            throw new \Exception("Non existing implementation");
                            break;
                    }
                    break;
                case 'redis':
                    switch (SHARED_VALUES_IMPLEMENTATION) {
                        case 'predis':
                            return new redisSharedStorage();
                            break;
                        default:
                            throw new \Exception("Non existing implementation");
                            break;
                    }
                    break;
                default:
                    throw new \Exception("Not implemented storage");
                    break;
            }
        }

    }

} 