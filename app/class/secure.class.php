<?php
namespace ccm;

require_once ROOT . "/class/sec/xtea.class.php";
require_once ROOT . "/class/sec/randomStrings.php";

if (!file_exists(ROOT . "/masterkey.php")) {
    echo "The System won't run without the master key \n";
    echo "Creating one... \n";
    $log = \ccm\logFactory::getLogger();
    $result = \ccm\masterKeyManager::createNewMasterKey();
    $log->Info("Master key created automatically.");

    require_once ROOT . "/masterkey.php";
} else require_once ROOT . "/masterkey.php";

require_once ROOT . "/class/sec/EncoderProtected.php";

class Secure
{

    private $authKey = "jshhsdWhsXBdfjvbnDdghxcssdksh7fhsd";
    private $cliKey = "SHHdbxg8sjkbxfBjsgmxc6jsg9gs";
    private $externalKey = "SFe349Gdhnb338shddgxnD12947Xnajdshdfdjhg5984hd21d";
    private $loginKey = "SdO628fdJ";
    private $crypt;
    private $internalKey = "88sjfSgcxbW";

    function __construct()
    {
        $this->crypt = new \ccm\sec\Xtea();
        $this->internalKey .= \get_master_key();
        $this->loginKey .= \get_master_key();
        $this->crypt->xtea_key_from_string($this->internalKey);
    }

    function vrfyAuth($auth)
    {
        if ($auth == null) return false;

        return ($this->authKey == substr($auth, 0, strlen($this->authKey)));
    }

    function vrfyAuthCli($auth)
    {
        if ($auth == null) return false;

        return ($this->cliKey == substr($auth, 0, strlen($this->cliKey)));
    }

    function vrfyAuthCrypt($auth)
    {
        return ($this->authKey == substr(decrypt($auth), 0, strlen($this->authKey)));
    }

    function decrypt($message)
    {

        $this->crypt->xtea_key_from_string($this->internalKey);

        return $this->crypt->xtea_decrypt_string($message);
    }

    function encrypt($message)
    {

        $this->crypt->xtea_key_from_string($this->internalKey);

        return $this->crypt->xtea_encrypt_string($message);
    }

    function extdecrypt($message)
    {

        $this->crypt->xtea_key_from_string($this->externalKey);

        return $this->crypt->xtea_decrypt_string($message);
    }

    function extencrypt($message)
    {

        $this->crypt->xtea_key_from_string($this->externalKey);

        return $this->crypt->xtea_encrypt_string($message);
    }

    function loginDecrypt($message)
    {

        $this->crypt->xtea_key_from_string($this->loginKey);

        return $this->crypt->xtea_decrypt_string($message);
    }

    function loginEncrypt($message)
    {

        $this->crypt->xtea_key_from_string($this->loginKey);

        return $this->crypt->xtea_encrypt_string($message);
    }

    function generateRandomString($length = 10)
    {

        return get_random_string($length);
    }
}

?>
