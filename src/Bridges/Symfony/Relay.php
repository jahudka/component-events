<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\Symfony;

use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Jahudka\ComponentEvents\IRelay;
use Symfony\Component\EventDispatcher\EventDispatcher;


class Relay implements IRelay {

    private EventDispatcher $dispatcher;

    private array $eventMap;

    /** @var IPresenter|Presenter|null */
    private ?IPresenter $presenter = null;

    private ?string $presenterClass = null;

    private bool $subscribed = false;

    private array $cleanup = [];

    public function __construct(EventDispatcher $dispatcher, array $eventMap) {
        $this->dispatcher = $dispatcher;
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
            foreach ($this->eventMap[$this->presenterClass] as $event => $priorityMap) {
                foreach ($priorityMap as $priority => $_) {
                    $handler = fn (...$args) => $this->relay($event, $priority, $args);
                    $this->dispatcher->addListener($event, $handler, $priority);
                    $this->cleanup[$event][$priority] = $handler;
                }
            }
        }
    }


    private function unsubscribeEvents() : void {
        if (!$this->subscribed) {
            return;
        }

        $this->subscribed = false;

        foreach ($this->cleanup as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->dispatcher->removeListener($event, $listener);
            }
        }

        $this->cleanup = [];
    }

    private function relay(string $event, int $priority, array $arguments) : void {
        if (!$this->presenter) {
            return;
        }

        if (isset($this->eventMap[$this->presenterClass][$event][$priority])) {
            foreach ($this->eventMap[$this->presenterClass][$event][$priority] as $component => $methods) {
                foreach ($methods as $method) {
                    call_user_func_array(
                        [$component ? $this->presenter->getComponent($component) : $this->presenter, $method],
                        $arguments
                    );
                }
            }
        }
    }
}
