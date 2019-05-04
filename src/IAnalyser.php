<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;


interface IAnalyser {

    public function analyse(\ReflectionClass $component) : ?array;

}
