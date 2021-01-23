<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests\Mocks;

use SplFileInfo;
use Jahudka\ComponentEvents\Tests\Constraints;

class ReturnTypes {
    public function builtinScalar() : int {
        return 0;
    }

    public function builtinScalarNullable() : ?int {
        return 0;
    }

    public function builtinClass() : SplFileInfo {
        return new SplFileInfo(__FILE__);
    }

    public function builtinClassNullable() : ?SplFileInfo {
        return new SplFileInfo(__FILE__);
    }

    public function userDefinedClass() : ChildComponent {
        return new ChildComponent();
    }

    public function userDefinedClassNullable() : ?ChildComponent {
        return new ChildComponent();
    }

    /** @return int */
    public function phpDocScalar() {
        return 0;
    }

    /** @return SplFileInfo */
    public function phpDocOverride() : object {
        return new SplFileInfo(__FILE__);
    }

    /** @return Constraints\ReflectionOfClass */
    public function phpDocImports() {
        return new Constraints\ReflectionOfClass(self::class);
    }
}
