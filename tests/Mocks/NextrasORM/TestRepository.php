<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests\Mocks\NextrasORM;

use Nextras\Orm\Repository\Repository;

class TestRepository extends Repository {
    public static function getEntityClassNames() : array {
        return [
            TestEntity::class,
        ];
    }
}
