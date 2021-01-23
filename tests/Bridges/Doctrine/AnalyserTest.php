<?php

namespace Jahudka\ComponentEvents\Tests\Bridges\Doctrine;

use Jahudka\ComponentEvents\Bridges\Doctrine\Analyser;
use Jahudka\ComponentEvents\Tests\Mocks;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AnalyserTest extends TestCase {
    public function testAnalyse() : void {
        $analyser = new Analyser();
        $result = $analyser->analyse(new ReflectionClass(Mocks\Doctrine\EventSubscriberComponent::class));
        $this->assertEquals(['prePersist', 'postPersist'], $result);
    }

    public function testAnalyseFailing() : void {
        $analyser = new Analyser();
        $result = $analyser->analyse(new ReflectionClass(Mocks\Doctrine\FailingEventSubscriberComponent::class));
        $this->assertNull($result);
    }
}
