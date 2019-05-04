<?php


declare(strict_types=1);

if (!class_exists('Nette\DI\Definitions\Statement')) {
    class_alias('Nette\DI\Statement', 'Nette\DI\Definitions\Statement');
}

if (!class_exists('Nette\DI\Definitions\ServiceDefinition')) {
    class_alias('Nette\DI\ServiceDefinition', 'Nette\DI\Definitions\ServiceDefinition');
}
