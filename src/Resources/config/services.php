<?php


namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bytes\ImageBundle\Controller\Image;

/**
 * @param ContainerConfigurator $container
 */
return static function (ContainerConfigurator $container) {

    $services = $container->services();

    //region Imaging
    $services->set('bytes_image.image', Image::class)
        ->args([
            service('cache.app'),
            true,
            '',
            0,
            true,
            '',
            0,
            0,
            0,
            0,
            service('event_dispatcher')
        ])
        ->call('setClient', [service('http_client')])
        ->lazy()
        ->alias(Image::class, 'bytes_image.image')
        ->public();
    //endregion
};
