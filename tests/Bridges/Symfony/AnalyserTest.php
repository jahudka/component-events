<?php

namespace Jahudka\ComponentEvents\Tests\Bridges\Symfony;

use Jahudka\ComponentEvents\Bridges\Symfony\Analyser;
use Jahudka\ComponentEvents\Tests\Mocks;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AnalyserTest extends TestCase {
    public function testAnalyse() : void {
        $analyser = new Analyser();
        $result = $analyser->analyse(new ReflectionClass(Mocks\Symfony\EventSubscriberComponent::class));

        $this->assertEquals(
            [
                'event1' => [['handlerForEvent1']],
                'event2' => [['handlerForEvent2', 10]],
                'event3' => [['firstHandlerForEvent3', 10], ['secondHandlerForEvent3', 20]],
            ],
            $result,
        );
    }
}
