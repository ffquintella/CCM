<?php
/**
 * User: felipe
 * Date: 03/03/14
 * Time: 16:32
 */
namespace gcc;

require_once ROOT . "/class/sec/EncoderProtected.php";
require_once ROOT . "/class/sec/randomStrings.php";

/**
 * Class masterKeyManager
 * Be careful this class wasn't designed for multi user execution!!!
 * @package gcc
 */
class masterKeyManager
{

    /**
     * @return bool
     *    true - exists
     *    false - doesn't
     */
    public static function masterKeyExists()
    {
        return file_exists(self::getFileName());
    }

    public static function getFileName()
    {
        return ROOT . '/masterkey.php';
    }

    public static function deleteMasterKey()
    {
        unlink(self::getFileName());
    }

    public static function recreateMasterKey()
    {
        self::createNewMasterKey(true);
    }

    /**
     * @param bool $regen
     * @return int
     *    -1 - Can't regrate without the regen parameter
     *    -2 - Can't open file
     *    1 - Generation OK
     */
    public static function createNewMasterKey($regen = false, string $fileName = '', int $keySize = 16)
    {

        if ($fileName == '') $fileName = self::getFileName();


        $fh = null;
        if ($regen) {
            $fh = fopen($fileName, 'w+');
        } else {
            if (!file_exists($fileName)) $fh = fopen($fileName, 'x');
        }

        if (!$fh) {
            if ($regen) {
                return -1;
                return;
            } else {
                return -2;
                return;
            }
        }
        $tmp = base64_encode(gzdeflate(" function get_master_key(){ return \\code_to_string('" . \string_to_code(get_random_string($keySize)) . "');} "));
        $textout = <<<EOD
<?php
/* Code protected for security reasons */
/** Este código está protegido qualquer dúvida favor procurar o ESI */
\$klnxjshd=" eval(gzinflate(base64_decode('$tmp')));"; call_user_func('bo928hSdjf', \$klnxjshd); for(\$kjd=0;\$kjd == \$klnxjshd; \$kjd++) \$bo928hSdjf = \$klnxjshd; function bo928hSdjf(\$prm){eval(\$prm);}
EOD;

        fputs($fh, $textout);
        fclose($fh);
        return 1;
    }


} 