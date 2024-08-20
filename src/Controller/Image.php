<?php

namespace Bytes\ImageBundle\Controller;

use Bytes\ImageBundle\Cache\ImageCache;
use Bytes\ResponseBundle\Enums\ContentType;
use DateInterval;
use Imagine\Imagick\Imagine;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Image
{
    private DateInterval $successExpiresAfter;

    private DateInterval $fallbackExpiresAfter;

    private HttpClientInterface $client;

    public function __construct(private readonly CacheItemPoolInterface $cache, private ImageCache $imageCache, private bool $useSuccessCache,
        int $successCacheDuration, private bool $useFallbackCache, int $fallbackCacheDuration, private int $responseSuccessCachedDuration,
        private int $responseSuccessInitialDuration, private int $responseFallbackDuration)
    {
        $successExpiresAfter = DateInterval::createFromDateString(sprintf('%d minutes', $successCacheDuration));
        if (!$successExpiresAfter) {
            $this->useSuccessCache = false;
        } else {
            $this->successExpiresAfter = $successExpiresAfter;
        }

        $fallbackExpiresAfter = DateInterval::createFromDateString(sprintf('%d minutes', $fallbackCacheDuration));
        if (!$fallbackExpiresAfter) {
            $this->useFallbackCache = false;
        } else {
            $this->fallbackExpiresAfter = $fallbackExpiresAfter;
        }

        $this->responseSuccessCachedDuration *= 60;
        $this->responseSuccessInitialDuration *= 60;
        $this->responseFallbackDuration *= 60;
    }

    /**
     * @param string|null $defaultUrl  Fallback/default url if $url does not resolve, ignored if data or defaultData is provided
     * @param string|null $defaultData Fallback/default data if $url does not resolve, ignored if data is provided
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public static function getImageAsPng(string $url, ?string $data = null, ?string $defaultUrl = null, ?string $defaultData = null, ?HttpClientInterface $client = null): Response
    {
        return static::getImageAs(ContentType::imagePng, $url, $data, defaultUrl: $defaultUrl, defaultData: $defaultData, client: $client);
    }

    /**
     * @param ContentType $contentType = [ContentType::imageJpg, ContentType::imagePng, ContentType::imageWebP][$any]
     * @param string|null $defaultUrl  Fallback/default url if $url does not resolve, ignored if data or defaultData is provided
     * @param string|null $defaultData Fallback/default data if $url does not resolve, ignored if data is provided
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public static function getImageAs(ContentType $contentType, string $url, ?string $data = null, ?string $defaultUrl = null, ?string $defaultData = null, ?HttpClientInterface $client = null, ?callable $responseCallback = null): Response
    {
        if (!$contentType->equals(ContentType::imageJpg, ContentType::imagePng, ContentType::imageGif, ContentType::imageWebP)) {
            throw new UnsupportedMediaTypeHttpException(sprintf('"%s" can only accept content types of jpeg, png, gif, or webp.', __FUNCTION__));
        }

        $fallback = false;
        if (empty($data)) {
            $client ??= HttpClient::create();
            $response = $client->request('GET', $url, ['timeout' => 5, 'max_duration' => 60]);
            if (static::isSuccess($response)) {
                $data = $response->getContent();
                if (is_null($responseCallback)) {
                    $responseCallable = function ($response) {
                        /* @var Response $response */
                        return $response->setPublic()
                            ->setMaxAge(15 * 60);
                    };
                }
            } else {
                $fallback = true;
                if (!empty($defaultUrl) && empty($defaultData)) {
                    $response = $client->request('GET', $defaultUrl, ['timeout' => 5, 'max_duration' => 60]);
                    $data = $response->getContent();
                } elseif (!empty($defaultData)) {
                    $data = $defaultData;
                } else {
                    // To simply throw the error
                    $data = $response->getContent();
                }
            }
        }

        $info = getimagesizefromstring($data);
        if (isset($info['mime']) && $info['mime'] === $contentType->value) {
            return self::createResponse($data, $contentType, $responseCallback);
        }

        ob_start();
        $imagine = new Imagine();

        $imagine->load($data)
            ->show($contentType->getExtension(), ['flatten' => false]);

        $image_data = ob_get_contents();
        ob_end_clean();

        return self::createResponse($image_data, $contentType, $responseCallback);
    }

    private static function isSuccess(ResponseInterface $response): bool
    {
        try {
            $code = $response->getStatusCode();

            return $code >= 200 && $code < 300;
        } catch (TransportExceptionInterface) {
            return false;
        }
    }

    private static function createResponse(?string $data, ContentType $contentType, ?callable $responseCallback): Response
    {
        $response = new Response($data,
            Response::HTTP_OK,
            ['content-type' => $contentType->value]);
        if (is_callable($responseCallback)) {
            $response = call_user_func($responseCallback, $response);
        }

        return $response;
    }

    /**
     * @param string|null $defaultUrl  Fallback/default url if $url does not resolve, ignored if data or defaultData is provided
     * @param string|null $defaultData Fallback/default data if $url does not resolve, ignored if data is provided
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public static function getImageAsWebP(string $url, ?string $data = null, ?string $defaultUrl = null, ?string $defaultData = null, ?HttpClientInterface $client = null): Response
    {
        return static::getImageAs(ContentType::imageWebP, $url, $data, defaultUrl: $defaultUrl, defaultData: $defaultData, client: $client);
    }

    /**
     * @return void
     */
    public function setClient(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param string|null $defaultUrl Fallback/default url if $url does not resolve
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getImageAsPngFromUrl(string $url, ?string $defaultUrl = null): Response
    {
        return $this->getImageAsFromUrl($url, ContentType::imagePng, defaultUrl: $defaultUrl);
    }

    /**
     * @param string|null $defaultUrl  Fallback/default url if $url does not resolve, ignored if data or defaultData is provided
     * @param string|null $defaultData Fallback/default data if $url does not resolve, ignored if data is provided
     * @param ContentType $contentType = [ContentType::imageJpg, ContentType::imagePng, ContentType::imageWebP][$any]
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getImageAsFromUrl(string $url, ContentType $contentType, ?string $defaultUrl = null, ?string $defaultData = null): Response
    {
        $responseCallable = null;
        $contentType ??= ContentType::imagePng;
        if (!$this->useSuccessCache) {
            return static::getImageAs($contentType, $url, defaultUrl: $defaultUrl, defaultData: $defaultData, client: $this->client);
        }

        try {
            $cacheKey = $this->imageCache->getImageAsFromUrlCacheKey(url: $url, contentType: $contentType);
            $item = $this->cache->getItem($cacheKey);
            /* @var callable|null $responseCallable */
            if ($item->isHit()) {
                $responseCallable = function ($response) {
                    /* @var Response $response */
                    return $response->setPublic()
                        ->setMaxAge($this->responseSuccessCachedDuration);
                };
            } else {
                $saveCacheItem = false; // Only save if the url is retrieved successfully, do not cache the default
                $response = $this->client->request('GET', $url, ['timeout' => 5, 'max_duration' => 60]);

                if (static::isSuccess($response)) {
                    $data = $response->getContent();
                    $saveCacheItem = true;
                    $responseCallable = function ($response) {
                        /* @var Response $response */
                        return $response->setPublic()
                            ->setMaxAge($this->responseSuccessInitialDuration);
                    };
                } else {
                    if (!empty($defaultUrl) && empty($defaultData)) {
                        $defaultCacheKey = $this->imageCache->getImageAsFromUrlFallbackCacheKey(url: $defaultUrl);
                        $defaultItem = $this->cache->getItem($defaultCacheKey);
                        if (!$defaultItem->isHit()) {
                            $response = $this->client->request('GET', $defaultUrl, ['timeout' => 5, 'max_duration' => 60]);
                            $data = $response->getContent();
                            // $defaultItem->expiresAfter($this->expiresAfter);
                            $defaultItem->expiresAfter($this->fallbackExpiresAfter);
                            $defaultItem->set($data);
                            if ($this->useFallbackCache) {
                                $this->cache->save($defaultItem);
                            }

                            $responseCallable = function ($response) {
                                /* @var Response $response */
                                return $response->setPublic()
                                    ->setMaxAge($this->responseFallbackDuration);
                            };
                        }

                        $data = $item->get();
                    } elseif (!empty($defaultData)) {
                        $data = $defaultData;
                    } else {
                        // To simply throw the error
                        $data = $response->getContent();
                    }
                }

                $item->expiresAfter($this->successExpiresAfter);
                $item->set($data);
                if ($saveCacheItem) {
                    $this->cache->save($item);
                }
            }

            $data = $item->get();

            return static::getImageAs($contentType, $url, $data, defaultUrl: $defaultUrl, defaultData: $defaultData, client: $this->client, responseCallback: $responseCallable);
        } catch (CacheException) {
            $this->useSuccessCache = false;

            return static::getImageAs($contentType, $url, defaultUrl: $defaultUrl, defaultData: $defaultData, client: $this->client, responseCallback: $responseCallable);
        }
    }

    /**
     * @param string|null $defaultUrl Fallback/default url if $url does not resolve
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getImageAsWebPFromUrl(string $url, ?string $defaultUrl = null): Response
    {
        return $this->getImageAsFromUrl($url, ContentType::imageWebP, defaultUrl: $defaultUrl);
    }
}
