<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests\Mocks\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Nette\Application\UI\Control;
use SplFileInfo;

class FailingEventSubscriberComponent extends Control implements EventSubscriber {
    private SplFileInfo $dependency;

    public function __construct(SplFileInfo $dependency) {
        $this->dependency = $dependency;
    }

    public function getSubscribedEvents() : array {
        if ($this->dependency->isDir()) {
            return [
                'prePersist',
                'postPersist',
            ];
        } else {
            return [];
        }
    }

    public function prePersist(EventArgs $args) : void {}

    public function postPersist(EventArgs $args) : void {}
}
