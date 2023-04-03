<?php


namespace Bytes\ImageBundle\Event;


use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractImageLoadEvent extends Event
{
    /**
     * @var string|null
     */
    private ?string $url = null;
    
    /**
     * @var ResponseInterface|null
     */
    private ?ResponseInterface $response = null;

    /**
     * @param string $requestedUrl
     * @param ResponseInterface $response
     */
    public function __construct(private string $requestedUrl, ResponseInterface $response)
    {
        $this->setResponse($response);
    }

    /**
     * @param string $url
     * @param ResponseInterface $response
     * @return static
     */
    public static function create(string $url, ResponseInterface $response): static
    {
        return new static($url, $response);
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return AbstractImageLoadEvent
     */
    public function setUrl(?string $url): AbstractImageLoadEvent
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestedUrl(): string
    {
        return $this->requestedUrl;
    }

    /**
     * @param string $requestedUrl
     * @return AbstractImageLoadEvent
     */
    public function setRequestedUrl(string $requestedUrl): AbstractImageLoadEvent
    {
        $this->requestedUrl = $requestedUrl;
        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     * @return AbstractImageLoadEvent
     */
    public function setResponse(ResponseInterface $response): AbstractImageLoadEvent
    {
        $this->response = $response;
        return $this->setUrl($response->getInfo()['url']);
    }
}
