<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Ajax\Tests\Event;

use Sonatra\Component\Ajax\AjaxEvents;
use Sonatra\Component\Ajax\Event\GetAjaxEvent;
use Sonatra\Component\Ajax\EventSubscriber\AjaxSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Tests case for ajax subscriber.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var AjaxSubscriber
     */
    protected $subscriber;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $kernel;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->subscriber = new AjaxSubscriber('foobar');
        $this->dispatcher->addSubscriber($this->subscriber);
        $this->kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->subscriber = null;
        $this->kernel = null;
    }

    public function testGetSubscribeEvents()
    {
        $events = $this->subscriber->getSubscribedEvents();

        $this->assertCount(2, $events);
        $this->assertArrayHasKey(AjaxEvents::INJECTION, $events);
        $this->assertArrayHasKey(KernelEvents::RESPONSE, $events);
    }

    public function testClassicHttpRequest()
    {
        /* @var HttpKernelInterface $kernel */
        $kernel = $this->kernel;
        $request = new Request();
        $request->query->set('foobar', '42');

        $this->assertFalse($request->isXmlHttpRequest());

        $response = new Response('foo');
        $event = new FilterResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST, $response);
        $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

        $this->assertNull($event->getResponse()->headers->get('Content-Type'));
        $this->assertEquals('foo', $event->getResponse()->getContent());
    }

    public function testXmlHttpRequestWithEmptyAjaxId()
    {
        /* @var HttpKernelInterface $kernel */
        $kernel = $this->kernel;
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->assertTrue($request->isXmlHttpRequest());

        $response = new Response('foo');
        $event = new FilterResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST, $response);
        $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

        $this->assertNull($event->getResponse()->headers->get('Content-Type'));
        $this->assertEquals('foo', $event->getResponse()->getContent());
    }

    public function testXmlHttpRequestWithAjaxIdAndNotAjaxEvent()
    {
        /* @var HttpKernelInterface $kernel */
        $kernel = $this->kernel;
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->query->set('foobar', '42');

        $this->assertTrue($request->isXmlHttpRequest());

        $response = new Response('foo');
        $event = new FilterResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST, $response);
        $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

        $this->assertNull($event->getResponse()->headers->get('Content-Type'));
        $this->assertNotEquals('foo', $event->getResponse()->getContent());
        $this->assertEquals('', $event->getResponse()->getContent());
    }

    public function testXmlHttpRequestWithAjaxIdAndAjaxEvent()
    {
        $ajaxEvent = new GetAjaxEvent('42', 'json');
        $ajaxEvent->setData(array('number' => '42'));
        $this->dispatcher->dispatch(AjaxEvents::INJECTION, $ajaxEvent);

        /* @var HttpKernelInterface $kernel */
        $kernel = $this->kernel;
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->query->set('foobar', '42');

        $this->assertTrue($request->isXmlHttpRequest());

        $response = new Response('foo');
        $event = new FilterResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST, $response);
        $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

        $this->assertEquals('application/json', $event->getResponse()->headers->get('Content-Type'));
        $this->assertNotEquals('foo', $event->getResponse()->getContent());
        $this->assertEquals('{"number":"42"}', $event->getResponse()->getContent());
    }
}
