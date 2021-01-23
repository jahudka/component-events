<?php

namespace Jahudka\ComponentEvents\Tests\Bridges\NextrasORM;

use Jahudka\ComponentEvents\Bridges\NextrasORM\Analyser;
use Jahudka\ComponentEvents\Tests\Mocks;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AnalyserTest extends TestCase {

    public function testAnalyse() {
        $analyser = new Analyser();
        $result = $analyser->analyse(new ReflectionClass(Mocks\NextrasORM\EntityListenerComponent::class));
        $this->assertEquals(['onAfterInsert' => [Mocks\NextrasORM\TestEntity::class]], $result);
    }
}
