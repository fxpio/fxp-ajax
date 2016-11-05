<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Ajax\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Sonatra\Component\Ajax\AjaxEvents;
use Sonatra\Component\Ajax\Event\GetAjaxEvent;

/**
 * Ajax event subscriber.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxSubscriber implements EventSubscriberInterface
{
    /**
     * @var GetAjaxEvent[]
     */
    private $ajaxEvents = array();

    /**
     * @var string
     */
    private $ajaxId;

    /**
     * Constructor.
     *
     * @param string $ajaxIdParameter
     */
    public function __construct($ajaxIdParameter)
    {
        $this->ajaxId = $ajaxIdParameter;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
                AjaxEvents::INJECTION => array('onAjaxInjection', 0),
                KernelEvents::RESPONSE => array('onKernelResponse', 0),
        );
    }

    /**
     * Add Ajax event in queue.
     *
     * @param GetAjaxEvent $event
     */
    public function onAjaxInjection(GetAjaxEvent $event)
    {
        $this->ajaxEvents[$event->getId()] = $event;
    }

    /**
     * If the request is ajax, this method find the ajax event corresponding
     * with the ajax id request parameter.
     * She replace the response by the ajax response with the correct format.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $id = $request->get($this->ajaxId);

        if (!$request->isXmlHttpRequest() || null === $id) {
            return;
        }

        if (!isset($this->ajaxEvents[$id])) {
            $event->setResponse(new Response());

            return;
        }

        $ajaxEvent = $this->ajaxEvents[$id];

        $request->setRequestFormat($ajaxEvent->getFormat());
        $event->setResponse($ajaxEvent->generateResponse());
        $event->stopPropagation();
    }
}
