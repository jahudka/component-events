<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\Doctrine;

use Doctrine\Common\EventManager;
use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Jahudka\ComponentEvents\IRelay;


class Relay implements IRelay {

    private $manager;

    private $eventMap;

    /** @var IPresenter|Presenter */
    private $presenter;

    private $subscribed = false;

    public function __construct(EventManager $manager, array $eventMap) {
        $this->manager = $manager;
        $this->eventMap = $eventMap;
    }

    public function setPresenter(IPresenter $presenter) : void {
        $this->presenter = $presenter;
        $this->subscribeEvents();
    }

    private function subscribeEvents() : void {
        if ($this->subscribed) {
            return;
        }

        $this->subscribed = true;

        foreach (array_keys($this->eventMap) as $event) {
            $this->manager->addEventListener(
                $event,
                $this
            );
        }
    }

    public function __call(string $event, array $arguments) {
        if (!$this->presenter) {
            return;
        }

        $presenter = get_class($this->presenter);

        if (isset($this->eventMap[$event][$presenter])) {
            foreach ($this->eventMap[$event][$presenter] as $component) {
                call_user_func_array(
                    [$component ? $this->presenter->getComponent($component) : $this->presenter, $event],
                    $arguments
                );
            }
        }
    }

}
