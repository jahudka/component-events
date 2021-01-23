<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests\Mocks;

use Contributte\Nextras\Orm\Events\Listeners\AfterInsertListener;
use Nette\Application\UI\Control;
use Nextras\Orm\Entity\IEntity;

/**
 * @AfterInsert(NextrasORM\TestEntity)
 */
class ComponentWithChildren extends Control implements AfterInsertListener {
    public int $onAfterInsertCalls = 0;

    public function createComponentChild() : ChildComponent {
        return new ChildComponent();
    }

    public function onAfterInsert(IEntity $entity) : void {
        ++$this->onAfterInsertCalls;
    }
}
