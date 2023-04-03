<?php


namespace Bytes\ImageBundle\Tests\DependencyInjection;


use Bytes\ImageBundle\Tests\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

/**
 *
 */
class DependencyInjectionTest extends TestCase
{

    /**
     * @var Kernel|null
     */
    protected $kernel;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     *
     */
    public function testFoundService()
    {
        $kernel = $this->kernel;
        $kernel->boot();

        $container = $kernel->getContainer();
        $dispatcher = $container->get('event_dispatcher');

        $this->assertNotNull($dispatcher);
    }

    /**
     *
     */
    public function testMissingService()
    {

        $this->expectException(ServiceNotFoundException::class);

        $kernel = $this->kernel;
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->get('router.default');
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->kernel = new Kernel();
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown(): void
    {
        if (is_null($this->fs)) {
            $this->fs = new Filesystem();
        }

        $this->fs->remove($this->kernel->getCacheDir());
        $this->kernel = null;
    }
}
