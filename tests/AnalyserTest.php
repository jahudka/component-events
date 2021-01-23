<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests;

use Jahudka\ComponentEvents\Analyser;
use Jahudka\ComponentEvents\IAnalyser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;


class AnalyserTest extends TestCase {
    private Analyser $analyser;
    private IAnalyser $mockInternalAnalyser;

    protected function setUp() : void {
        $this->mockInternalAnalyser = $this->createMock(IAnalyser::class);

        $this->analyser = new Analyser();
        $this->analyser->add($this->mockInternalAnalyser, 'mock');
    }

    public function testAnalyseNonContainerPresenter() : void {
        $target = new ReflectionClass(Mocks\NonContainerPresenter::class);

        $this->mockInternalAnalyser->expects($this->once())
            ->method('analyse')
            ->with($this->identicalTo($target));

        $result = $this->analyser->analysePresenter($target);
        $this->assertEquals([], $result);
    }

    public function testAnalyseContainerPresenter() : void {
        $target = new ReflectionClass(Mocks\ContainerPresenter::class);

        $this->mockInternalAnalyser->expects($this->exactly(3))
            ->method('analyse')
            ->withConsecutive(
                [$this->identicalTo($target)],
                [new Constraints\ReflectionOfClass(Mocks\ComponentWithChildren::class)],
                [new Constraints\ReflectionOfClass(Mocks\ChildComponent::class)],
            );

        $result = $this->analyser->analysePresenter($target);
        $this->assertEquals([], $result);
    }
}
