<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests\Constraints;

use PHPUnit\Framework\Constraint\Constraint;
use ReflectionClass;

class ReflectionOfClass extends Constraint {
    private string $class;

    public function __construct(string $class) {
        $this->class = $class;
    }

    public function toString() : string {
        return sprintf("is a reflection of '%s'", $this->class);
    }

    protected function matches($other) : bool {
        return $other instanceof ReflectionClass && $other->getName() === $this->class;
    }
}
