<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use Nette\Application\Application;
use Nette\Application\IPresenter;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\ServiceDefinition;


class ComponentEventsExtension extends CompilerExtension {

    private const BRIDGES = [
        'doctrine' => Bridges\Doctrine\Bridge::class,
        'symfony' => Bridges\Symfony\Bridge::class,
    ];

    private $defaults = [
        'doctrine' => null,
        'symfony' => null,
    ];

    public function beforeCompile() : void {
        $config = $this->getConfig() + $this->defaults;
        $builder = $this->getContainerBuilder();
        $bridges = $this->getBridges($config, $builder);
        $listeners = $this->analysePresenters($builder->findByType(IPresenter::class), $bridges);
        $setType = method_exists(ServiceDefinition::class, 'setType') ? 'setType' : 'setClass';

        if ($listeners) {
            $relay = $builder->addDefinition($this->prefix('relay'));
            $relay->$setType(Relay::class);

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
     * @return array
     */
    private function analysePresenters(array $presenters, array $bridges) : array {
        $analyser = $this->createAnalyser($bridges);
        $listeners = [];

        foreach ($presenters as $presenter) {
            $class = $presenter->getType();
            $rc = new \ReflectionClass($class);

            foreach ($analyser->analysePresenter($rc) as $id => $components) {
                $listeners[$id][$class] = $components;
            }
        }

        return $listeners;
    }

    /**
     * @param IBridge[] $bridges
     * @return Analyser
     */
    private function createAnalyser(array $bridges) : Analyser {
        $analyser = new Analyser();

        foreach ($bridges as $id => $bridge) {
            $analyser->add($bridge->createAnalyser(), $id);
        }

        return $analyser;
    }

    /**
     * @param array $config
     * @param ContainerBuilder $builder
     * @return IBridge[]
     */
    private function getBridges(array $config, ContainerBuilder $builder) : array {
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
