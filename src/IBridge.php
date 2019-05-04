<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\Statement;


interface IBridge {

    public function detectPresence(ContainerBuilder $builder) : bool;

    public function createAnalyser() : IAnalyser;

    public function createRelayFactory(array $listeners) : Statement;

}
