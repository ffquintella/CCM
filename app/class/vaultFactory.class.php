<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 09/01/17
 * Time: 11:56
 */

namespace gcc;

require_once ROOT . "/class/pmpVault.class.php";
require_once ROOT . "/class/mockVault.class.php";
require_once ROOT . "/interfaces/ivault.interface.php";
require_once ROOT . "/class/vaultObject.class.php";

class vaultFactory
{

    public static function getVault(): ivault
    {
        if (defined('UNIT_TESTING')) {
            return new mockVault();
        } else {
            switch (VAULT_TYPE) {
                case 'pmp':
                    return new pmpVault();
                    break;
                default:
                    throw new \Exception("Not implemented vault");
                    break;
            }
        }

    }

} 