<?php

namespace Bytes\ImageBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractImageLoadEvent extends Event
{
    private ?string $url = null;

    private ?ResponseInterface $response = null;

    public function __construct(private string $requestedUrl, ResponseInterface $response)
    {
        $this->setResponse($response);
    }

    public static function create(string $url, ResponseInterface $response): static
    {
        return new static($url, $response);
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): AbstractImageLoadEvent
    {
        $this->url = $url;

        return $this;
    }

    public function getRequestedUrl(): string
    {
        return $this->requestedUrl;
    }

    public function setRequestedUrl(string $requestedUrl): AbstractImageLoadEvent
    {
        $this->requestedUrl = $requestedUrl;

        return $this;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): AbstractImageLoadEvent
    {
        $this->response = $response;

        return $this->setUrl($response->getInfo()['url']);
    }
}
