<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 13/03/14
 * Time: 11:58
 */

namespace gcc;


interface isharedStorage
{

    public function __construct($expirationTime);

    /**
     * @param $key string - The key being searched
     * @return mixed
     */
    public function get(string $key);

    /***
     * Tests the connection to the storage
     * @return bool
     */
    public function ping():bool;

    /**
     * @param $setName string - The name of the set being searched
     * @return mixed
     */
    public function getSet(string $setName);

    /**
     * @param string $setName
     * @param string $pattern
     * @return array|null
     */
    public function searchSet(string $setName, string $pattern): ?array;

    /**
     * @param string $key
     * @param string $value
     * @param int $expiration
     * @return mixed
     */
    public function set(string $key, string $value, int $expiration);

    /**
     * @param $set - The name of the set
     * @param $value - The value to add
     * @return mixed
     */
    public function putSet($set, $value);

    /**
     * @param $set - The name of the set
     * @param $value - The value to remove
     * @return mixed
     */
    public function delSet($set, $value);

    public function rename(string $oldName, string $newName);

    public function type(string $key):string ;

    public function replace($key, $value, $expiration);

    /**
     * Deletes the specified key
     * @param $key string
     * @return mixed
     */
    public function del($key);

    public function connect();

    public function getStatus();

} 