<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 02/03/14
 * Time: 18:10
 */

namespace ccm\Tests\ws;

use ccm\tools\strTools;

require_once "../app/vars.php";

require_once ROOT."/class/logFactory.class.php";
require_once ROOT."/class/tools/strTools.class.php";

class databasesTest extends \PHPUnit_Framework_TestCase {

    private $ch, $url, $token;

    function setUp() {
        //open connection

        $this->url = WS_TEST_URL.'databases';
        //?XDEBUG_SESSION_START=gubddev
    }

    /**
     * @group Regression
     */
   /* function testGetUser(){
        $this->setUp();


        //set POST variables

        $fields = array(
            'username' => 'utestes',
            'password' => 'teste',
        );

        $fields_string = "";
        //url-ify the data for the POST
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        $this->ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($this->ch,CURLOPT_HEADER, false);
        curl_setopt($this->ch,CURLOPT_URL, WS_TEST_URL.'authenticationLogin');
        //curl_setopt($this->ch, CURLOPT_USERPWD, 'Stestes' . ":" . 'teste');
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch,CURLOPT_POST, count($fields));
        curl_setopt($this->ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($this->ch,CURLOPT_CERTINFO, true);
        curl_setopt($this->ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch,CURLOPT_SSL_VERIFYHOST, 0);

        //execute post
        $result = curl_exec($this->ch);

        $http_status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $this->assertEquals("200", $http_status);

        $this->assertTrue(strTools::startsWith($result, '{"userName":"utestes","tokenType":"user","tokenValue":'));

        $tokenFull = json_decode($result);


        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'Authorization: GUBD:'.$tokenFull->tokenValue
        ));

        //curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->ch,CURLOPT_URL, $this->url."?format=json");
        curl_setopt($this->ch,CURLOPT_POST, false);

        $result = curl_exec($this->ch);

        $http_status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        $this->assertEquals("200", $http_status);

        $this->assertTrue(strTools::startsWith($result, '[{"name":"Banco_Testes","value":{"name":"Banco Testes",'));


        //close connection
        curl_close($this->ch);



        $this->tearDown();
    }*/

    /**
     * @group Regression
     */
    function testGet(){
        $this->setUp();


        //set POST variables

        $fields = array(
            'username' => 'Stestes',
            'password' => 'teste',
        );

        $fields_string = "";
        //url-ify the data for the POST
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        $this->ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($this->ch,CURLOPT_HEADER, false);
        curl_setopt($this->ch,CURLOPT_URL, WS_TEST_URL.'authenticationLogin');
        //curl_setopt($this->ch, CURLOPT_USERPWD, 'Stestes' . ":" . 'teste');
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch,CURLOPT_POST, count($fields));
        curl_setopt($this->ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($this->ch,CURLOPT_CERTINFO, true);
        curl_setopt($this->ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch,CURLOPT_SSL_VERIFYHOST, 0);

        //execute post
        $result = curl_exec($this->ch);


        $this->assertTrue(strTools::startsWith($result, '{"userName":"Stestes","tokenType":"system","tokenValue":'));

        $tokenFull = json_decode($result);


        curl_reset($this->ch);

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'Authorization:'.$tokenFull->tokenValue
        ));


        curl_setopt($this->ch,CURLOPT_URL, $this->url."?format=json");
        curl_setopt($this->ch,CURLOPT_POST, false);



        $result = curl_exec($this->ch);

        $http_status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        $this->assertEquals("200", $http_status);

        //$this->assertTrue(strTools::startsWith($result, '[{"name":"Banco_Testes","value":{"name":"Banco Testes",'));


        //close connection
        curl_close($this->ch);



        $this->tearDown();
    }

    function tearDown() {

    }

}








