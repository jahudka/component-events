<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests\Mocks;

use Doctrine\Common\EventSubscriber;
use Nette\Application\UI\Control;

class ChildComponent extends Control implements EventSubscriber {
    public int $postRemoveCalls = 0;

    public function getSubscribedEvents() : array {
        return ['postRemove'];
    }

    public function postRemove() : void {
        ++$this->postRemoveCalls;
    }
}
