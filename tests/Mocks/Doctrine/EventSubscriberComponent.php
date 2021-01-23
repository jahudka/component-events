<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests\Mocks\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Nette\Application\UI\Control;

class EventSubscriberComponent extends Control implements EventSubscriber {
    public function getSubscribedEvents() : array {
        return [
            'prePersist',
            'postPersist',
        ];
    }

    public function prePersist(EventArgs $args) : void {}
    public function postPersist(EventArgs $args) : void {}
}
