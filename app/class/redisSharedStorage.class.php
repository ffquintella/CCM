<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 19/12/16
 * Time: 11:55
 */

namespace gcc;

require_once ROOT . "/data/redisServers.list.php";
require_once ROOT . "/interfaces/isharedStorage.interface.php";
require_once ROOT . "/vendor/predis/predis/autoload.php";
require_once ROOT . "/class/logFactory.class.php";

use Predis;

class redisSharedStorage implements isharedStorage
{

    protected $expirationTime;
    private $log;
    /** @var \Predis\Client */
    private $client;

    public function __construct($expirationTime = AUTH_TOKEN_TIME)
    {
        \Predis\Autoloader::register();
        $this->log = logFactory::getLogger();
    }

    public function putSet($set, $value)
    {
        if ($this->client == null) {
            $this->connect();
        }

        //$this->client->sadd(strtolower($set), array_change_key_case(array($value), CASE_LOWER));
        $this->client->sadd(strtolower($set), array($value));


        return true;
    }

    public function connectSingle()
    {

        $list = getRedisServersList();

        $node = $list->readNode(1);

        $this->client = new Predis\Client($node);
        $this->client->connect();

    }

    public function connect()
    {

        $list = getRedisServersList();

        $servers = array();
        for ($i = 1; $i <= $list->totalNodes(); $i++) {
            $val = $list->readNode($i);
            array_push($servers, $val);
            $this->log->Debug("Adding server to connection list", [ 'server' => $val['host'] ]);
        }

        if(count($servers) > 1)  $options    = ['replication' => true];
        else $options = array();
        $this->client = new Predis\Client($servers, $options);
        $this->client->connect();

    }

    public function rename(string $oldName, string $newName){
        if ($this->client == null) $this->connect();

        $this->client->rename($oldName, $newName);
    }

    public function ping(): bool
    {
        if ($this->client == null) $this->connect();

        if($this->client->isConnected()) return true;

        return false;
    }

    public function get(string $key)
    {
        if ($this->client == null) {
            $this->connect();
        }

        return $this->client->get(strtolower($key));
    }

    public function type(string $key):string
    {
        if ($this->client == null) $this->connect();
        return $this->client->type($key);
    }

    public function getPattern($pattern)
    {
        if ($this->client == null) {
            $this->connect();
        }

        return $this->client->keys($pattern);
    }

    public function getSet(string $setName)
    {
        if ($this->client == null) {
            $this->connect();
        }

        return $this->client->smembers(strtolower($setName));
    }

    public function set(string $key, string $value, int $expiration = -1)
    {
        if ($this->client == null) {
            $this->connect();
        }
        if ($expiration == -1)
            $this->client->set(strtolower($key), $value);
        else {
            $this->client->set(strtolower($key), $value);
            $this->client->expire(strtolower($key), $expiration);
        }
        return true;
    }

    public function getStatus()
    {
        return $this->client->isConnected();
    }

    public function replace($key, $value, $expiration = -1)
    {
        if ($this->client == null) {
            $this->connect();
        }
        if (!$this->client->get(strtolower($key))) return false;

        if ($expiration == -1)
            $this->client->set(strtolower($key), $value);
        else {
            $this->client->set(strtolower($key), $value);
            $this->client->expire(strtolower($key), $expiration);
        }
        return true;
    }

    public function del($key)
    {
        if ($this->client == null) {
            $this->connect();
        }

        $this->client->del(array(strtolower($key)));

        return true;
    }

    public function delSet($set, $value)
    {
        if ($this->client == null) {
            $this->connect();
        }

        $this->client->srem(strtolower($set), $value);

        return true;
    }

    public function searchSet(string $setName, string $pattern): array
    {
        if ($this->client == null) {
            $this->connect();
        }

        $result = array();
        foreach (new Predis\Collection\Iterator\SetKey($this->client, strtolower($setName), $pattern) as $value) {
            $result[] = $value;
        }


        return $result;
    }
}