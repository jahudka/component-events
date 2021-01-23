<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use Nette\Application\Application;
use Nette\Application\IPresenter;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use ReflectionClass;


class ComponentEventsExtension extends CompilerExtension {

    private const BRIDGES = [
        'doctrine' => Bridges\Doctrine\Bridge::class,
        'symfony' => Bridges\Symfony\Bridge::class,
        'nextras-orm' => Bridges\NextrasORM\Bridge::class,
    ];

    private array $defaults = [
        'doctrine' => null,
        'symfony' => null,
        'nextras-orm' => null,
    ];

    public function getConfigSchema() : Schema {
        return Expect::structure([
                'doctrine' => Expect::bool()->nullable(),
                'symfony' => Expect::bool()->nullable(),
                'nextras-orm' => Expect::bool()->nullable(),
            ])
            ->otherItems(Expect::string())
            ->required(false);
    }

    public function loadConfiguration() : void {
        Compat::aliasClasses();
    }

    public function beforeCompile() : void {
        $config = Compat::getConfig($this, $this->defaults);
        $builder = $this->getContainerBuilder();
        $bridges = $this->getBridges($config, $builder);
        $listeners = $this->analysePresenters($builder->findByType(IPresenter::class), $bridges);

        if ($listeners) {
            $relay = $builder->addDefinition($this->prefix('relay'));
            $relay->setType(Relay::class);

            /** @var ServiceDefinition $application */
            $applicationId = $builder->getByType(Application::class);
            $application = $builder->getDefinition($applicationId);
            $application->addSetup('$service->onPresenter[] = [?, "setPresenter"]', [$this->prefix('@relay')]);

            foreach ($listeners as $bridgeId => $bridgeListeners) {
                $relay->addSetup('add', [$bridges[$bridgeId]->createRelayFactory($bridgeListeners)]);
            }
        }
    }

    /**
     * @param Definition[] $presenters
     * @param IBridge[] $bridges
     */
    private function analysePresenters(array $presenters, array $bridges) : array {
        $analyser = $this->createAnalyser($bridges);
        $listeners = [];

        foreach ($presenters as $presenter) {
            $class = $presenter->getType();
            $rc = new ReflectionClass($class);

            foreach ($analyser->analysePresenter($rc) as $id => $components) {
                $listeners[$id][$class] = $components;
            }
        }

        return $listeners;
    }

    /**
     * @param IBridge[] $bridges
     */
    private function createAnalyser(array $bridges) : Analyser {
        $analyser = new Analyser();

        foreach ($bridges as $id => $bridge) {
            $analyser->add($bridge->createAnalyser(), $id);
        }

        return $analyser;
    }

    /**
     * @return IBridge[]
     */
    private function getBridges(object $config, ContainerBuilder $builder) : array {
        $bridges = [];

        foreach ($config as $id => $enabled) {
            $class = is_string($enabled) ? $enabled : self::BRIDGES[$id];
            /** @var IBridge $bridge */
            $bridge = new $class();

            if ($enabled ?? $bridge->detectPresence($builder)) {
                $bridges[$id] = $bridge;
            }
        }

        return $bridges;
    }
}
