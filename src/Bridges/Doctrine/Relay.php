<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\Doctrine;

use Doctrine\Common\EventManager;
use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Jahudka\ComponentEvents\IRelay;


class Relay implements IRelay {

    private EventManager $manager;

    private array $eventMap;

    /** @var IPresenter|Presenter|null */
    private ?IPresenter $presenter = null;

    private ?string $presenterClass = null;

    private bool $subscribed = false;

    public function __construct(EventManager $manager, array $eventMap) {
        $this->manager = $manager;
        $this->eventMap = $eventMap;
    }

    public function setPresenter(IPresenter $presenter) : void {
        $this->unsubscribeEvents();
        $this->presenter = $presenter;
        $this->presenterClass = get_class($presenter);
        $this->subscribeEvents();
    }

    private function subscribeEvents() : void {
        if ($this->subscribed) {
            return;
        }

        $this->subscribed = true;

        if (isset($this->eventMap[$this->presenterClass])) {
            $this->manager->addEventListener(array_keys($this->eventMap[$this->presenterClass]), $this);
        }
    }

    private function unsubscribeEvents() : void {
        if (!$this->subscribed) {
            return;
        }

        $this->subscribed = false;

        if (isset($this->eventMap[$this->presenterClass])) {
            $this->manager->removeEventListener(array_keys($this->eventMap[$this->presenterClass]), $this);
        }
    }

    public function __call(string $event, array $arguments) {
        if (!$this->presenter) {
            return;
        }

        if (isset($this->eventMap[$this->presenterClass][$event])) {
            foreach ($this->eventMap[$this->presenterClass][$event] as $component) {
                call_user_func_array(
                    [$component ? $this->presenter->getComponent($component) : $this->presenter, $event],
                    $arguments
                );
            }
        }
    }
}
