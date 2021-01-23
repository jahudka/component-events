<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\NextrasORM;

use Jahudka\ComponentEvents\IRelay;
use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Nextras\Orm\Model\Model;


class Relay implements IRelay {
    private Model $model;

    private array $eventMap;

    /** @var IPresenter|Presenter|null */
    private ?IPresenter $presenter = null;

    private ?string $presenterClass = null;

    private array $cleanup = [];

    public function __construct(Model $model, array $eventMap) {
        $this->model = $model;
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

            foreach ($this->eventMap[$class] as $entity => $events) {
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
        foreach ($this->cleanup as $entity => $events) {
            $repository = $this->model->getRepositoryForEntity($entity);

            foreach ($events as $event => $handler) {
                $index = array_search($handler, $repository->$event, true);

                if (is_int($index)) {
                    array_splice($repository->$event, $index, 1);
                }
            }
        }

        $this->presenter = $this->presenterClass = null;
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
