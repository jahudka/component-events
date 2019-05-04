<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\Symfony;

use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\Statement;
use Jahudka\ComponentEvents\IBridge;
use Jahudka\ComponentEvents\IAnalyser;
use Symfony\Component\EventDispatcher\EventDispatcher;


class Bridge implements IBridge {

    public function detectPresence(ContainerBuilder $builder) : bool {
        return !empty($builder->findByType(EventDispatcher::class));
    }

    public function createAnalyser() : IAnalyser {
        return new Analyser();
    }

    public function createRelayFactory(array $listeners) : Statement {
        [$eventMap, $idMap] = $this->createEventMaps($listeners);

        return new Statement(
            Relay::class,
            [
                'eventMap' => $eventMap,
                'idMap' => $idMap,
            ]
        );
    }

    private function createEventMaps(array $listenerMap) : array {
        $eventMap = [];
        $idMap = [];

        foreach ($listenerMap as $presenter => $components) {
            foreach ($components as $component => $eventMap) {
                foreach ($eventMap as $event => $listeners) {
                    foreach ($listeners as $listener) {
                        $eventMap[$event][$listener[1] ?? 0][$presenter][$component][] = $listener[0];
                        $idMap[] = [$event, $listener[1] ?? 0];
                    }
                }
            }
        }

        return [$eventMap, $idMap];
    }

}
