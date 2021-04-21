<?php
/**
 * Class Xtea
 * Montada a partir de arquivo externo e convertida em Classe por
 * Felipe Quintella
 */

namespace ccm\sec;

class Xtea
{

    const _XTEA_DELTA = 0x9E3779B9; // (sqrt(5) - 1) * 2^31
    const _xtea_num_rounds = 6;
    const _base64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    private $_xtea_key = array(0, 0, 0, 0);

// Returns Integer based on 8 byte Base64 Code XXXXXX== (llBase64ToInteger)

    function xtea_key_from_string($str)
    {
        $_xtea_key = $this->_xtea_key;
        $str = md5($str . ":0"); // Use nonce = 0 in LSL for same output
        eval("\$_xtea_key[0] = 0x" . substr($str, 0, 8) . ";");
        eval("\$_xtea_key[1] = 0x" . substr($str, 8, 8) . ";");
        eval("\$_xtea_key[2] = 0x" . substr($str, 16, 8) . ";");
        eval("\$_xtea_key[3] = 0x" . substr($str, 24, 8) . ";");
    }

// Returns 8 Byte Base64 code based on 32 bit integer ((llIntegerToBase64)

    function xtea_encrypt_string($str)
    {
        // encode Binany string to Base64
        $str = base64_encode($str);
        // remove trailing =s so we can do our own 0 padding
        $i = strpos($str, '=', 0);
        if ($i !== FALSE) {
            $str = substr($str, 0, $i);
        }
        // we don't want to process padding, so get length before adding it
        $len = strlen($str);
        // zero pad
        $str .= "AAAAAAAAAA=";
        $result = "";
        $i = 0;
        do {
            // encipher 30 (5*6) bits at a time.
            $enc1 = $this->base64_integer(substr($str, $i, 5) . "A==");
            $i += 5;
            $enc2 = $this->base64_integer(substr($str, $i, 5) . "A==");
            $i += 5;
            $result .= $this->xtea_encipher($enc1, $enc2);
        } while ($i < $len);
        return $result; //Return Encrypted string
    }

//strict 32 bit addition using logic

    function base64_integer($str)
    {
        //global $_base64;
        if (strlen($str) != 8) return 0;
        return ((strpos(self::_base64, $str[0]) << 26) |
            (strpos(self::_base64, $str[1]) << 20) |
            (strpos(self::_base64, $str[2]) << 14) |
            (strpos(self::_base64, $str[3]) << 8) |
            (strpos(self::_base64, $str[4]) << 2) |
            (strpos(self::_base64, $str[5]) >> 4));
    }


// Convers any string to a 32 char MD5 string and then to a list of
// 4 * 32 bit integers = 128 bit Key.

    function xtea_encipher($v0, $v1)
    {
        $_xtea_num_rounds = self::_xtea_num_rounds;
        $_xtea_key = $this->_xtea_key;
        $_XTEA_DELTA = self::_XTEA_DELTA;

        $num_rounds = $_xtea_num_rounds;
        $sum = 0;
        do {

            // LSL only has 32 bit integers. However PHP automaticly changes
            // 32 bit integers to 64 bit floats as nessesary. This causes
            // incompatibilities between the LSL Encryption and the PHP
            // counterpart. I got round this by changing all addition to
            // binary addition using logical & and ^ and loops to handle bit
            // carries. This forces the 32 bit integer to remain 32 bits as
            // I mask out any carry over 32 bits. this bring the output of the
            // encrypt routine to conform with the output of its LSL counterpart.
            // LSL does not have unsigned integers, so when shifting right we
            // have to mask out sign-extension bits.
            // calculate ((($v1 << 4) ^ (($v1 >> 5) & 0x07FFFFFF)) + $v1)
            $v0a = $this->binadd((($v1 << 4) ^ (($v1 >> 5) & 0x07FFFFFF)), $v1);
            // calculate ($sum + $_xtea_key[$sum & 3])
            $v0b = $this->binadd($sum, $_xtea_key[$sum & 3]);
            // Calculate ($v0 + ((($v1 << 4) ^ (($v1 >> 5) & 0x07FFFFFF)) + $v1)
            // ^ ($sum + $_xtea_key[$sum & 3]))
            $v0 = $this->binadd($v0, ($v0a ^ $v0b));
            //Calculate ($sum + $_XTEA_DELTA)
            $sum = $this->binadd($sum, $_XTEA_DELTA);
            //Calculate ((($v0 << 4) ^ (($v0 >> 5) & 0x07FFFFFF)) + $v0)
            $v1a = $this->binadd((($v0 << 4) ^ (($v0 >> 5) & 0x07FFFFFF)), $v0);
            // Calculate ($sum + $_xtea_key[($sum >>11) & 3])
            $v1b = $this->binadd($sum, $_xtea_key[($sum >> 11) & 3]);
            //Calculate ($v1 + ((($v0 << 4) ^ (($v0 >> 5) & 0x07FFFFFF)) + $v0
            // ^ ($sum & $_xtea_key[($sum >>11) & 3]))
            $v1 = $this->binadd($v1, ($v1a ^ $v1b));
        } while ($num_rounds = ~-$num_rounds);
        //return only first 6 chars to remove "=="'s and compact encrypted text.
        return substr($this->integer_base64($v0), 0, 6) . substr($this->integer_base64($v1), 0, 6);
    }

// Encipher two integers and return the result as a 12-byte string
// containing two base64-encoded integers.

    function binadd($val1, $val2)
    {
        $ta = $val1 ^ $val2;
        while ($tc = (($val1 & $val2) << 1) & 0x0FFFFFFFF) {
            $ta = ($val1 = $tc) ^ ($val2 = $ta);
        }
        return $ta; // $ta will now be the result so return it
    }
// Decipher two base64-encoded integers and return the FIRST 30 BITS of
// each as one 10-byte base64-encoded string.

    function integer_base64($int)
    {
        $_base64 = self::_base64;
        if ($int != (integer)$int) return 0;
        return $_base64[($int >> 26 & 0x3F)] .
            $_base64[($int >> 20 & 0x3F)] .
            $_base64[($int >> 14 & 0x3F)] .
            $_base64[($int >> 8 & 0x3F)] .
            $_base64[($int >> 2 & 0x3F)] .
            $_base64[($int << 4 & 0x3F)] . "==";
    }

// Encrypt a full string using XTEA.

    function xtea_decrypt_string($str)
    {
        $_base64 = self::_base64;
        $len = strlen($str);
        $i = 0;
        $result = "";
        do {
            $dec1 = $this->base64_integer(substr($str, $i, 6) . "==");
            $i += 6;
            $dec2 = $this->base64_integer(substr($str, $i, 6) . "==");
            $i += 6;
            $result .= $this->xtea_decipher($dec1, $dec2);
        } while ($i < $len);
        // Replace multiple trailing zeroes with a single one
        $i = strlen($result);
        while (substr($result, --$i, 1) == "A") ;
        $result = substr($result, 0, $i + 1);
        $i = strlen($result);
        $mod = $i % 4; //Depending on encoded length diffrent appends are needed
        if ($mod == 1) return base64_decode($result . "A==");
        else if ($mod == 2) {
            if ((strpos($_base64, substr($result, -1, 1))) & 0x0F) return base64_decode($result . "A=");
            else return base64_decode($result . "==");
        } else if ($mod == 3) {
            if ((strpos($_base64, substr($result, -1, 1))) & 0x03) return base64_decode($result . "A");
            else return base64_decode($result . "=");
        }
        return base64_decode($result);

    }

// Decrypt a full string using XTEA

    function xtea_decipher($v0, $v1)
    {
        $_xtea_num_rounds = self::_xtea_num_rounds;
        $_xtea_key = $this->_xtea_key;
        $_XTEA_DELTA = self::_XTEA_DELTA;

        $num_rounds = $_xtea_num_rounds;
        $sum = 0; // $_XTEA_DELTA * $_xtea_num_rounds;
        $tda = $_XTEA_DELTA;
        do { // Binary multiplication using binary manipulation
            if ($num_rounds & 1) {
                $sum = $this->binadd($sum, $tda);
            }
            $num_rounds = $num_rounds >> 1;
            $tda = ($tda << 1) & 0x0FFFFFFFF;
        } while ($num_rounds);
        $num_rounds = $_xtea_num_rounds; // reset $num_rounds back to its propper setting;
        do {
            // LSL only has 32 bit integers. However PHP automaticly changes
            // 32 bit integers to 64 bit floats as nessesary. This causes
            // incompatibilities between the LSL Encryption and the PHP
            // counterpart. I got round this by changing all addition to
            // binary addition using logical & and ^ and loops to handle bit
            // carries. This forces the 32 bit integer to remain 32 bits as
            // I mask out any carry over 32 bits. this bring the output of the
            // decrypt routine to conform with the output of its LSL counterpart.
            // Subtrations are handled by using 2's compliment
            // LSL does not have unsigned integers, so when shifting right we
            // have to mask out sign-extension bits.
            // calculate ((($v0 << 4) ^ (($v0 >> 5) & 0x07FFFFFF)) + $v0)
            $v1a = $this->binadd((($v0 << 4) ^ (($v0 >> 5) & 0x07FFFFFF)), $v0);
            // calculate ($sum + $_xtea_key[($sum>>11) & 3])
            $v1b = $this->binadd($sum, $_xtea_key[($sum >> 11) & 3]);
            //Calculate 2's compliment of ($v1a ^ $v1b) for subtraction
            $v1c = $this->binadd((~($v1a ^ $v1b)), 1);
            //Calculate ($v1 - ((($v0 << 4) ^ (($v0 >> 5) & 0x07FFFFFF)) + $v0)
            // ^ ($sum + $_xtea_key[($sum>>11) & 3]))
            $v1 = $this->binadd($v1, $v1c);
            // Calculate new $sum based on $num_rounds - 1
            $tnr = $num_rounds - 1; // Temp $num_rounds
            $sum = 0; // $_XTEA_DELTA * ($num_rounds - 1);
            $tda = $_XTEA_DELTA;
            do { // Binary multiplication using binary manipulation
                if ($tnr & 1) {
                    $sum = $this->binadd($sum, $tda);
                }
                $tnr = $tnr >> 1;
                $tda = ($tda << 1) & 0x0FFFFFFFF;
            } while ($tnr);
            //Calculate ((($v1 << 4) ^ (($v1 >> 5) & 0x07FFFFFF)) + $v1)
            $v0a = $this->binadd((($v1 << 4) ^ (($v1 >> 5) & 0x07FFFFFF)), $v1);
            //Calculate ($sum + $_xtea_key[$sum & 3])
            $v0b = $this->binadd($sum, $_xtea_key[$sum & 3]);
            //Calculate 2's compliment of ($v0a ^ $v0b) for subtraction
            $v0c = $this->binadd((~($v0a ^ $v0b)), 1);
            //Calculate ($v0 - ((($v1 << 4) ^ (($v1 >> 5) & 0x07FFFFFF)) + $v1
            // ^ ($sum + $_xtea_key[$sum & 3]))
            $v0 = $this->binadd($v0, $v0c);
        } while ($num_rounds = ~-$num_rounds);
        return substr($this->integer_base64($v0), 0, 5) . substr($this->integer_base64($v1), 0, 5);
    }
}

?>