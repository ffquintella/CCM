<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 13/03/14
 * Time: 11:55
 */

namespace ccm;

use SebastianBergmann\Exporter\Exception;

require_once ROOT . "/data/memcacheServers.list.php";
require_once ROOT . "/interfaces/isharedStorage.interface.php";


class liteMemcacheSharedStorage implements isharedStorage
{

    protected $expirationTime, $lmc, $serverHash, $connStats;

    public function __construct($expirationTime = AUTH_TOKEN_TIME)
    {

        $this->serverHash = new \Flexihash();
        $this->expirationTime = $expirationTime;

        $list = getMemcacheServersList();

        for ($i = 1; $i <= $list->totalNodes(); $i++) {
            $val = $list->readNode($i);
            $this->connStats[$val['ip'] . ':' . $val['port']] = "OFFLINE";

        }

    }

    public function get(string $key)
    {

        $server = $this->serverHash->lookup($key);
        $val = $this->lmc[$server]->get($key);

        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = logFactory::getLogger();
            $log->Debug("Getting memcache  key=" . $key . " server=" . $server . " val=" . $val);
        }

        return $val;
    }

    public function ping(): bool
    {
        return true;
    }

    public function set(string $key, string $value, int $expiration = -1)
    {
        if ($expiration == -1) $expiration = $this->expirationTime;
        $server = $this->serverHash->lookup($key);

        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = logFactory::getLogger();
            $log->Debug("Setting memcache value=" . $value . " key=" . $key . " server=" . $server);
        }

        return $this->lmc[$server]->set($key, $value, $expiration);

    }

    public function connect()
    {
        $list = getMemcacheServersList();
        $this->lmc = array();
        for ($i = 1; $i <= $list->totalNodes(); $i++) {
            $val = $list->readNode($i);
            try {
                $this->lmc[$val['ip'] . ':' . $val['port']] = new \LiteMemcache($val['ip'] . ':' . $val['port']);
                if ($this->connStats[$val['ip'] . ':' . $val['port']] == "OFFLINE") {
                    $this->serverHash->addTarget($val['ip'] . ':' . $val['port']);
                    $this->connStats[$val['ip'] . ':' . $val['port']] = "ONLINE";
                }
            } catch (Exception $ex) {
                $this->serverHash->removeTarget($val['ip'] . ':' . $val['port']);
                $this->connStats[$val['ip'] . ':' . $val['port']] = "OFFLINE";
            }
        }

    }

    public function getStatus()
    {
        $result = null;

        foreach ($this->connStats as $key => $value) {
            if ($value == "ONLINE")
                $result .= 'Server:' . $key . ' ONLINE ';
        }
        //return $this->mc->getStats();
        return $result;
    }

    public function replace($key, $value, $expiration = -1)
    {
        if ($expiration == -1) $expiration = $this->expirationTime;
        $server = $this->serverHash->lookup($key);
        return $this->lmc[$server]->replace($key, $value, 0, $expiration);
    }

    public function getSet(string $setName)
    {
        // TODO: Implement getSet() method.
    }

    public function del($key)
    {
        // TODO: Implement del() method.
    }

    public function type(string $key):string
    {
        // TODO: Implement del() method.
        return "";
    }

    public function putSet($key, $value)
    {
        // TODO: Implement putSet() method.
    }

    public function rename(string $oldName, string $newName){
        // TODO: Implement rename() method.
        throw  new \Exception("Not implemented");
    }

    public function delSet($set, $value)
    {
        // TODO: Implement delSet() method.
    }

    public function searchSet(string $setName, string $pattern): array
    {
        // TODO: Implement searchSet() method.
    }
}