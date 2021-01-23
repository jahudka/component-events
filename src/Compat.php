<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\DI\Statement as LegacyStatement;
use Nette\DI\ServiceDefinition as LegacyServiceDefinition;
use Nette\Schema\Schema;
use Nette\StaticClass;

class Compat {
    use StaticClass;

    public static function aliasClasses() : void {
        if (!class_exists(Statement::class)) {
            class_alias(LegacyStatement::class, Statement::class);
        }

        if (!class_exists(ServiceDefinition::class)) {
            class_alias(LegacyServiceDefinition::class, ServiceDefinition::class);
        }
    }

    public static function getConfig(ComponentEventsExtension $extension, array $defaults) : object {
        if (interface_exists(Schema::class)) {
            return $extension->getConfig();
        }

        return self::toObject($extension->validateConfig($defaults, $extension->getConfig()));
    }

    private static function toObject(array $config) : object {
        return (object) array_map(fn ($v) => is_array($v) ? self::toObject($v) : $v, $config);
    }
}
