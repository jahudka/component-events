<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\Symfony;

use Jahudka\ComponentEvents\IRelay;
use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class Relay implements IRelay {

    private EventDispatcherInterface $dispatcher;

    private array $eventMap;

    /** @var IPresenter|Presenter|null */
    private ?IPresenter $presenter = null;

    private ?string $presenterClass = null;

    private array $cleanup = [];

    public function __construct(EventDispatcherInterface $dispatcher, array $eventMap) {
        $this->dispatcher = $dispatcher;
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

            foreach ($this->eventMap[$class] as $event => $priorityMap) {
                foreach ($priorityMap as $priority => $_) {
                    $handler = fn (...$args) => $this->relay($event, $priority, $args);
                    $this->dispatcher->addListener($event, $handler, $priority);
                    $this->cleanup[$event][$priority] = $handler;
                }
            }
        }
    }


    private function unsubscribeEvents() : void {
        foreach ($this->cleanup as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->dispatcher->removeListener($event, $listener);
            }
        }

        $this->presenter = $this->presenterClass = null;
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
