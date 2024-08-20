<?php

namespace Bytes\ImageBundle\Tests;

use Bytes\ImageBundle\BytesImageBundle;
use Exception;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class Kernel extends BaseKernel
{
    protected string $callback;

    protected array $config;

    /**
     * @var array
     */
    private $classes = [];

    /**
     * Kernel constructor.
     */
    public function __construct(string $callback = '', array $config = [])
    {
        $this->callback = $callback;
        $this->config = $config;

        parent::__construct('test', true);
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new BytesImageBundle(),
        ];
    }

    /**
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->register('router.request_context', RequestContext::class);
            $container->register('router.default', UrlGeneratorInterface::class);
            $container->register('router', UrlGeneratorInterface::class);

            foreach ($this->classes as $class) {
                if (is_array($class)) {
                    if (array_key_exists('id', $class) && array_key_exists('class', $class)) {
                        $container->register($class['id'], $class['class']);
                    } else {
                        $container->register($class[0], $class[1]);
                    }
                } else {
                    $container->register($class);
                }
            }

            $container
                ->loadFromExtension('framework', [
                    'secret' => 'abc123',
                    'http_method_override' => false,
                ])
                ->loadFromExtension('bytes_image', $this->config);
        });
    }

    public function hasCallback(): bool
    {
        return !empty($this->callback);
    }

    /**
     * Gets the cache directory.
     *
     * Since Symfony 5.2, the cache directory should be used for caches that are written at runtime.
     * For caches and artifacts that can be warmed at compile-time and deployed as read-only,
     * use the new "build directory" returned by the {@see getBuildDir()} method.
     *
     * @return string The cache directory
     */
    public function getCacheDir(): string
    {
        return parent::getCacheDir().'/'.spl_object_hash($this);
    }

    public function getCallback(): string
    {
        return $this->callback;
    }

    /**
     * @return $this
     */
    public function setCallback(string $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return $this
     */
    public function setConfig(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return $this
     */
    public function mergeConfig(array $config): static
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @return $this
     */
    public function setClasses(array $classes): static
    {
        $this->classes = $classes;

        return $this;
    }

    /**
     * @param string|array $class
     *
     * @return $this
     */
    public function addClass($class): static
    {
        $this->classes[] = $class;

        return $this;
    }
}
