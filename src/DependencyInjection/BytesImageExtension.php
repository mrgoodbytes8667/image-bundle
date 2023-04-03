<?php


namespace Bytes\ImageBundle\DependencyInjection;


use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class BytesImageExtension extends Extension implements ExtensionInterface
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $configuration = $this->getConfiguration($configs, $container);

        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('bytes_image.image');
        $definition->replaceArgument(1, $config['cache']['local']['success']['enable']);
        $definition->replaceArgument(2, $config['cache']['local']['success']['key']);
        $definition->replaceArgument(3, $config['cache']['local']['success']['duration']);
        $definition->replaceArgument(4, $config['cache']['local']['fallback']['enable']);
        $definition->replaceArgument(5, $config['cache']['local']['fallback']['key']);
        $definition->replaceArgument(6, $config['cache']['local']['fallback']['duration']);
        $definition->replaceArgument(7, $config['cache']['response']['success']['cached']['duration']);
        $definition->replaceArgument(8, $config['cache']['response']['success']['initial']['duration']);
        $definition->replaceArgument(9, $config['cache']['response']['fallback']['duration']);
    }
}
