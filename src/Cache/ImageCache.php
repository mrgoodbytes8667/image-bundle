<?php

namespace Bytes\ImageBundle\Cache;

use Bytes\ResponseBundle\Enums\ContentType;

use function Symfony\Component\String\u;

class ImageCache
{
    public function __construct(private readonly string $successCachePrefix, private readonly string $fallbackCachePrefix)
    {
    }

    public function getImageAsFromUrlCacheKey(string $url, ContentType $contentType): string
    {
        return u($this->successCachePrefix)->append('.getImageAsFromUrl.', urlencode($url), urlencode($contentType->value), '.contents');
    }

    public function getImageAsFromUrlFallbackCacheKey(string $url): string
    {
        return u($this->fallbackCachePrefix)->append('.getImageAsFromUrl.')->append(urlencode($url))->append('.contents');
    }
}
