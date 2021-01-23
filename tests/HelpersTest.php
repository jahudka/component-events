<?php

namespace Jahudka\ComponentEvents\Tests;

use Jahudka\ComponentEvents\Helpers;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use SplFileInfo;

class HelpersTest extends TestCase {
    /**
     * @dataProvider provideReturnTypes
     */
    public function testGetReturnType(ReflectionMethod $method, ?string $expectedType) : void {
        $this->assertEquals($expectedType, Helpers::getReturnType($method));
    }

    public function provideReturnTypes() : array {
        $rc = new ReflectionClass(Mocks\ReturnTypes::class);

        return [
            [$rc->getMethod('builtinScalar'), null],
            [$rc->getMethod('builtinScalarNullable'), null],
            [$rc->getMethod('builtinClass'), SplFileInfo::class],
            [$rc->getMethod('builtinClassNullable'), SplFileInfo::class],
            [$rc->getMethod('userDefinedClass'), Mocks\ChildComponent::class],
            [$rc->getMethod('userDefinedClassNullable'), Mocks\ChildComponent::class],
            [$rc->getMethod('phpDocScalar'), null],
            [$rc->getMethod('phpDocOverride'), SplFileInfo::class],
            [$rc->getMethod('phpDocImports'), Constraints\ReflectionOfClass::class],
        ];
    }

    /**
     * @dataProvider provideReturnTypes80
     */
    public function testGetReturnTypes80(ReflectionMethod $method, ?string $expectedType) : void {
        $this->testGetReturnType($method, $expectedType);
    }

    public function provideReturnTypes80() : array {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped('Cannot test on PHP < 8.0');
        }

        $rc = new ReflectionClass(Mocks\ReturnTypes80::class);

        return [
            [$rc->getMethod('unionOfClasses'), null],
            [$rc->getMethod('unionOfClassAndNull'), Mocks\ComponentWithChildren::class],
        ];
    }
}
