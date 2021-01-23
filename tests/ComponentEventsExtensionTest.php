<?php

namespace Jahudka\ComponentEvents\Tests;

use Doctrine\Common\EventManager;
use Jahudka\ComponentEvents\ComponentEventsExtension;
use Nette\Application\Application;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nextras\Orm\Bridges\NetteDI\OrmExtension;
use PHPUnit\Framework\TestCase;
use ComponentEventsTestContainer;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ComponentEventsExtensionTest extends TestCase {
    private string $tmpFile;

    public function setUp() : void {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'cev');

        $compiler = new Compiler();
        $compiler->loadConfig(__DIR__ . '/container.neon');

        $compiler->addExtension('nextras.orm', new OrmExtension());
        $compiler->addExtension('componentEvents', new ComponentEventsExtension());

        $compiler->setClassName(ComponentEventsTestContainer::class);
        file_put_contents($this->tmpFile, '<?php ' . $compiler->compile());
        require $this->tmpFile;
    }

    public function testComponentEventsExtension() : void {
        /** @var Container $container */
        $container = new ComponentEventsTestContainer();

        $application = $container->getByType(Application::class);
        $presenter = $container->getByType(Mocks\NonContainerPresenter::class);
        $application->onPresenter($application, $presenter);

        $container->getByType(EventDispatcher::class)->dispatch(new \stdClass(), 'kernel.response');
        $this->assertEquals(1, $presenter->handleResponseCalls);

        $presenter = $container->getByType(Mocks\ContainerPresenter::class);
        $application->onPresenter($application, $presenter);

        $this->assertCount(0, $presenter->getComponents());
        $container->getByType(EventManager::class)->dispatchEvent('postRemove');
        $this->assertCount(1, $presenter->getComponents());
        $this->assertEquals(1, $presenter->getComponent('componentWithChildren-child')->postRemoveCalls);

        $entity = new Mocks\NextrasORM\TestEntity();
        $container->getByType(Mocks\NextrasORM\TestRepository::class)->onAfterInsert($entity);
        $this->assertEquals(1, $presenter->getComponent('componentWithChildren')->onAfterInsertCalls);
    }

    public function tearDown() : void {
        unlink($this->tmpFile);
    }
}
