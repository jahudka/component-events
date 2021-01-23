<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\Doctrine;

use Doctrine\Common\EventManager;
use Jahudka\ComponentEvents\IRelay;
use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;


class Relay implements IRelay {

    private EventManager $manager;

    private array $eventMap;

    /** @var IPresenter|Presenter|null */
    private ?IPresenter $presenter = null;

    private ?string $presenterClass = null;

    public function __construct(EventManager $manager, array $eventMap) {
        $this->manager = $manager;
        $this->eventMap = $eventMap;
    }

    public function setPresenter(IPresenter $presenter) : void {
        $this->unsubscribeEvents();
        $this->subscribeEvents($presenter);
    }

    private function subscribeEvents(IPresenter $presenter) : void {
        $class = get_class($presenter);

        if (isset($this->eventMap[$class])) {
            $this->presenter = $presenter;
            $this->presenterClass = $class;
            $this->manager->addEventListener(array_keys($this->eventMap[$class]), $this);
        }
    }

    private function unsubscribeEvents() : void {
        if ($this->presenterClass && isset($this->eventMap[$this->presenterClass])) {
            $this->manager->removeEventListener(array_keys($this->eventMap[$this->presenterClass]), $this);
            $this->presenter = $this->presenterClass = null;
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
