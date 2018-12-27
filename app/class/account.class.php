<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 13/03/14
 * Time: 22:47
 */

namespace gcc;


class account
{

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var Secure
     */
    protected $sec;

    function __construct($name, $password)
    {
        $this->sec = new Secure();
        $this->name = $name;

        if (strpos($password, '#:#') == false) {
            $salt = $this->sec->generateRandomString(5);
            $pwd = $salt . "#:#" . md5($salt . $password);
        } else {
            $pwd = $password;
        }

        $this->password = $this->sec->loginEncrypt($pwd);
    }

    /**
     * @param $password - The password bein set
     * @return bool - true if ok - false if not validated
     */
    public static function validadePasswordComplexity($password)
    {
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number = preg_match('@[0-9]@', $password);

        if (!$uppercase || !$lowercase || !$number || strlen($password) < USER_PASS_SIZE) {
            return false;
        } else return true;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        $lpass = $this->sec->loginDecrypt($this->password);
        return explode("#:#", $lpass)[1];
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $this->sec->loginEncrypt($password);
    }

    /**
     * @return mixed
     */
    public function getSalt()
    {
        $lpass = $this->sec->loginDecrypt($this->password);
        return explode("#:#", $lpass)[0];
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


} 