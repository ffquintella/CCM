<?php

namespace gcc\Tests;

use gcc\tools\HTTP_Accept;

require_once "../app/vars.php";
require_once ROOT . '/class/tools/HTTP_Accept.class.php';
//require_once 'PHPUnit/Framework.php';
 
class HTTP_AcceptTest extends \PHPUnit_Framework_TestCase
{
    public function testNoParamsNoExtensions()
    {
        $accept = new HTTP_Accept('text/plain;q=0.5,text/html,text/x-c;q=0.9');

        $this->assertEquals(0.5, $accept->getQuality('text/plain'));
        $this->assertEquals(1, $accept->getQuality('text/html'));
        $this->assertEquals(0.9, $accept->getQuality('text/x-c'));
        $this->assertEquals(0, $accept->getQuality('text/x-c++'));
        $this->assertTrue($accept->isMatchExact('text/plain'));
        $this->assertTrue($accept->isMatchExact('text/html'));
        $this->assertTrue($accept->isMatchExact('text/x-c'));
        $this->assertFalse($accept->isMatchExact('text/x-c++'));
    }
 
    public function testParamsNoExtensions()
    {
        $accept = new HTTP_Accept('text/html;level=1;q=0.5,'.
                'text/html;level=4;awesome=yes;q=0.9,text/html');

        $this->assertEquals(0.5,
                $accept->getQuality('text/html', array('level' => '1')));
        $this->assertEquals(0.5,
                $accept->getQuality('text/html', array('level' => 1)));
        $this->assertEquals(1,
                $accept->getQuality('text/html', array('awesome' => 'yes')));
        $this->assertEquals(1,
                $accept->getQuality('text/html', array('level' => 4)));
        $this->assertEquals(0.9,
                $accept->getQuality('text/html',
                    array('level' => 4, 'awesome' => 'yes')));
        $this->assertEquals(1, $accept->getQuality('text/html', array()));

        $this->assertTrue($accept->isMatchExact('text/html',
                    array('level' => 4, 'awesome' => 'yes')));
        $this->assertTrue($accept->isMatchExact('text/html',
                    array('level' => 1)));
        $this->assertFalse($accept->isMatchExact('text/html',
                    array('level' => 4)));
        $this->assertFalse($accept->isMatchExact('text/html',
                    array('awesome' => 'yes')));
    }

    public function testExtensionsNoParams()
    {
        $accept = new HTTP_Accept('text/html;q=0.5;up=down,'.
                'text/html;q=0.9;up=down;meaning=42,text/html;q=0.3;blue,'.
                'text/html;q=0.1');

        $this->assertEquals(0.5,
                $accept->getQuality('text/html', array(),
                    array('up' => 'down')));
        $this->assertEquals(0.9,
                $accept->getQuality('text/html', array(),
                    array('up' => 'down', 'meaning' => 42)));
        $this->assertEquals(0.9,
                $accept->getQuality('text/html', array(),
                    array('up' => 'down', 'meaning' => '42')));
        $this->assertEquals(0.3,
                $accept->getQuality('text/html', array(),
                    array('blue' => null)));
        $this->assertEquals(0.1,
                $accept->getQuality('text/html', array(),
                    array('blue' => true)));
        $this->assertEquals(0.1,
                $accept->getQuality('text/html', array(), array()));

        $this->assertTrue($accept->isMatchExact('text/html', array(),
                    array('up' => 'down')));
        $this->assertTrue($accept->isMatchExact('text/html', array(),
                    array('up' => 'down', 'meaning' => 42)));
        $this->assertTrue($accept->isMatchExact('text/html', array(),
                    array('up' => 'down', 'meaning' => '42')));
        $this->assertTrue($accept->isMatchExact('text/html', array(),
                    array('blue' => null)));
        $this->assertFalse($accept->isMatchExact('text/html', array(),
                    array('blue' => true)));
        $this->assertTrue($accept->isMatchExact('text/html', array(),
                    array()));
    }

    public function testExtensionsAndParams()
    {
        $accept = new HTTP_Accept('text/html;level=1;q=0.5;up=down,'.
                'text/html;level=2;q=0.6;up=down,'.
                'text/html;level=2;q=0.7;up=down;blue,text/html;q=0.1');

        $this->assertEquals(0.5, $accept->getQuality('text/html',
                    array('level' => 1), array('up' => 'down')));
        $this->assertEquals(0.6, $accept->getQuality('text/html',
                    array('level' => 2), array('up' => 'down')));
        $this->assertEquals(0.7, $accept->getQuality('text/html',
                    array('level' => 2),
                    array('up' => 'down', 'blue' => null)));
        $this->assertEquals(0.1, $accept->getQuality('text/html',
                    array(), array('up' => 'down')));
        $this->assertEquals(0.1, $accept->getQuality('text/html',
                    array('level' => 1), array()));
        $this->assertEquals(0.1, $accept->getQuality('text/html',
                    array('level' => 2), array()));

        $this->assertTrue($accept->isMatchExact('text/html',
                    array('level' => 1), array('up' => 'down')));
        $this->assertTrue($accept->isMatchExact('text/html',
                    array('level' => 2), array('up' => 'down')));
        $this->assertTrue($accept->isMatchExact('text/html',
                    array('level' => 2),
                    array('up' => 'down', 'blue' => null)));
        $this->assertFalse($accept->isMatchExact('text/html',
                    array(), array('up' => 'down')));
        $this->assertFalse($accept->isMatchExact('text/html',
                    array('level' => 1), array()));
        $this->assertFalse($accept->isMatchExact('text/html',
                    array('level' => 2), array()));
    }

    public function testWildcards()
    {
        $accept = new HTTP_Accept('text/html,text/*;q=0.4,'.
                'text/*;charset=utf-8;q=0.5,*/*;q=0.1');

        $this->assertEquals(1, $accept->getQuality('text/html'));
        $this->assertEquals(0.4, $accept->getQuality('text/plain'));
        $this->assertEquals(0.5, $accept->getQuality('text/plain',
                    array('charset' => 'utf-8')));
        $this->assertEquals(1, $accept->getQuality('text/html',
                    array('charset' => 'utf-8')));
        $this->assertEquals(0.1, $accept->getQuality('image/jpeg'));
        $this->assertEquals(0.4, $accept->getQuality('text/*'));

        $this->assertTrue($accept->isMatchExact('text/html'));
        $this->assertFalse($accept->isMatchExact('text/plain'));
        $this->assertFalse($accept->isMatchExact('text/plain',
                    array('charset' => 'utf-8')));
        $this->assertFalse($accept->isMatchExact('text/html',
                    array('charset' => 'utf-8')));
        $this->assertFalse($accept->isMatchExact('image/jpeg'));
        $this->assertTrue($accept->isMatchExact('text/*'));
    }

    public function testQstrings()
    {
        $accept = new HTTP_Accept(
                'text/html;string="Really great stuff!\\\\";q=0.8,'.
                'text/html;string="\"Best stuff ever!\"";q=0.9,'.
                'text/html;q=0.1;string="\"kinda good\""');

        $this->assertEquals(0.8, $accept->getQuality('text/html',
                array('string' => 'Really great stuff!\\')));
        $this->assertEquals(0.9, $accept->getQuality('text/html',
                array('string' => '"Best stuff ever!"')));
        $this->assertEquals(0.1, $accept->getQuality('text/html',
                array(), array('string' => '"kinda good"')));

        $this->assertTrue($accept->isMatchExact('text/html',
                array('string' => 'Really great stuff!\\')));
        $this->assertTrue($accept->isMatchExact('text/html',
                array('string' => '"Best stuff ever!"')));
        $this->assertTrue($accept->isMatchExact('text/html',
                array(), array('string' => '"kinda good"')));
    }

    public function testMalformed()
    {
        // The behavior of these tests is not specified anywhere
        // these tests are mostly to show what the current implementation does
        $accept = new HTTP_Accept('*/html;q=0.5,text/html;q=2.0,'.
                    'text/plain;q=0.12345');

        $this->assertEquals(0, $accept->getQuality('*/html'));
        $this->assertEquals(0, $accept->getQuality('application/html'));
        $this->assertEquals(1, $accept->getQuality('text/html'));
        $this->assertEquals(0.12345, $accept->getQuality('text/plain'));

        $this->assertFalse($accept->isMatchExact('*/html'));
    }

    public function testBestMatching()
    {
        $accept = new HTTP_Accept('text/html;q=0.5,text/html;level=4;q=0.6,'.
                'text/html;level=4;q=0.7;foo=1');

        $this->assertEquals(0.5,
                $accept->getQuality('text/html', array('level' => 2)));
        $this->assertEquals(0.5,
                $accept->getQuality('text/html', array(), array('qux' => 1)));
        $this->assertEquals(0.5,
                $accept->getQuality('text/html', array(), array('foo' => 1)));
        $this->assertEquals(0.6,
                $accept->getQuality('text/html',
                    array('level' => 4, 'baz' => 1)));
        $this->assertEquals(0.6,
                $accept->getQuality('text/html', array('level' => 4),
                    array('qux' => 1)));
        $this->assertEquals(0.7,
                $accept->getQuality('text/html',
                    array('level' => 4, 'baz' => 1), array('foo' => 1)));
        $this->assertEquals(0.7,
                $accept->getQuality('text/html', array('level' => 4),
                    array('foo' => 1, 'qux' => 1)));

        $this->assertFalse($accept->isMatchExact('text/html',
                    array('level' => 2)));
        $this->assertFalse($accept->isMatchExact('text/html', array(),
                    array('qux' => 1)));
        $this->assertFalse($accept->isMatchExact('text/html', array(),
                    array('foo' => 1)));
        $this->assertFalse($accept->isMatchExact('text/html',
                    array('level' => 4, 'baz' => 1)));
        $this->assertFalse($accept->isMatchExact('text/html',
                    array('level' => 4), array('qux' => 1)));
        $this->assertFalse($accept->isMatchExact('text/html',
                    array('level' => 4, 'baz' => 1), array('foo' => 1)));
        $this->assertFalse($accept->isMatchExact('text/html',
                    array('level' => 4), array('foo' => 1, 'qux' => 1)));
    }

    public function testGetTypes()
    {
        $accept = new HTTP_Accept('text/plain;q=0.5,text/html;level=4;q=0.6,'.
                'text/x-c;q=0.4;c=1,image/gif;q=0.1');

        $this->assertEquals(
                array('text/html', 'text/plain', 'text/x-c', 'image/gif'),
                $accept->getTypes());
    }

    public function testGetParameterSets()
    {
        $accept = new HTTP_Accept('text/plain;q=1;d=1,text/html;q=0.5,'.
                'text/html;level=4;q=0.6,text/html;level=4;q=0.4;c=1,'.
                'text/html;level=2;q=0.1');

        $this->assertEquals(array(), $accept->getParameterSets('text/plain'));
        $this->assertEquals(
                array(array('level' => 4), array(), array('level' => 2)),
                $accept->getParameterSets('text/html'));
    }

    public function testGetExtensionSets()
    {
        $accept = new HTTP_Accept('text/plain;p=1;q=1,text/html;q=0.5,'.
                'text/html;q=0.6;foo=4,text/html;level=4;q=0.4;foo=4,'.
                'text/html;q=0.1;bar=1');

        $this->assertEquals(array(), $accept->getExtensionSets('text/plain'));
        $this->assertEquals(
                array(array('foo' => 4), array(), array('bar' => 1)),
                $accept->getExtensionSets('text/html'));
    }

    public function testAddRemoveType()
    {
        $accept = new HTTP_Accept('text/html;level=1;q=0.5,text/html;q=0.9');


        $this->assertEquals(0, $accept->getQuality('text/plain'));
        $accept->addType('text/plain');
        $this->assertEquals(1, $accept->getQuality('text/plain'));


        $this->assertEquals(1,
                $accept->getQuality('text/plain', array('p' => 1)));
        $accept->addType('text/plain', 0.5, array('p' => 1));
        $this->assertEquals(0.5,
                $accept->getQuality('text/plain', array('p' => 1)));


        $this->assertEquals(0.5,
                $accept->getQuality('text/plain', array('p' => 1),
                    array('e' => 2)));
        $accept->addType('text/plain', 0.4, array('p' => 1), array('e' => 2));
        $this->assertEquals(0.4,
                $accept->getQuality('text/plain', array('p' => 1),
                    array('e' => 2)));
        $accept->removeType('text/plain', array('p' => 1), array('e' => 2));
        $this->assertEquals(0.5,
                $accept->getQuality('text/plain', array('p' => 1),
                    array('e' => 2)));


        $this->assertEquals(0.9, $accept->getQuality('text/html'));
        $accept->addType('text/html');
        $this->assertEquals(1, $accept->getQuality('text/html'));
        $accept->addType('text/html', 0.5);
        $this->assertEquals(0.5, $accept->getQuality('text/html'));
        $accept->removeType('text/html');
        $this->assertEquals(0, $accept->getQuality('text/html'));
        $accept->removeType('text/html');
        $this->assertEquals(0, $accept->getQuality('text/html'));
    }

    public function testToString()
    {
        $accept = new HTTP_Accept('text/html;level=1;q=0.5,'.
                'text/html;q=0.9,'.
                'text/html;q=0.4;foo=1,'.
                'text/plain;p=1;q=0.95;e=2,'.
                'image/png;q=1,'.
                'text/plain;q=0.7;foo="greetings \"hue\""');

        $this->assertEquals(
                'image/png,text/plain;p=1;q=0.95;e=2,'.
                'text/html;q=0.9,'.
                'text/plain;q=0.7;foo="greetings \"hue\"",'.
                'text/html;level=1;q=0.5,'.
                'text/html;q=0.4;foo=1',
                $accept->__toString());
    }
}

// vim: set ts=4 sts=4 sw=4 et:
?>
