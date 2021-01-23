<?php

namespace Jahudka\ComponentEvents\Tests;

use Jahudka\ComponentEvents\IRelay;
use Jahudka\ComponentEvents\Relay;
use Nette\Application\Application;
use Nette\Application\IPresenter;
use PHPUnit\Framework\TestCase;

class RelayTest extends TestCase {
    public function testSetPresenter() : void {
        $relay = new Relay();

        $mockInternalRelay = $this->createMock(IRelay::class);
        $mockApplication = $this->createMock(Application::class);
        $mockPresenter = $this->createMock(IPresenter::class);

        $mockInternalRelay->expects($this->once())
            ->method('setPresenter')
            ->with($mockPresenter);

        $relay->add($mockInternalRelay);

        $relay->setPresenter($mockApplication, $mockPresenter);
    }
}
