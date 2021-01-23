<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\Symfony;

use Jahudka\ComponentEvents\IAnalyser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ReflectionClass;


class Analyser implements IAnalyser {

    public function analyse(ReflectionClass $component) : ?array {
        if (!$component->implementsInterface(EventSubscriberInterface::class)) {
            return null;
        }

        $events = call_user_func([$component->getName(), 'getSubscribedEvents']);

        return array_map(function($listeners) {
            if (is_string($listeners)) {
                return [[$listeners]];
            } else if ($listeners && is_string($listeners[0])) {
                return [$listeners];
            } else {
                return $listeners;
            }
        }, $events);
    }

}
