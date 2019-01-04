<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 09/01/17
 * Time: 17:20
 */

namespace ccm;


use ccm\vaultObject;

interface ivault
{

    /**
     * Gets the password from the vault
     *
     * @param string $resource
     * @return mixed
     */
    function getPassword(string $resource): string;


    /**
     * @return vaultObject[]
     */
    function listVaultKeys();


}