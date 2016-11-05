<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Ajax\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Sonatra\Component\Ajax\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Ajax Event dispatched.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GetAjaxEvent extends Event
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var mixed|\Closure
     */
    protected $data;

    /**
     * @var string
     */
    protected $format;

    /**
     * @var array
     */
    protected $formats = array('xml', 'json');

    /**
     * Constructor.
     *
     * @param string $id
     * @param string $format
     */
    public function __construct($id, $format = 'json')
    {
        $this->setId($id);
        $this->setFormat($format);
    }

    /**
     * Set ajax event unique id.
     *
     * @param string $id
     *
     * @return GetAjaxEvent
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get ajax event unique id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set request format.
     *
     * @param string $format
     *
     * @return GetAjaxEvent
     *
     * @throws InvalidArgumentException When the format is not allowed
     */
    public function setFormat($format)
    {
        if (!in_array($format, $this->formats)) {
            $msg = "The '%s' format is not allowed. Try with '%s'";
            throw new InvalidArgumentException(sprintf($msg, $format, implode("', '", $this->formats)));
        }

        $this->format = $format;

        return $this;
    }

    /**
     * Get request format.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set data.
     *
     * @param mixed $data
     *
     * @return GetAjaxEvent
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Generate the ajax response.
     *
     * @return Response
     */
    public function generateResponse()
    {
        $data = $this->getData();

        if ($data instanceof \Closure) {
            $data = $data();
        }

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/'.$this->getFormat());
        $response->setContent($serializer->serialize($data, $this->getFormat()));

        return $response;
    }
}
