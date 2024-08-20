<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bytes\ImageBundle\Cache\ImageCache;
use Bytes\ImageBundle\Controller\Image;

/*
 * @param ContainerConfigurator $container
 */
return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // region Imaging
    $services->set('bytes_image.image', Image::class)
        ->args([
            service('cache.app'),                // 0 => CacheItemPoolInterface $cache
            service('bytes_image.image.cache'),  // 1 => ImageCache $imageCache
            true,                                // 2 => bool $useSuccessCache
            0,                                   // 3 => int $successCacheDuration
            true,                                // 4 => bool $useFallbackCache
            0,                                   // 5 => int $fallbackCacheDuration
            0,                                   // 6 => int $responseSuccessCachedDuration
            0,                                   // 7 => int $responseSuccessInitialDuration
            0,                                   // 8 => int $responseFallbackDuration
            service('event_dispatcher'),
        ])
        ->call('setClient', [service('http_client')])
        ->lazy()
        ->alias(Image::class, 'bytes_image.image')
        ->public();

    $services->set('bytes_image.image.cache', ImageCache::class)
        ->args([
            '',
            '',
        ])
        ->lazy()
        ->alias(ImageCache::class, 'bytes_image.image.cache')
        ->public();
    // endregion
};
