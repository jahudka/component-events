<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\Doctrine;

use Doctrine\Common\EventManager;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\Statement;
use Jahudka\ComponentEvents\IBridge;
use Jahudka\ComponentEvents\IAnalyser;


class Bridge implements IBridge {

    public function detectPresence(ContainerBuilder $builder) : bool {
        return !empty($builder->findByType(EventManager::class));
    }

    public function createAnalyser() : IAnalyser {
        return new Analyser();
    }

    public function createRelayFactory(array $listeners) : Statement {
        return new Statement(
            Relay::class,
            [
                'eventMap' => $this->createEventMap($listeners),
            ]
        );
    }

    private function createEventMap(array $listenerMap) : array {
        $map = [];

        foreach ($listenerMap as $presenter => $components) {
            foreach ($components as $component => $events) {
                foreach ($events as $event) {
                    $map[$presenter][$event][] = $component;
                }
            }
        }

        return $map;
    }

}
