<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use Nette\ComponentModel\Container;


class Analyser {

    /** @var IAnalyser[] */
    private $analysers = [];

    public function add(IAnalyser $analyser, string $id) : void {
        $this->analysers[$id] = $analyser;
    }

    public function analysePresenter(\ReflectionClass $presenter) : array {
        $listeners = [];
        $stack = [[null, $presenter]];

        /** @var \ReflectionClass $component */
        while ([$name, $component] = array_shift($stack)) {
            foreach ($this->analyse($component) as $id => $events) {
                $listeners[$id][$name] = $events;
            }

            if ($component->isSubclassOf(Container::class)) {
                foreach ($component->getMethods() as $method) {
                    if (
                        preg_match('~^createComponent(.+)$~', $method->getName(), $m)
                        && ($type = Helpers::getReturnType($method))
                    ) {
                        $stack[] = [
                            ($name ? $name . Container::NAME_SEPARATOR : '') . lcfirst($m[1]),
                            new \ReflectionClass($type),
                        ];
                    }
                }
            }
        }

        return $listeners;
    }

    private function analyse(\ReflectionClass $component) : array {
        $listeners = [];

        foreach ($this->analysers as $id => $analyser) {
            if ($tmp = $analyser->analyse($component)) {
                $listeners[$id] = $tmp;
            }
        }

        return $listeners;
    }


}
