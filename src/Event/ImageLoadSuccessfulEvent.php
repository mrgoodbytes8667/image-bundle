<?php

namespace Bytes\ImageBundle\Event;

class ImageLoadSuccessfulEvent extends AbstractImageLoadEvent
{
    public function isRedirect(): bool
    {
        return $this->getUrl() !== $this->getRequestedUrl();
    }
}
