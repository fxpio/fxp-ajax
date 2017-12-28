<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Ajax\Tests\Event;

use Fxp\Component\Ajax\Event\GetAjaxEvent;
use PHPUnit\Framework\TestCase;

/**
 * Tests case for ajax event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class GetAjaxEventTest extends TestCase
{
    public function testDefaultConstructor()
    {
        $event = new GetAjaxEvent('foobar');

        $this->assertEquals('foobar', $event->getId());
        $this->assertEquals('json', $event->getFormat());
        $this->assertNull($event->getData());

        $this->assertEquals($event, $event->setId('barfoo'));
        $this->assertEquals($event, $event->setFormat('xml'));

        $this->assertEquals('barfoo', $event->getId());
        $this->assertEquals('xml', $event->getFormat());
    }

    /**
     * @expectedException \Fxp\Component\Ajax\Exception\InvalidArgumentException
     */
    public function testInvalidFormat()
    {
        new GetAjaxEvent('foobar', 'format');
    }

    public function testCustomConstructor()
    {
        $event = new GetAjaxEvent('foobar', 'xml');

        $this->assertEquals('foobar', $event->getId());
        $this->assertEquals('xml', $event->getFormat());
        $this->assertNull($event->getData());

        $this->assertEquals($event, $event->setData('number 42'));

        $this->assertEquals('number 42', $event->getData());
    }

    public function testGenerateClosureDataResponse()
    {
        $data = function () {
            return ['number' => '42'];
        };

        $event = new GetAjaxEvent('foobar', 'json');
        $event->setData($data);
        $response = $event->generateResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals('{"number":"42"}', $response->getContent());
    }

    public function testGenerateJsonResponse()
    {
        $event = new GetAjaxEvent('foobar', 'json');
        $event->setData(['number' => '42']);
        $response = $event->generateResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals('{"number":"42"}', $response->getContent());
    }

    public function testGenerateXmlResponse()
    {
        $event = new GetAjaxEvent('foobar', 'xml');
        $event->setData(['number' => '42']);
        $response = $event->generateResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals('application/xml', $response->headers->get('Content-Type'));
        $this->assertEquals("<?xml version=\"1.0\"?>\n<response><number>42</number></response>\n", $response->getContent());
    }
}
