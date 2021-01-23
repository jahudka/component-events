<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests\Mocks\NextrasORM;

use Contributte\Nextras\Orm\Events\Listeners\AfterInsertListener;
use Nette\Application\UI\Control;
use Nextras\Orm\Entity\IEntity;

/**
 * @AfterInsert(TestEntity)
 */
class EntityListenerComponent extends Control implements AfterInsertListener {
    public function onAfterInsert(IEntity $entity) : void {}
}
