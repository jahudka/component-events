<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests\Mocks;

use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\VoidResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class NonContainerPresenter implements IPresenter, EventSubscriberInterface {
    public int $handleResponseCalls = 0;

    public static function getSubscribedEvents() : array {
        return [
            'kernel.response' => ['handleResponse', 10],
        ];
    }

    public function run(Request $request) : Response {
        return new VoidResponse();
    }

    public function handleResponse() : void {
        ++$this->handleResponseCalls;
    }
}
