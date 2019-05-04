<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\Doctrine;

use Doctrine\Common\EventSubscriber;
use Jahudka\ComponentEvents\IAnalyser;


class Analyser implements IAnalyser {

    public function analyse(\ReflectionClass $component) : ?array {
        if (!$component->implementsInterface(EventSubscriber::class)) {
            return null;
        }

        try {
            /** @var EventSubscriber $instance */
            $instance = $component->newInstanceWithoutConstructor();
            return $instance->getSubscribedEvents();
        } catch (\Throwable $e) {
            return null;
        }
    }

}
