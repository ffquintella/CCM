<?php
/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 26/12/16
 * Time: 17:11
 */


class loginManager
{

    public static function isAuthenticated(){

        loginManager::verifyDirectories();

        if(file_exists($_SERVER['HOME'].'/.gcc-cli/session')){
            $handle = @fopen($_SERVER['HOME'].'/.gcc-cli/session', "r");
            if ($handle) {

                $buffer = fgets($handle, 4096);

                $buffer = preg_replace( "/\r|\n/", "", $buffer );

                if(!$buffer) return false;

                $GLOBALS['SESSION_TIMEOUT'] = $buffer;

                $buffer = fgets($handle, 4096);

                $buffer = preg_replace( "/\r|\n/", "", $buffer );

                if(!$buffer) return false;

                $GLOBALS['SESSION_USER'] = $buffer;

                $buffer = fgets($handle, 4096);

                $buffer = preg_replace( "/\r|\n/", "", $buffer );

                if(!$buffer) return false;

                $GLOBALS['SESSION_TOKEN'] = $buffer;

                fclose($handle);

                if($GLOBALS['SESSION_TIMEOUT'] < time()) return false;
                else {
                    self::writeSession($GLOBALS['SESSION_USER'],$GLOBALS['SESSION_TOKEN']);
                    return true;
                }
            }
        }else return false;
    }


    private static function verifyDirectories() {

       // var_dump($_SERVER); exit;

        if(!file_exists($_SERVER['HOME'].'/.gcc-cli')){
            mkdir($_SERVER['HOME'].'/.gcc-cli');
        }
    }

    public static function writeSession($user, $token){
        $fp = @fopen($_SERVER['HOME'].'/.gcc-cli/session', "w+");
        fwrite($fp, time() + SESSION_TIMEOUT."\n");
        fwrite($fp, $user."\n");
        fwrite($fp, $token."\n");
        fclose($fp);

        $GLOBALS['SESSION_TIMEOUT'] = time() + SESSION_TIMEOUT;
        $GLOBALS['SESSION_USER'] = $user;
        $GLOBALS['SESSION_TOKEN'] = $token;
    }

}