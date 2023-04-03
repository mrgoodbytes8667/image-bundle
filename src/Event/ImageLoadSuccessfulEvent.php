<?php

namespace Bytes\ImageBundle\Event;

class ImageLoadSuccessfulEvent extends AbstractImageLoadEvent
{
    /**
     * @return bool
     */
    public function isRedirect(): bool
    {
        return $this->getUrl() !== $this->getRequestedUrl();
    }
}
