<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Ajax\EventSubscriber;

use Fxp\Component\Ajax\AjaxEvents;
use Fxp\Component\Ajax\Event\GetAjaxEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Ajax event subscriber.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AjaxSubscriber implements EventSubscriberInterface
{
    /**
     * @var GetAjaxEvent[]
     */
    private $ajaxEvents = [];

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
        return [
                AjaxEvents::INJECTION => ['onAjaxInjection', 0],
                KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
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
