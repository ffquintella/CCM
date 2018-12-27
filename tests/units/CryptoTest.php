<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 02/03/14
 * Time: 18:10
 */

namespace gcc\Tests;

use gcc\masterKeyManager;
use gcc\sec\Xtea;

require_once "../app/vars.php";
require_once ROOT."/class/sec/EncoderProtected.php";
require_once ROOT."/class/masterKeyManager.class.php";
require_once ROOT."/class/sec/xtea.class.php";
require_once ROOT."/masterkey.php";


class CryptoTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group Regression
     */
    function testEncoder(){
        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Executando testes de encoder... \n";
        $text = "abcd a";
        $code = \string_to_code($text);
        $code_open = \code_to_string($code);
        $this->assertEquals($text, $code_open);
        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Fim dos testes de encoder... \n";

    }

    /**
     * @group Unity
     */
    function testMasterKey(){
        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Executando testes de masterkey... \n";
        $this->assertTrue(masterKeyManager::masterKeyExists());
        $this->assertEquals(-2,masterKeyManager::createNewMasterKey());
        $this->assertNotNull(\get_master_key());
        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Fim dos testes de masterkey... \n";
    }

    /**
     * @group Unity
     */
    function testXTea(){
        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Executando testes de encriptação... \n";
        $xtea = new Xtea();
        $xtea->xtea_key_from_string('ChaveDeTesteDeEncryptação');
        $enc = $xtea->xtea_encrypt_string("Teste 123456");

        if (TEST_VERBOSE_LEVEL >= \verbose::DEBUG ) echo "Saida do teste: ". $enc ."\n";
        $open = $xtea->xtea_decrypt_string($enc);
        $this->assertEquals("Teste 123456", $open);
        if (TEST_VERBOSE_LEVEL >= \verbose::INFO ) echo "Fim do teste de encriptação. \n";

    }
}








