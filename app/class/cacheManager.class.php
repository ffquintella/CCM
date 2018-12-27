<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 10/01/17
 * Time: 10:26
 */

namespace gcc;


class cacheManager
{
    /**
     * @var integer
     */
    private $timeout;

    public function __construct(int $timeout = CACHE_DEFAULT_TIMEOUT)
    {
        $this->timeout = $timeout;
    }

    /**
     * @param string $key
     * @param string $value
     * @throws wrongFunctionParameterEX - 1- $key cannot be empty
     */
    public function setCachedValue(string $key, string $value)
    {
        if ($key == '') throw new wrongFunctionParameterEX('$key cannot be empty', 1);

        $ss = sharedStorageFactory::getSharedStorage();
        $ss->set('cache:' . $key, $value, $this->timeout);
    }

    /**
     * @param string $key
     * @return string|null
     * @throws wrongFunctionParameterEX - 1- $key cannot be empty
     */
    public function getCachedValue(string $key):?string
    {
        if ($key == '') throw new wrongFunctionParameterEX('$key cannot be empty', 1);
        $ss = sharedStorageFactory::getSharedStorage();
        return $ss->get($key);
    }

}