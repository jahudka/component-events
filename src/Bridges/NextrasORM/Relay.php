<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\NextrasORM;

use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Jahudka\ComponentEvents\IRelay;
use Nextras\Orm\Model\Model;


class Relay implements IRelay {
    private Model $model;

    private array $eventMap;

    /** @var IPresenter|Presenter|null */
    private ?IPresenter $presenter = null;

    private ?string $presenterClass = null;

    private bool $subscribed = false;

    private array $cleanup = [];

    public function __construct(Model $model, array $eventMap) {
        $this->model = $model;
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
            foreach ($this->eventMap[$this->presenterClass] as $entity => $events) {
                $repository = $this->model->getRepositoryForEntity($entity);

                foreach ($events as $event => $_) {
                    $handler = fn (...$args) => $this->relay($entity, $event, $args);
                    $repository->$event[] = $handler;
                    $this->cleanup[$entity][$event] = $handler;
                }
            }
        }
    }

    private function unsubscribeEvents() : void {
        if (!$this->subscribed) {
            return;
        }

        $this->subscribed = false;

        foreach ($this->cleanup as $entity => $events) {
            $repository = $this->model->getRepositoryForEntity($entity);

            foreach ($events as $event => $handler) {
                $index = array_search($handler, $repository->$event, true);

                if (is_int($index)) {
                    array_splice($repository->$event, $index, 1);
                }
            }
        }

        $this->cleanup = [];
    }

    private function relay(string $entity, string $event, array $arguments) : void {
        if (!$this->presenter) {
            return;
        }

        if (isset($this->eventMap[$this->presenterClass][$entity][$event])) {
            foreach ($this->eventMap[$this->presenterClass][$entity][$event] as $component) {
                call_user_func_array(
                    [$component ? $this->presenter->getComponent($component) : $this->presenter, $event],
                    $arguments
                );
            }
        }
    }
}
