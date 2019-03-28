<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 02/03/14
 * Time: 18:10
 */

namespace ccm\Tests\ws;

use ccm\tools\strTools;

require_once __DIR__."/../../app/vars.php";

require_once ROOT."/class/logFactory.class.php";
require_once ROOT."/class/tools/strTools.class.php";

class authenticationLoginTest extends \PHPUnit\Framework\TestCase {

    private $ch, $url;

    function setUp(): void  {
        //open connection

        $this->url = WS_TEST_URL.'authenticationLogin';
        //?XDEBUG_SESSION_START=gubddev
    }

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
        curl_setopt($this->ch,CURLOPT_URL, $this->url);
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

        $fields = array(
            'username' => 'Stestes',
            'password' => 'testee',
        );

        $fields_string = "";
        //url-ify the data for the POST
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        curl_setopt($this->ch,CURLOPT_POST, count($fields));
        curl_setopt($this->ch,CURLOPT_POSTFIELDS, $fields_string);

        $result = curl_exec($this->ch);
        $this->assertFalse(strTools::startsWith($result, '{"userName":"Stestes","tokenType":"system","tokenValue":'));


        //close connection
        //curl_close($this->ch);

        $fields = array(
            'username' => 'utestes',
            'password' => 'teste',
        );

        $fields_string = "";
        //url-ify the data for the POST
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        curl_setopt($this->ch,CURLOPT_POST, count($fields));
        curl_setopt($this->ch,CURLOPT_POSTFIELDS, $fields_string);

        $result = curl_exec($this->ch);

        $http_status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $this->assertEquals("200", $http_status);

        $this->assertFalse(strTools::startsWith($result, '{"userName":"Stestes","tokenType":"system","tokenValue":'));

        $fields = array(
            'username' => 'Uteste',
            'password' => 'teste',
        );

        $fields_string = "";
        //url-ify the data for the POST
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        curl_setopt($this->ch,CURLOPT_POST, count($fields));
        curl_setopt($this->ch,CURLOPT_POSTFIELDS, $fields_string);

        $result = curl_exec($this->ch);

        $http_status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $this->assertEquals("401", $http_status);

        $fields = array(
            'username' => 'utestes',
            'password' => 'testew',
        );

        $fields_string = "";
        //url-ify the data for the POST
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        curl_setopt($this->ch,CURLOPT_POST, count($fields));
        curl_setopt($this->ch,CURLOPT_POSTFIELDS, $fields_string);

        $result = curl_exec($this->ch);

        $http_status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $this->assertEquals("401", $http_status);

        //close connection
        curl_close($this->ch);



        $this->tearDown();
    }

    function tearDown(): void {

    }

}








