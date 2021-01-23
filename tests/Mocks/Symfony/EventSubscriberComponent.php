<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests\Mocks\Symfony;

use Nette\Application\UI\Control;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriberComponent extends Control implements EventSubscriberInterface {
    public static function getSubscribedEvents() : array {
        return [
            'event1' => 'handlerForEvent1',
            'event2' => ['handlerForEvent2', 10],
            'event3' => [
                ['firstHandlerForEvent3', 10],
                ['secondHandlerForEvent3', 20],
            ],
        ];
    }

    public function handlerForEvent1() : void {}
    public function handlerForEvent2() : void {}
    public function firstHandlerForEvent3() : void {}
    public function secondHandlerForEvent3() : void {}
}
