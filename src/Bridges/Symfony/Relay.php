<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\Symfony;

use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Jahudka\ComponentEvents\IRelay;
use Symfony\Component\EventDispatcher\EventDispatcher;


class Relay implements IRelay {

    private $dispatcher;

    private $eventMap;

    private $idMap;

    /** @var IPresenter|Presenter */
    private $presenter;

    private $subscribed = false;

    public function __construct(EventDispatcher $dispatcher, array $eventMap, array $idMap) {
        $this->dispatcher = $dispatcher;
        $this->eventMap = $eventMap;
        $this->idMap = $idMap;
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

        foreach ($this->idMap as $id => [$event, $priority]) {
            $this->dispatcher->addListener(
                $event,
                [$this, 'relay__' . $id],
                $priority
            );
        }
    }

    public function __call(string $name, array $arguments) {
        if (!$this->presenter) {
            return;
        }

        $id = (int) substr($name, 7);

        if (!isset($this->idMap[$id])) {
            throw new \RuntimeException('Method ' . static::class . '::' . $name . '() doesn\'t exist');
        }

        [$event, $priority] = $this->idMap[$id];
        $presenter = get_class($this->presenter);

        if (isset($this->eventMap[$event][$priority][$presenter])) {
            foreach ($this->eventMap[$event][$priority][$presenter] as $component => $methods) {
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
