<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 09/01/17
 * Time: 17:19
 */

namespace gcc;


class mockVault implements ivault
{

    function getPassword(string $resource): string
    {
        switch ($resource) {
            case '6044:6390':
                return 'Test';
            default:
                return '---';
        }
    }

    /**
     * @return \gcc\vaultObject[]
     */
    function listVaultKeys()
    {
        $vault = new vaultObject();
        $vault->details = "dummy1";
        $vault->resource = "res1";

        $vaults[] = $vault;

        return $vaults;
    }
}