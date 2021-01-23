<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Bridges\NextrasORM;

use Contributte\Nextras\Orm\Events\Listeners as ORM;
use Jahudka\ComponentEvents\Helpers;
use Jahudka\ComponentEvents\IAnalyser;
use ReflectionClass;

class Analyser implements IAnalyser {
    private const events = [
        ORM\LifecycleListener::class => [
            'onBeforeInsert',
            'onBeforePersist',
            'onBeforeRemove',
            'onBeforeUpdate',
            'onAfterInsert',
            'onAfterPersist',
            'onAfterRemove',
            'onAfterUpdate',
            'onFlush',
        ],
        ORM\BeforeInsertListener::class => [
            'onBeforeInsert',
        ],
        ORM\BeforePersistListener::class => [
            'onBeforePersist',
        ],
        ORM\BeforeRemoveListener::class => [
            'onBeforeRemove',
        ],
        ORM\BeforeUpdateListener::class => [
            'onBeforeUpdate',
        ],
        ORM\AfterInsertListener::class => [
            'onAfterInsert',
        ],
        ORM\AfterPersistListener::class => [
            'onAfterPersist',
        ],
        ORM\AfterRemoveListener::class => [
            'onAfterRemove',
        ],
        ORM\AfterUpdateListener::class => [
            'onAfterUpdate',
        ],
        ORM\FlushListener::class => [
            'onFlush',
        ],
    ];

    public function analyse(ReflectionClass $component) : ?array {
        $events = [];

        foreach (self::events as $listener => $listenerEvents) {
            if ($component->implementsInterface($listener)) {
                $annotationName = preg_replace('~^.+\\\\|Listener$~', '', $listener);

                if ($annotation = Helpers::getAnnotation($component, $annotationName)) {
                    $entities = preg_split('~\s*,\s*~', $annotation);

                    foreach ($listenerEvents as $event) {
                        foreach ($entities as $entity) {
                            $events[$event][] = Helpers::resolveClassName($entity, $component->getFileName());
                        }
                    }
                }
            }
        }

        return $events ?: null;
    }
}
