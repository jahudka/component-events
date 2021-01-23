<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use ReflectionClass;


interface IAnalyser {

    /**
     * This method should analyse a presenter / component class
     * and return an array of all events the target class wants
     * to subscribe to.
     *
     * The internal structure of the array is opaque to the rest
     * of ComponentEvents, but it doesn't need to include information
     * about the target class or the component path as this is handled
     * within ComponentEvents. See the IBridge interface for further
     * information.
     */
    public function analyse(ReflectionClass $component) : ?array;

}
