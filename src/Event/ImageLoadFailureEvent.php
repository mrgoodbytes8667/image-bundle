<?php

namespace Bytes\ImageBundle\Event;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ImageLoadFailureEvent extends AbstractImageLoadEvent
{
    /**
     * @return int
     * @throws TransportExceptionInterface
     */
    public function getStatusCode()
    {
        return $this->getResponse()->getStatusCode();
    }
}
