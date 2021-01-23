<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use ReflectionClass;


interface IAnalyser {

    public function analyse(ReflectionClass $component) : ?array;

}
