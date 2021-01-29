<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\Symfony;

use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\Statement;
use Jahudka\ComponentEvents\IBridge;
use Jahudka\ComponentEvents\IAnalyser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class Bridge implements IBridge {

    public function detectPresence(ContainerBuilder $builder) : bool {
        return !empty($builder->findByType(EventDispatcherInterface::class));
    }

    public function createAnalyser() : IAnalyser {
        return new Analyser();
    }

    public function createRelayFactory(array $listeners) : Statement {
        $eventMap = $this->createEventMap($listeners);

        return new Statement(
            Relay::class,
            [
                'eventMap' => $eventMap,
            ]
        );
    }

    private function createEventMap(array $listenerMap) : array {
        $eventMap = [];

        foreach ($listenerMap as $presenter => $components) {
            foreach ($components as $component => $events) {
                foreach ($events as $event => $listeners) {
                    foreach ($listeners as $listener) {
                        $eventMap[$presenter][$event][$listener[1] ?? 0][$component][] = $listener[0];
                    }
                }
            }
        }

        return $eventMap;
    }

}
